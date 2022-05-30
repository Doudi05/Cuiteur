<?php

ob_start(); // start output buffering
session_start();

require_once 'bibli_generale.php';
require_once 'bibli_cuiteur.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

// if user is not authenticated, redirect to index.php
if (wa_est_authentifie()){
    header('Location: utilisateur.php');
    exit;
}

$bd = wa_bd_connect();

//test si l'id est present dans l'url & s'il est valide
if(!isset($_POST['id'])){
	$id=decryptage(($_GET['id']));
}else{ //si le bouton est actionner on l'abonne ou desabonne
	$id=$_POST['id'];
	if(isset($_POST['sabonner'])){
		$date= date('Y').date('m').date('d');
		$sabonner="INSERT INTO estabonne (eaIDUser , eaIDAbonne, eaDate)
				VALUES ({$_SESSION['id']},{$_POST['id']},'$date')";
		$sabonne=wa_bd_send_request($bd, $sabonner);
	}else{
		$desabonner="DELETE FROM estabonne WHERE eaIDUser = {$_SESSION['id']} AND eaIDAbonne = {$_POST['id']}";
		$desabonne=wa_bd_send_request($bd, $desabonner);
	}		
}

//requete pour recuperer le pseudo
$usID=$_SESSION['id'];
$sqlPseudo = "SELECT usPseudo FROM users WHERE usID ='$id'";
$save=wa_bd_send_request($bd, $sqlPseudo);
$pseudo=mysqli_fetch_assoc($save);

$pseudo = wa_html_proteger_sortie($pseudo);

/*------------------------------------------------------------------------------
- Generating the html code for the page
------------------------------------------------------------------------------*/
wa_aff_debut('Cuiteur | Profil de '. $pseudo['usPseudo'], '../styles/cuiteur.css');

//test pour savoir si l'utilsateur existe 
$Exist= "SELECT COUNT(usID)
		FROM  users 
		WHERE usID = '$id'";
$test=wa_bd_send_request($bd, $Exist);
$G=mysqli_fetch_assoc($test);


if($G['COUNT(usID)']=='0'){
	wa_aff_entete("Cette utilisateur n\'éxiste pas");
}else{
	wa_aff_entete("Le profil de ". $pseudo['usPseudo'] . "");
}

wa_aff_infos(true, $bd);

if($G['COUNT(usID)']!='0'){
	wa_afficher_profil($bd, $id, "utilisateur");
}

wa_aff_pied();

wa_aff_fin();

// facultatif car fait automatiquement par PHP
ob_end_flush();

// free resources
mysqli_close($bd);

?>