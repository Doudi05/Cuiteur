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

$bd= wa_bd_connect();

/*------------------------- Etape 1 --------------------------------------------
- vérifications diverses et traitement des soumissions
------------------------------------------------------------------------------*/

// si utilisateur déjà authentifié, on le redirige vers la page cuiteur.php
if (wa_est_authentifie()){
    header('Location: recherche.php');
    exit();
}

foreach($_POST as &$val){
    $val = trim($val);
}

$usID=$_SESSION['id'];
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

/*------------------------- Etape 2 --------------------------------------------
- génération du code HTML de la page
------------------------------------------------------------------------------*/

wa_aff_debut('Cuiteur | Recherche', '../styles/cuiteur.css');

wa_aff_entete("Recherche des utilisateurs");

wa_aff_infos(true, $bd);

$saisie='';
if (isset($_POST['recherche'])) {
	$saisie=$_POST['saisie'];
}
echo '<div id=divCompte>',
		'<form action=# method=post id=soustitre>',
			'<input id=recherche type="text" name="saisie" value="',$saisie,'" size=35 />',
			'<input type="submit" name="recherche" value="Rechercher"/>',
		'</form>';
if(isset($_POST['recherche']) && $_POST['saisie']!=''){
	$count="SELECT count(usID)
			FROM users
			WHERE usPseudo LIKE '%$saisie%'
			OR usNom LIKE '%$saisie%'";
	$cou=wa_bd_send_request($bd, $count);
	$C=mysqli_fetch_assoc($cou);
	if ($C['count(usID)']==0) {
		echo '<div id="blablavide">',
                '<p>Aucun utilisateur trouver</p>',
            '</div>';
	}else{
		$saisie=mysqli_escape_string($bd,$_POST['saisie']);
		$sql="SELECT DISTINCT * 
			FROM users
			WHERE usPseudo LIKE '%$saisie%'
			OR usNom LIKE '%$saisie%'";
		$recherche=wa_bd_send_request($bd, $sql);
		echo
            '<br>',
			'<h2 id=resultRech>Résultats de la recherche</h2>',
            '<br>';
		    wa_aff_recherche($bd,$recherche);
	}
}
echo
	'</div>';

wa_aff_pied();

wa_aff_fin();

// facultatif car fait automatiquement par PHP
ob_end_flush();


?>