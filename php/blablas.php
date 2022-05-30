<?php

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
    header('Location: blablas.php');
    exit();
}

// verification de l'existance de la variable blablas
if (!isset($_GET['blablas'])) {
  	$nbblablas=4;
}else{
	$nbblablas=blablaTest($_GET['blablas']);
}

$bd= wa_bd_connect();

wa_aff_debut('Cuiteur | Blablas', '../styles/cuiteur.css');

$id = wa_bd_proteger_entree($bd,decryptage($_GET['id']));

//test pour savoir si l'utilsateur existe
$Exist= "SELECT COUNT(usID)
		FROM users
		WHERE users.usID = '$id'";
$test=wa_bd_send_request($bd, $Exist);
$G=mysqli_fetch_assoc($test);
//si l'utilisateur d'existe pas redirection vers page utilisateur pur afficher message
if($G['COUNT(usID)']==0){
	header('location: utilisateur.php?id='.cryptage(wa_html_proteger_sortie($id)));
}
//REQUETE SQL QUI PERMET DE SAVOIR SI IL EXISTE DES BLABLAS A AFFICHER
$bl= "SELECT COUNT(blID) , usPseudo
		FROM users ,blablas
		WHERE users.usID = '$id'
		AND blIDAuteur = usID";
$existbl=wa_bd_send_request($bd, $bl);
$B=mysqli_fetch_assoc($existbl);
$nombrebl=wa_html_proteger_sortie($B['COUNT(blID)']);


$sql = "SELECT COUNT(blID) , usPseudo
		FROM users ,blablas
		WHERE users.usID = '$id'
		AND blIDAuteur = usID";

$res = wa_bd_send_request($bd, $sql);

if (mysqli_num_rows($res) == 0){
    // libération des ressources
    mysqli_free_result($res);
    mysqli_close($bd);
    
    wa_aff_entete('Erreur');
    wa_aff_infos();
    echo    '<ul>',
                '<li>L\'utilisateur ', $id, ' n\'existe pas</li>',
            '</ul>';
    wa_aff_pied();
    wa_aff_fin();
    exit;   //==> FIN DU SCRIPT
}

$t = mysqli_fetch_assoc($res);

if($_SESSION['id']==$id){
	wa_aff_entete("Mes blablas");
}else{
	wa_aff_entete("Les blablas de {$t['usPseudo']}");
}

wa_aff_infos(true, $bd);

if($G['COUNT(usID)']!='0'){
	wa_afficher_profil($bd, $id, "");
}

echo '<br>','<br>','<br>', '<br>';

$php="blablas.php";

if($nombrebl==0){
	//affichage des blablas
	echo '<ul>';
	wa_aff_blablas($bd, $existbl, $nombrebl);
	echo '</ul>';
}else{
	//REQUETE SQL QUI PERMET DE RECUPERER TOUT LES BLABLAS DE L UTILISATEUR
	$sql = "SELECT auteur.usID AS autID, auteur.usPseudo AS autPseudo, auteur.usNom AS autNom, auteur.usAvecPhoto AS autPhoto, blID,  blTexte, blDate, blHeure, origin.usID AS oriID, origin.usPseudo AS oriPseudo, origin.usNom As oriNom, origin.usAvecPhoto AS oriPhoto
	              FROM (blablas
	              INNER JOIN users AS auteur ON blIDAuteur = usID)
	              LEFT OUTER JOIN users AS origin ON blIDAutOrig = origin.usID
	              WHERE auteur.usID = '$id'
	              ORDER BY blID DESC";
	$sqlblablas=wa_bd_send_request($bd, $sql);
	//affichage des blablas
	echo '<ul>';
	wa_aff_blablas($bd, $sqlblablas, $nombrebl);
	echo '</ul>';
}

// libération des ressources
mysqli_free_result($res);

mysqli_close($bd);

wa_aff_pied();

wa_aff_fin();

// facultatif car fait automatiquement par PHP
ob_end_flush();



?>
