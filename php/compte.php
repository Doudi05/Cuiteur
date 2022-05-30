<?php
/* ------------------------------------------------------------------------------
    Architecture de la page
    - étape 1 : vérifications diverses et traitement des soumissions
    - étape 2 : génération du code HTML de la page
------------------------------------------------------------------------------*/

ob_start(); //démarre la bufferisation
session_start();

require_once 'bibli_generale.php';
require_once 'bibli_cuiteur.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

/*------------------------- Etape 1 --------------------------------------------
- vérifications diverses et traitement des soumissions
------------------------------------------------------------------------------*/

// si utilisateur déjà authentifié, on le redirige vers la page cuiteur_1.php
if (wa_est_authentifie()){
  header('Location: ../index.php');
  exit();
}

$bd = wa_bd_connect();

/*------------------------------------------------------------------------------
- Get user's data
- Check form submission
------------------------------------------------------------------------------*/

if(!isset($_POST['btnValider1'])){
  $sql = "SELECT * FROM users WHERE usID = '".$_SESSION['id']."'"; 
  $res = wa_bd_send_request($bd, $sql);
  $t = mysqli_fetch_assoc($res);
  mysqli_free_result($res);
  mysqli_close($bd);
}

// traitement si soumission des formulaires
$err1 = isset($_POST['btnValider1']) ? wal_traitement_info_perso() : array();
$err2 = isset($_POST['btnValider2']) ? wal_traitement_info_compte() : array();
$err3 = isset($_POST['btnValider3']) ? wal_traitement_info_parametres() : array();

/*------------------------- Etape 2 --------------------------------------------
- génération du code HTML de la page
------------------------------------------------------------------------------*/
$bd = wa_bd_connect();

wa_aff_debut('Cuiteur | Compte', '../styles/cuiteur.css');

wa_aff_entete('Paramètres de mon compte');

wa_aff_infos(true, $bd);

echo '<p>Cette page vous permet de modifier les informations relatives à votre compte.</p>',
     '<br>';

wal_aff_formulaire1($err1);

wal_aff_formulaire2($err2);

wal_aff_formulaire3($err3);

wa_aff_pied();

wa_aff_fin();

// facultatif car fait automatiquement par PHP
ob_end_flush();

// ----------  Fonctions locales du script ----------- //

/**
 * Affiche le contenu de la page compte.php
 * avec toutes les information sur l'utilisateur connecté ainsi que les liens menant vers la page pour les modifier 
 *
 * @param   array   $err    tableau d'erreurs à afficher
 * @global  array   $_POST
 */
function wal_aff_formulaire1(array $err): void {
  global $t;

  // réaffichage des données soumises en cas d'erreur, sauf les mots de passe 
  if (isset($_POST['btnValider1'])){
    $values = wa_html_proteger_sortie($_POST);
  }
  else{
    $bd = wa_bd_connect();
    $sql = "SELECT * FROM users WHERE usID = '".$_SESSION['id']."'"; 
    $res = wa_bd_send_request($bd, $sql);
    $t = mysqli_fetch_assoc($res);
    
    $values['nomprenom'] = isset($_POST['nomprenom'])?  wa_html_proteger_sortie($_POST['nomprenom']) : wa_html_proteger_sortie($t['usNom']);
    $values['naissance'] = isset($_POST['naissance'])?  wa_html_proteger_sortie($_POST['naissance']) : wa_html_proteger_sortie(wa_convert_date_input($t['usDateNaissance']));
    $values['ville'] = isset($_POST['ville'])?  wa_html_proteger_sortie($_POST['ville']) : wa_html_proteger_sortie($t['usVille']);
    $values['bio'] = isset($_POST['bio'])?  wa_html_proteger_sortie($_POST['bio']) : wa_html_proteger_sortie($t['usBio']);
  }

  echo    
      '<div id="divCompte">',
        '<h3>Informations personnelles</h3>';
        if (count($err) > 0) {
          echo '<p class="error">Les erreurs suivantes ont été détectées : ';
          foreach ($err as $v) {
              echo '<br> - ', $v;
          }
          echo '</p>';    
        }
        else if (isset($_POST['btnValider1'])) {
            echo '<p class="update">La mise à jour des informations sur votre compte a bien été effectuée.</p>';  
        }
        echo
        '<form method="post" action="compte.php">',
            '<table>';

    wa_aff_ligne_input('Nom', array('type' => 'text', 'name' => 'nomprenom', 'value' => $values['nomprenom'], 'required' => null));
    wa_aff_ligne_input('Date de naissance', array('type' => 'date', 'name' => 'naissance', 'value' => $values['naissance'], 'required' => null));
    wa_aff_ligne_input('Ville', array('type' => 'text', 'name' => 'ville', 'value' => $values['ville']));
    echo 
                '<tr>
                    <td>
                        <label for="usBio" id="bio">Mini-bio</label >
                    </td>
                    <td>
                        <textarea name="bio" cols="40" rows="13">', $values['bio'] ,'</textarea>
                    </td>
                </tr>',
                '<tr>',
                    '<td colspan="2">',
                        '<input type="submit" name="btnValider1" value="Valider">',
                    '</td>',
                '</tr>',
            '</table>',
        '</form>',
      '</div>';
}

