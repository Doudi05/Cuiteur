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

// si utilisateur déjà authentifié, on le redirige vers la page cuiteur.php
if (wa_est_authentifie()){
    header('Location: cuiteur.php');
    exit();
}

// verification de l'existance de la variable blablas
// if (!isset($_GET['blablas'])) {
//   	$nbblablas=4;
// }else{
// 	$nbblablas=blablaTest($_GET['blablas']);
// }

//_______________________________________________________________________________________________________

/**
 * Fonction permettant de poster un blabla
 * En cas de probl�mes elle retournera l'erreur (Le message ne doit pas être vide)
 * @param	mysqli	$bd		il s'agit de l'objet repr�sentant la connection au serveur MySQL
 * 
 * @return  array	retourne un tableau avec les erreurs rencontr�s, si les donn�es sont valides le tableau est vide
 */
function wa_postBlabla($bd){
    $erreurs=array();

    foreach($_POST as &$val){
        $val = trim($val);
    }

    if(empty($_POST['txtMessage'])){
        $erreurs[]="Le message ne doit pas être vide";
    }else {
        $noTags = strip_tags($_POST['txtMessage']);
        if ($noTags != $_POST['txtMessage']){
            $erreurs[] = "Le message ne doit pas contenir de code HTML.";
        }
    }
    
    //Pas d'erreur
    if(count($erreurs)==0){
        //On insére le cuiteur
        $text=wa_bd_proteger_entree ($bd, ($_POST['txtMessage']));
        $date= date('Y').date('m').date('d');
        $heure= date('H:i:s');
        $sql="INSERT INTO blablas (blIDAuteur,blDate,blHeure,blTexte,blIDAutOrig)
        VALUES ({$_SESSION['id']},$date,'$heure','$text', NULL)";
        
        //On analyse le texte pour retrouver les mentions et tags
        $res=wa_bd_send_request($bd, $sql);
        $last_id=mysqli_insert_id($bd);
        $spaceArray = explode(' ', ($_POST['txtMessage']));
        $tags=array();
        $mentions=array();
        foreach($spaceArray as $value){
            if ($value[0]=='#'){
                $tags[]=$value;
            }
            else if($value[0]=='@'){
                $mentions[]=$value;
            }
        }
        //insertion des tags
        foreach($tags as $tag){
            $sql="INSERT INTO tags (taID, taIDBlabla)
            VALUES ('".wa_bd_proteger_entree($bd, (substr($tag, 1)))."','".$last_id."');";
            wa_bd_send_request($bd, $sql);
        }
        
        //insertion des mentions après vérification que le pseudo correspond à quelqu'un présent dans la base de donnée
        foreach($mentions as $mention){
            $sql="SELECT usID
                FROM users
                WHERE usPseudo='".wa_bd_proteger_entree ($bd, (substr($mention, 1)))."'";
            $res=wa_bd_send_request($bd, $sql);
            $tab=mysqli_fetch_assoc($res);
            wa_html_proteger_sortie($tab);
            if(count($tab)==1){
                $sql="INSERT INTO mentions (meIDUser, meIDBlabla)
                VALUES ('".wa_bd_proteger_entree ($bd, ($tab['usID']))."','".$last_id."');";
                wa_bd_send_request($bd, $sql);
            }
        }
    }
    return $erreurs;
}

$erreurs=array();

$bd= wa_bd_connect();

//Si l'utilisateur a publier un message
if (isset($_POST['btnPublier'])){
    $erreurs=wa_postBlabla($bd);
    if(count($erreurs)==0){
        header ('location: cuiteur.php');
        exit();
    }
}
else{
    $_POST['txtMessage']="";
}

//Permet de savoir le nombre de blablas a afficher (plus de blablas)
// if(!isset($_GET['blablas'])){
//     $_GET['blablas']=1;
//     $countBlablas=MAX_BLA;
// }
// else{
//     $countBlablas=MAX_BLA*($_GET['blablas']);
// }

wa_rec($bd);

wa_supp($bd);

$rep=wa_rep($bd);

wa_aff_debut('Cuiteur | Principale', '../styles/cuiteur.css');

wa_aff_entete(null, $rep);

wa_aff_infos($bd, true);

//REQEUTE QUI VERIFIE SI IL EXISTE DES BLABLAS A AFFICHER
$sql="SELECT COUNT(blID)
FROM (blablas
INNER JOIN users AS auteur ON blIDAuteur = usID)
LEFT OUTER JOIN users AS origin ON blIDAutOrig = origin.usID
WHERE auteur.usID = {$_SESSION['id']}
OR auteur.usID IN (SELECT eaIDAbonne
                    FROM estabonne
                    WHERE eaIDUser = {$_SESSION['id']})
OR blID IN (SELECT blID
                  FROM blablas
                  INNER JOIN mentions ON blID = meIDBlabla
                  WHERE meIDUser = {$_SESSION['id']})                  
ORDER BY blID DESC";

$existbl=wa_bd_send_request($bd, $sql);
$B=mysqli_fetch_assoc($existbl);
$nombrebl=$B['COUNT(blID)'];

$php="cuiteur.php";

if($nombrebl!=0){
    //Requete pour recuperer tout les blablas a afficher dans cuiteur de l'utilisateur
    $sql= "SELECT auteur.usID AS autID, auteur.usPseudo AS autPseudo, auteur.usNom AS autNom, auteur.usAvecPhoto AS autPhoto, 
            blID, blTexte, blDate, blHeure, 
            origin.usID AS oriID, origin.usPseudo AS oriPseudo, origin.usNom As oriNom, origin.usAvecPhoto AS oriPhoto 
        FROM (blablas
        INNER JOIN users AS auteur ON blIDAuteur = usID)
        LEFT OUTER JOIN users AS origin ON blIDAutOrig = origin.usID
        WHERE auteur.usID = {$_SESSION['id']}
        OR auteur.usID IN (SELECT eaIDAbonne
                            FROM estabonne
                            WHERE eaIDUser = {$_SESSION['id']})
        OR blID IN (SELECT blID
                            FROM blablas
                            INNER JOIN mentions ON blID = meIDBlabla
                            WHERE meIDUser = {$_SESSION['id']})                  
        ORDER BY blID DESC";
    $res=wa_bd_send_request($bd, $sql);
    echo '<ul>';
    wa_aff_blablas($bd, $res, $nombrebl);
}else{
    wa_aff_blablas($bd, $existbl, $nombrebl);
    echo '</ul>';
}

wa_aff_pied();

wa_aff_fin();

// facultatif car fait automatiquement par PHP
ob_end_flush();
?>
