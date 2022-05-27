<?php

ob_start(); // start output buffering
session_start();

require_once 'bibli_generale.php';
require_once 'bibli_cuiteur.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

// if user is not authenticated, redirect to index.php
if (wa_est_authentifie()){
    header('Location: abonnements.php');
    exit;
}

$bd = wa_bd_connect();

foreach($_POST as &$val){
    $val = trim($val);
}

if (isset($_POST['btnValider'])) {
	foreach ($_POST as $key => $value) {
		if (is_numeric($key)) {
			if ($value==1) {
				$desabonner="DELETE FROM estabonne WHERE eaIDUser={$_SESSION['id']} AND eaIDAbonne=$key";
				$desa=wa_bd_send_request($bd, $desabonner);
			}else{
				$date= date('Y').date('m').date('d');
				$sabonner="INSERT INTO estabonne (eaIDUser , eaIDAbonne, eaDate)
				VALUES ({$_SESSION['id']},$key,'$date')";
				$sabonne=wa_bd_send_request($bd, $sabonner);
			}
		}
	}
	header('location: cuiteur.php');
}

///////////HTML//////////////
//debut du html
wa_aff_debut('Cuiteur | Suggestions', '../styles/cuiteur.css');

wa_aff_entete("Suggestions");

wa_aff_infos($bd, true);

$max=5;
$suggestions= "SELECT *
                FROM users
                WHERE usID IN 
                            (SELECT eaIDAbonne 
                            FROM estabonne 
                            WHERE eaIDUser IN
                                            (SELECT eaIDAbonne 
                                            FROM estabonne 
                                            WHERE eaIDUser = {$_SESSION['id']})) 
                                            AND usID NOT IN
                                                            (SELECT eaIDAbonne
                                                            FROM estabonne
                                                            WHERE eaIDUser = {$_SESSION['id']})
                                                            AND usID != {$_SESSION['id']}
                                                            LIMIT $max";
$recherche=wa_bd_send_request($bd, $suggestions);
echo '<div id=divCompte>';
    wa_aff_suggestions($bd, "suggestions.php", $max);
echo '</div>';
//affichage du pied de page 
wa_aff_pied();
//fin du html
wa_aff_fin();
exit;
?>