/**
 * Affiche le contenu de la page compte.php
 * avec toutes les information sur l'utilisateur connecté ainsi que les liens menant vers la page pour les modifier 
 *
 * @param   array   $err    tableau d'erreurs à afficher
 * @global  array   $_POST
 */
function wal_aff_formulaire2(array $err): void {
  global $t;

  // réaffichage des données soumises en cas d'erreur, sauf les mots de passe 
  if (isset($_POST['btnValider2'])){
    $values = wa_html_proteger_sortie($_POST);
  }
  else{
    $bd = wa_bd_connect();
    $sql = "SELECT * FROM users WHERE usID = '".$_SESSION['id']."'"; 
    $res = wa_bd_send_request($bd, $sql);
    $t = mysqli_fetch_assoc($res);
    
    $values['email'] = isset($_POST['email'])?  wa_html_proteger_sortie($_POST['email']) : wa_html_proteger_sortie($t['usMail']);
    $values['web'] = isset($_POST['web'])?  wa_html_proteger_sortie($_POST['web']) : wa_html_proteger_sortie($t['usWeb']);
  }

  echo    
      '<div id="divCompte">',
        '<h3>Informations sur votre compte Cuiteur</h3>';
        if (count($err) > 0) {
          echo '<p class="error">Les erreurs suivantes ont été détectées : ';
          foreach ($err as $v) {
              echo '<br> - ', $v;
          }
          echo '</p>';    
        }
        else if (isset($_POST['btnValider2'])) {
            echo '<p class="update">La mise à jour des informations sur votre compte a bien été effectuée.</p>';  
        }
        echo
        '<form method="post" action="compte.php">',
            '<table>';

    wa_aff_ligne_input('Adresse mail', array('type' => 'email', 'name' => 'email', 'value' => $values['email'], 'required' => null));
    wa_aff_ligne_input('Site web', array('type' => 'text', 'name' => 'web', 'value' => $values['web']));
    echo 
                '<tr>',
                    '<td colspan="2">',
                        '<input type="submit" name="btnValider2" value="Valider">',
                    '</td>',
                '</tr>',
            '</table>',
        '</form>',
      '</div>';
}

/**
 * Affiche le contenu de la page compte.php
 * avec toutes les information sur l'utilisateur connecté ainsi que les liens menant vers la page pour les modifier 
 *
 * @param   array   $err    tableau d'erreurs à afficher
 * @global  array   $_POST
 */
