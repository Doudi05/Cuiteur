<?php
/* ------------------------------------------------------------------------------
    Architecture de la page
    - étape 1 : vérifications diverses et traitement des soumissions
    - étape 2 : génération du code HTML de la page
------------------------------------------------------------------------------*/

ob_start(); //démarre la bufferisation
session_start();

require_once 'php/bibli_generale.php';
require_once 'php/bibli_cuiteur.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

/*------------------------- Etape 1 --------------------------------------------
- vérifications diverses et traitement des soumissions
------------------------------------------------------------------------------*/

// si utilisateur déjà authentifié, on le redirige vers la page cuiteur_1.php
if (wa_est_authentifie()){
    $chemin = isset($_POST['redirection'])? $_POST['redirection'] : 'php/wesh.php';
    header('Location: '.$chemin);
    exit();
}

// traitement si soumission du formulaire de connexion
$err = isset($_POST['btnConnexion']) ? wal_traitement_connexion() : array();

/*------------------------- Etape 2 --------------------------------------------
- génération du code HTML de la page
------------------------------------------------------------------------------*/

wa_aff_debut('Cuiteur | Connexion', 'styles/cuiteur.css');

wa_aff_entete('Connectez-vous');
wa_aff_infos(false);

wal_aff_formulaire($err);

wa_aff_pied();
wa_aff_fin();

// facultatif car fait automatiquement par PHP
ob_end_flush();

// ----------  Fonctions locales du script ----------- //

/**
 * Affichage du contenu de la page (formulaire de connexion)
 *
 * @param   array   $err    tableau d'erreurs à afficher
 * @global  array   $_POST
 */
function wal_aff_formulaire(array $err): void {

    // réaffichage des données soumises en cas d'erreur, sauf les mots de passe 
    if (isset($_POST['btnConnexion'])){
        $values = wa_html_proteger_sortie($_POST);
    }
    else{
        $values['pseudo'] = '';
    }
        
    if (count($err) > 0) {
        echo '<p class="error">Votre connexion n\'a pas pu être réalisée à cause des erreurs suivantes : ';
        foreach ($err as $v) {
            echo '<br> - ', $v;
        }
        echo '</p>';    
    }


    echo    
            '<p>Pour vous connecter, il faut vous authentifier. </p>',
            '<form method="post" action="index.php">',
                '<table>';

    wa_aff_ligne_input( 'Pseudo :', array('type' => 'text', 'name' => 'pseudo', 'value' => $values['pseudo'], 'required' => true));
    wa_aff_ligne_input('Mot de passe :', array('type' => 'password', 'name' => 'passe', 'value' => '', 'required' => true));
    echo '<tr><td  colspan="2"><input type="hidden" name="redirection" value="'
    ,isset($_POST["redirection"])? $_POST["redirection"] : ( isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'php/wesh.php') ,'"></td></tr>';

    echo 
                    '<tr>',
                        '<td colspan="2">',
                            '<input type="submit" name="btnConnexion" value="Connexion">',
                        '</td>',
                    '</tr>',
                '</table>',
            '</form>',
            '<p id="texteBaspage">Pas encore de compte ? <a href="php/inscription.php">Inscrivez-vous</a> sans plus tarder!<br>Vous hésitez à vous inscrire ? Laissez-vous séduire par une <a href="html/presentation.html">présentation</a> des possibilités de Cuiteur.</p>';
}

/**
 *  Traitement de la connexion
 *
 *      Etape 1. vérification de la validité des données
 *                  -> return des erreurs si on en trouve
 *      Etape 2. enregistrement du nouvel inscrit dans la base
 *      Etape 3. ouverture de la session et redirection vers la page protegee.php 
 *
 * Toutes les erreurs détectées qui nécessitent une modification du code HTML sont considérées comme des tentatives de piratage 
 * et donc entraînent l'appel de la fonction wa_session_exit() sauf :
 * - les éventuelles suppressions des attributs required car l'attribut required est une nouveauté apparue dans la version HTML5 et 
 *   nous souhaitons que l'application fonctionne également correctement sur les vieux navigateurs qui ne supportent pas encore HTML5
 * - une éventuelle modification de l'input de type date en input de type text car c'est ce que font les navigateurs qui ne supportent 
 *   pas les input de type date
 *
 * @global array    $_POST
 *
 * @return array    tableau assosiatif contenant les erreurs
 */
function wal_traitement_connexion(): array {
    $erreurs = array();
    
    if( !wa_parametres_controle('post', array('pseudo', 'passe', 'btnConnexion','redirection'))) {
        $erreurs [] = 'Tous les champs doivent être remplis';
        echo 'blabla',$_POST['pseudo'],'';
        return $erreurs; 
        $erreurs = array();
    }
    
    foreach($_POST as &$val){
        $val = trim($val);
    }
    
    // vérification du pseudo
    $pseudo = mb_strlen($_POST['pseudo'], 'UTF-8');
    if ($pseudo == 0){
        $erreurs[] = 'Le pseudo doit être renseigné.';
    }
    else if ($pseudo < LMIN_PSEUDO || $pseudo > LMAX_PSEUDO){
        $erreurs[] = 'Le pseudo doit être constitué de '. LMIN_PSEUDO . ' à ' . LMAX_PSEUDO . ' caractères.';
    }
    else if( !mb_ereg_match('^[[:alnum:]]{'.LMIN_PSEUDO.','.LMAX_PSEUDO.'}$', $_POST['pseudo'])){
        $erreurs[] = 'Le pseudo ne doit contenir que des caractères alphanumériques.' ;
    }

    // vérification de l'existance du pseudo
    $bd = wa_bd_connect();
    $pseudo = wa_bd_proteger_entree($bd, $_POST['pseudo']);
    $sql = "SELECT usID FROM users WHERE usPseudo = '$pseudo'"; 

    $res = wa_bd_send_request($bd, $sql);
    
    if (mysqli_num_rows($res) == 0) {
        $erreurs[] = 'Le pseudo spécifié n\'existe pas.';
        // libération des ressources
        mysqli_free_result($res);
        mysqli_close($bd);
        return $erreurs;
    }
    
    // vérification des mots de passe
    $passe = mb_strlen($_POST['passe'], 'UTF-8');
    if ($passe < LMIN_PASSWORD || $passe > LMAX_PASSWORD){
        $erreurs[] = 'Le mot de passe doit être constitué de '. LMIN_PASSWORD . ' à ' . LMAX_PASSWORD . ' caractères.';
    }

    // vérification de l'existance du mot de passe
    $bd = wa_bd_connect();
    $pseudo = wa_bd_proteger_entree($bd, $_POST['pseudo']);
    $sql = "SELECT usID, usPasse FROM users WHERE usPseudo = '$pseudo'"; 

    $res = wa_bd_send_request($bd, $sql);
    $t = mysqli_fetch_assoc($res);
    $passBd = $t['usPasse'];
    if (!password_verify($_POST['passe'], $passBd)) {
        $erreurs[] = 'Le mot de passe est incorrect.';
        // libération des ressources
        mysqli_free_result($res);
        mysqli_close($bd);

    }else {
        $_SESSION['id'] = $t['usID'];
        $erreurs []= 'bien connecté';
        //header("Location: ".$_POST['redirection']);
        header('Location: php/cuiteur.php');
        //exit();
    }
    return $erreurs;
}

?>