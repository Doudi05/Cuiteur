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

$bd= wa_bd_connect();

foreach($_POST as &$val){
    $val = trim($val);
}

//test pour voir si btn publier existe
if(isset($_POST['btnPublier'])){
    $text=mysqli_escape_string($bd,$_POST['txtMessage']);
    if($text!=''){
      $date= date('Y').date('m').date('d');
      $heure= date('H:i:s');
      $sql="INSERT INTO blablas (blIDAuteur,blDate,blHeure,blTexte,blIDAutORig)
      VALUES ({$_SESSION['id']},$date,'$heure','$text', NULL)";
      $poster=wa_bd_send_request($bd, $sql);
      $blID=mysqli_insert_id($bd);
      //recuperer toute les mentiions du blablas
      $mentions=all_mentions($text);
  
      foreach ($mentions as $key) {
        $key=mysqli_escape_string($bd,$key);
        $sql="SELECT usID , count(usID) FROM users WHERE usPseudo = '$key'";
        $users=wa_bd_send_request($bd, $sql);
        $US=mysqli_fetch_assoc($users);
        if($US['count(usID)']!=0){
          $sql="INSERT INTO mentions (meIDUser,meIDBlabla) 
                VALUES ({$US['usID']},$blID)";
          $insertMentions=wa_bd_send_request($bd, $sql);
        }
      }
      //recup toute les tags du blablas
      $tags=all_tags($text);
      foreach ($tags as $key) {
        $key=mysqli_escape_string($bd,$key);
        $sql="INSERT INTO tags (taID,taIDBlabla)
            VALUES ('$key',$blID)";
        $insertTags=wa_bd_send_request($bd, $sql);
      }
      header('location: cuiteur.php');
    }
}

rec_sup($bd);
$rep=repondre($bd);

if (!isset($_GET['blablas'])) {
    $nbblablas=4;
}else{
  $nbblablas=blablaTest($_GET['blablas']);
}
//REQEUTE QUI VERIFIE SI IL EXISTE DES BLABLAS A AFFICHER
$bl= "SELECT COUNT(blID)
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
$existbl=wa_bd_send_request($bd, $bl);
$B=mysqli_fetch_assoc($existbl);
$nombrebl=$B['COUNT(blID)'];

wa_aff_debut('Cuiteur | Principale', '../styles/cuiteur.css');
wa_aff_entete();
wa_aff_infos(true);

if($nombrebl!=0){
    //Requete pour recuperer tout les blablas a afficher dans cuiteur de l'utilisateur
    $sql= "SELECT auteur.usID AS autID, auteur.usPseudo AS autPseudo, auteur.usNom AS autNom, auteur.usAvecPhoto AS autPhoto, blID, blTexte, blDate, blHeure, origin.usID AS oriID, origin.usPseudo AS oriPseudo, origin.usNom As oriNom, origin.usAvecPhoto AS oriPhoto 
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
    wa_aff_blablas($bd,$res, $nbblablas , 'cuiteur.php');
}else{
    wa_aff_blablas($bd,$existbl, $nbblablas , 'cuiteur.php', $nombrebl);
}

wa_aff_pied();
wa_aff_fin();
// facultatif car fait automatiquement par PHP
ob_end_flush();

?>