function wal_aff_formulaire3(array $err): void {
  global $t;

  // réaffichage des données soumises en cas d'erreur, sauf les mots de passe 
  if (isset($_POST['btnValider3'])){
    $values = wa_html_proteger_sortie($_POST);
  }
  else{
    $bd = wa_bd_connect();
    $sql = "SELECT * FROM users WHERE usID = '".$_SESSION['id']."'"; 
    $res = wa_bd_send_request($bd, $sql);
    $t = mysqli_fetch_assoc($res);

    $values['photo'] = isset($_POST['photo'])?  wa_html_proteger_sortie($_POST['photo']) : wa_html_proteger_sortie($t['usAvecPhoto']);
  }
  echo    
      '<div id="divCompte">',
        '<h3>Paramètres de votre compte Cuiteur</h3>';
        if (count($err) > 0) {
          echo '<p class="error">Les erreurs suivantes ont été détectées : ';
          foreach ($err as $v) {
              echo '<br> - ', $v;
          }
          echo '</p>';    
        }
        else if (isset($_POST['btnValider3'])) {
            echo '<p class="update">La mise à jour des informations sur votre compte a bien été effectuée.</p>';  
        }

        if ((isset($_POST['btnValider3']) && $_POST['photo'] == '1') || (!isset($_POST['btnValider3']) && $values['photo'] == '1')) {
          $photoProfilePath =  '../upload/' . $_SESSION['id'] . '.jpg';
        }
        else {
          $photoProfilePath = '../images/anonyme.jpg';
        }

        echo
        '<form method="post" action="compte.php">',
            '<table>';

    wa_aff_ligne_input('Changer le mot de passe :', array('type' => 'password', 'name' => 'passe1', 'value' => ''));
    wa_aff_ligne_input('Répétez le mot de passe :', array('type' => 'password', 'name' => 'passe2', 'value' => ''));
    echo
        '<tr>',
          '<td>',
              '<p>Votre photo actuelle</p>',
          '</td>',
          '<td>',
              '<img class="photoProfil" src="'.$photoProfilePath.'" alt="Photo de profil">',
              '<p>Taille 20ko maximum</p>',
              '<p>Image JPG carrée (mini 50x50px)</p>',
              wa_aff_ligne_input('', array('type' => 'file', 'name' => 'fichier', 'value' => '', 'size' => '10')),
          '</td>',
        '</tr>',

        '<tr>',
            '<td>',
              '<label for="usAvecPhoto">Utiliser votre photo</label>',
            '</td>',
            '<td>';
              if((isset($_POST['btnValider3']) && $_POST['photo'] == '0') || (!isset($_POST['btnValider3']) && $values['photo'] == '0')){
                echo
                  '<input type="radio" name="pp" value="0" checked>';
              }else{
                echo '<input type="radio" name="pp" value="0">';
              }
              echo '<label>non</label>';

              if((isset($_POST['btnValider3']) && $_POST['photo'] == '1') || (!isset($_POST['btnValider3']) && $values['photo'] == '1')){
                echo
                  '<input type="radio" name="pp" value="1" checked>';
              }else{
                echo '<input type="radio" name="pp" value="1">';
              }
              echo '<label>oui</label>';
      echo
              '</td>',
          '</tr>';
    echo 
                    '<tr>',
                        '<td colspan="2">',
                            '<input type="submit" name="btnValider3" value="Valider">',
                        '</td>',
                    '</tr>',
                '</table>',
            '</form>',
            '</div>';
}

/**
 *  Traitement des informations personelles
 *
 *      Step 1. Vérifier les informations
 *                  -> si erreurs, retourner un tableau d'erreurs
 *      Step 2. modifier les informations dans la base de données
 *      Step 3. Recharger la page avec un message de succés
 *
 *
 * @global array    $_POST
 *
 * @return array    tableau d'erreurs à afficher
 */
