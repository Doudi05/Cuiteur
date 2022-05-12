<?php

ob_start(); // start output buffering
session_start();

require_once 'bibli_generale.php';
require_once 'bibli_cuiteur.php';

// if user is not authenticated, redirect to index.php
if (wa_est_authentifie()){
    header('Location: utilisateur.php');
    exit;
}

$db = wa_bd_connect();

/*------------------------------------------------------------------------------
- Get user's data (current user if id is not set or invalid)
------------------------------------------------------------------------------*/
$id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['usID'];

if (isset($_GET['id']) && (! wa_est_entier(($_GET['id'])) || $_GET['id'] <= 0)){
    $id = $_SESSION['usID'];
}

$sqlUserData = "SELECT users.*
                FROM users
                WHERE users.usID = $id";

$userData = mysqli_fetch_assoc(wa_bd_send_request($GLOBALS['db'], $sqlUserData));

$sqlStats = "SELECT COUNT(*) AS nbBlablas
             FROM blablas
             WHERE blablas.blIDAuteur = $id
             UNION
             SELECT COUNT(*) AS nbMentions
             FROM mentions
             WHERE mentions.meIDUser = $id";
$stats = mysqli_fetch_assoc(wa_bd_send_request($GLOBALS['db'], $sqlStats));

echo '<pre>';
print_r($userData);
print_r($stats);
echo '</pre>';
die;

// if user is not found, get current user's data
if (! $userData){
    $sqlUserData = "SELECT *
               FROM users
               WHERE usId = ". $_SESSION['usID'];
    $userData = mysqli_fetch_assoc(wa_bd_send_request($GLOBALS['db'], $sqlUserData));
}

$userData = wa_html_proteger_sortie($userData);

/*------------------------------------------------------------------------------
- Generating the html code for the page
------------------------------------------------------------------------------*/
wa_aff_debut('Cuiteur | Profil de '. $userData['usPseudo'], '../styles/cuiteur.css');

//test pour savoir si l'utilsateur existe 
$Exist= "SELECT COUNT(usID)
		FROM  users 
		WHERE usID = '$id'";
$test=wa_bd_send_request($bd, $Exist);
$G=mysqli_fetch_assoc($test);


if($G['COUNT(usID)']=='0'){
	wa_aff_entete("Cette utilisateur n\'éxiste pas");
}else{
	wa_aff_entete("Le profil de", $userData['usPseudo']);
}

wa_aff_infos(true);

wa_aff_user_info($userData);

wa_aff_pied();
wa_aff_fin();

// facultatif car fait automatiquement par PHP
ob_end_flush();

// free resources
mysqli_close($db);

// ----------  Local functions ----------- //

/**
 * Show user's info
 *
 * @param array $userData User's data
 */
function wa_aff_user_info(array $userData){
    $photoProfilPath = $userData['usAvecPhoto'] == '1' ? '../upload/'. $userData['usID'] .'.jpg' : '../images/anonyme.jpg';
    echo '<p>',
            '<img src="', $photoProfilPath, '" alt="Photo de profil" class="photoProfil">',
            wa_html_a('./utilisateur.php?id='. $userData['usID'], $userData['usPseudo']), ' ', $userData['usNom'],
            wa_html_a('./blablas.php?id='. $userData['usID'], $userData['nbBlablas'] .' blabla'. ($userData['nbBlablas'] > 1 ? 's' : '')),
            wa_html_a('./mentions.php?id='. $userData['usID'], $userData['nbMentions'] .' mention'. ($userData['nbMentions'] > 1 ? 's' : '')),
            wa_html_a('./abonnes.php?id='. $userData['usID'], $userData['nbAbonnes'] .' abonnement'. ($userData['nbAbonnes'] > 1 ? 's' : '')),
            wa_html_a('./abonnements.php?id='. $userData['usID'], $userData['nbAbonnements'] .' abonné'. ($userData['nbAbonnements'] > 1 ? 's' : '')),
            '</p>';
}