function wal_traitement_info_perso(): array {
  $erreurs = array();
  
  if(!wa_parametres_controle('post', array('nomprenom', 'naissance', 'btnValider1'), array('ville', 'bio'))) {
    wa_session_exit();   
  }
    
  foreach($_POST as &$val){
    $val = trim($val);
  }

  // vérification des noms et prenoms
  if (empty($_POST['nomprenom'])) {
    $erreurs[] = 'Le nom et le prénom doivent être renseignés.'; 
  }
  else {
    if (mb_strlen($_POST['nomprenom'], 'UTF-8') > LMAX_NOMPRENOM){
        $erreurs[] = 'Le nom et le prénom ne peuvent pas dépasser ' . LMAX_NOMPRENOM . ' caractères.';
    }
    $noTags = strip_tags($_POST['nomprenom']);
    if ($noTags != $_POST['nomprenom']){
        $erreurs[] = 'Le nom et le prénom ne peuvent pas contenir de code HTML.';
    }
    else {
        if(!mb_ereg_match('^[[:alpha:]]([\' -]?[[:alpha:]]+)*$', $_POST['nomprenom'])){
            $erreurs[] = 'Le nom et le prénom contiennent des caractères non autorisés.';
        }
    }
  }

  // vérification de la date de naissance
  if (empty($_POST['naissance'])){
    $erreurs[] = 'La date de naissance doit être renseignée.'; 
  }
  else{
    if( !mb_ereg_match('^\d{4}(-\d{2}){2}$', $_POST['naissance'])){ //vieux navigateur qui ne supporte pas le type date ?
      $erreurs[] = 'la date de naissance doit être au format "AAAA-MM-JJ".'; 
    }
    else{
      list($annee, $mois, $jour) = explode('-', $_POST['naissance']);
      if (!checkdate($mois, $jour, $annee)) {
        $erreurs[] = 'La date de naissance n\'est pas valide.'; 
      }
      else if (mktime(0,0,0,$mois,$jour,$annee + AGE_MIN) > time()) {
        $erreurs[] = 'Vous devez avoir au moins '.AGE_MIN.' ans pour vous inscrire.'; 
      }
      else if (mktime(0,0,0,$mois,$jour,$annee + AGE_MAX + 1) < time()) {
        $erreurs[] = 'Vous devez avoir au plus '.AGE_MAX.' ans pour vous inscrire.'; 
      }
    }
  }

  // vérification de la ville
  if (mb_strlen($_POST['ville'], 'UTF-8') > LMAX_VILLE){
    $erreurs[] = 'La ville ne peut pas dépasser ' . LMAX_VILLE . ' caractères.';
  }
  $noTags = strip_tags($_POST['ville']);
  if ($noTags != $_POST['ville']){
    $erreurs[] = 'La ville ne peut pas contenir de code HTML.';
  }

  // vérification de la mini-bio
  if (mb_strlen($_POST['bio'], 'UTF-8') > LMAX_BIO){
    $erreurs[] = 'La mini-bio ne peut pas dépasser ' . LMAX_BIO . ' caractères.';
  }
  $noTags = strip_tags($_POST['bio']);
  if ($noTags != $_POST['bio']){
    $erreurs[] = 'La mini-bio ne peut pas contenir de code HTML.';
  }

  // s'il y a des erreurs ==> on retourne le tableau d'erreurs    
  if (count($erreurs) > 0) {  
    return $erreurs;
  }

  $bd = wa_bd_connect();
  $nomprenom = wa_bd_proteger_entree($bd, $_POST['nomprenom']);
  $naissance = wa_bd_proteger_entree($bd, wa_convert_date_sql($_POST['naissance']));
  $ville = wa_bd_proteger_entree($bd, $_POST['ville']);
  $bio = wa_bd_proteger_entree($bd, $_POST['bio']);

  $sql = "UPDATE users SET usNom = '$nomprenom', usDateNaissance = '$naissance', usVille = '$ville', usBio = '$bio' WHERE usID = '$_SESSION[id]'";
  wa_bd_send_request($bd, $sql);
  return array();
  mysqli_close($bd);
  header('Location: compte.php'); 
  exit();
}

/**
 *  Traitement des informations du compte Cuiteur
 *
 *      Step 1. Vérifier les informations
 *                  -> si erreurs, retourner un tableau d'erreurs
 *      Step 2. modifier les informations dans la base de données
 *      Step 3. Recharger la page avec un message de succés
 *
 *
 * @global array    $_POST
 *
 * @return array    tableau d'erreurs à afficher
 */
function wal_traitement_info_compte(): array {
  $erreurs = array();
  
  if(!wa_parametres_controle('post', array('email', 'btnValider2'), array('web'))) {
    wa_session_exit();   
  }
    
  foreach($_POST as &$val){
    $val = trim($val);
  }

  // vérification du format de l'adresse email
  if (empty($_POST['email'])){
    $erreurs[] = 'L\'adresse mail ne doit pas être vide.'; 
  }
  else {
    if (mb_strlen($_POST['email'], 'UTF-8') > LMAX_EMAIL){
      $erreurs[] = 'L\'adresse mail ne peut pas dépasser '.LMAX_EMAIL.' caractères.';
    }
    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
      $erreurs[] = 'L\'adresse mail n\'est pas valide.';
    }
  }

  // vérification du site web
  if (!empty($_POST['web'] && !filter_var($_POST['web'], FILTER_VALIDATE_URL))) {
    $erreurs[] = 'Le site web n\'est pas valide.';
  }

  // s'il y a des erreurs ==> on retourne le tableau d'erreurs    
  if (count($erreurs) > 0) {  
    return $erreurs;
  }

  $bd = wa_bd_connect();
  $email = wa_bd_proteger_entree($bd, $_POST['email']);
  $web = wa_bd_proteger_entree($bd, $_POST['web']);

  $sql = "UPDATE users SET usMail = '$email', usWeb = '$web' WHERE usID = '$_SESSION[id]'";
  wa_bd_send_request($bd, $sql);
  return array();
  mysqli_close($bd);
  header('Location: compte.php'); 
  exit();
}

/**
 *  Traitement des paramètres du compte Cuiteur
 *
 *      Step 1. Vérifier les informations
 *                  -> si erreurs, retourner un tableau d'erreurs
 *      Step 2. modifier les informations dans la base de données
 *      Step 3. Recharger la page avec un message de succés
 *
 *
 * @global array    $_POST
 *
 * @return array    tableau d'erreurs à afficher
 */
function wal_traitement_info_parametres(): array {
  $erreurs = array();
  
  if(!wa_parametres_controle('post', array('usAvecPhoto', 'btnValider3'), array('passe1', 'passe2', 'fichier'))) {
    wa_session_exit();   
  }
    
  foreach($_POST as &$val){
    $val = trim($val);
  }

  // vérification des mots de passe
  if (mb_strlen($_POST['passe1'], 'UTF-8') > 0 || mb_strlen($_POST['passe2'], 'UTF-8') > 0) {
    if ($_POST['passe1'] !== $_POST['passe2']) {
      $erreurs[] = 'Les mots de passe doivent être identiques.';
    }
    $nb = mb_strlen($_POST['passe1'], 'UTF-8');
    if ($nb < LMIN_PASSWORD || $nb > LMAX_PASSWORD){
      $erreurs[] = 'Le mot de passe doit être constitué de '. LMIN_PASSWORD . ' à ' . LMAX_PASSWORD . ' caractères.';
    }
  }

  // vérification de la photo
  if ($_POST['usAvecPhoto'] == '1') {
    if ($_FILES['fichier']['size'] == 0 && !file_exists('../upload/' . $_SESSION['id'] . '.jpg')) {
        $erreurs[] = 'Veuillez sélectionner une photo';
    }
    else if ($_FILES['fichier']['size'] > 0) {
        $extension = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
        if ($extension !== 'jpg') {
            $erreurs[] = 'Le fichier doit être un fichier JPG.';
        }
        // check the size
        $size = getimagesize($_FILES['fichier']['tmp_name']);
        if ($size[0] < 50 || $size[1] < 50) {
            $erreurs[] = 'L\'image doit être au moins de 50x50px.';
        }
        // check the weight
        $maxSize = MAX_PHOTO_PROFILE_WEIGHT_KB * 1024;
        if ($_FILES['fichier']['size'] > $maxSize) {
            $erreurs[] = 'Le fichier doit être inférieur à ' . MAX_PHOTO_PROFILE_WEIGHT_KB . 'ko.';
        }
    }
  }

  // s'il y a des erreurs ==> on retourne le tableau d'erreurs    
  if (count($erreurs) > 0) {  
    return $erreurs;
  }

  $bd = wa_bd_connect();
  if ($_POST['usPasse'] !== '') {
    $passe1 = password_hash($_POST['usPasse'], PASSWORD_DEFAULT);
    $passe1 = wa_bd_proteger_entree($bd, $passe1);
  }

  $photo = $_POST['usAvecPhoto'] == '1' ? '1' : '0';
  $sql = "UPDATE users SET usAvecPhoto = '$photo'";

  if (isset($passe)) {
    $sql .= ", usPasse = '$passe1'";
  }
  $sql .= " WHERE usID = '" . $_SESSION['id'] . "'";

  wa_bd_send_request($bd, $sql);

  // Télécharger une nouvelle photo
  if ($_POST['usAvecPhoto'] == '1' && $_FILES['fichier']['size'] > 0) {
    $photoProfilPath = '../upload/' . $_SESSION['id'] . '.jpg';
    // supprimer l'ancienne photo si elle existe
    if (file_exists($photoProfilPath)) {
      unlink($photoProfilPath);
    }
    move_uploaded_file($_FILES['fichier']['tmp_name'], $photoProfilPath);
  }

  return array();
  mysqli_close($bd);
  header('Location: compte.php'); 
  exit();
}
?>