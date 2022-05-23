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

/**
 * Fonction qui permet de soustraire un intervalle a une date
 *
 * @param string  $diff  durrées dans le format requis pour la constructeur DateInterval
 * @return string $res   retourne la date soustraire sous la forme (Ymd)
**/
function dateSub($diff=''){
	if($diff==''){
		$dateDuJour=date('Ymd');
		return $dateDuJour;
	}
	$date=new DateTime(date('Y-m-d'));
	$sub=new DateInterval($diff);
	$date->sub($sub);
	return $date->format('Ymd');
}
/**
 * Fonction qui affiche toute les tendances
 *
 * @param mysqli  $bd connexion a la base de donnée
**/
function allTendance($bd){
	$date=dateSub();
	$sqljours="SELECT taID, count(taIDBlabla) as NB FROM tags,blablas WHERE blID = taIDBlabla
	AND blDate >= '$date'
	GROUP BY taID
	ORDER BY count(taIDBlabla) DESC
	LIMIT 9";
	$Topjour=wa_bd_send_request($bd, $sqljours);

	$date=dateSub('P7D');
	$sqlsemaine="SELECT taID, count(taIDBlabla) as NB FROM tags,blablas WHERE blID = taIDBlabla
	AND blDate >= '$date'
	GROUP BY taID
	ORDER BY count(taIDBlabla) DESC
	LIMIT 9";
	$Topsemaine=wa_bd_send_request($bd, $sqlsemaine);

	$date=dateSub('P1M');
	$sqlmois="SELECT taID, count(taIDBlabla) as NB FROM tags,blablas WHERE blID = taIDBlabla
	AND blDate >= '$date'
	GROUP BY taID
	ORDER BY count(taIDBlabla) DESC
	LIMIT 9";
	$Topmois=wa_bd_send_request($bd, $sqlmois);


	$date=dateSub('P1Y');
	$sqlanne="SELECT taID, count(taIDBlabla) as NB FROM tags,blablas WHERE blID = taIDBlabla
	AND blDate >= '$date'
	GROUP BY taID
	ORDER BY count(taIDBlabla) DESC
	LIMIT 9";
	$Topannee=wa_bd_send_request($bd, $sqlanne);
    
	echo '<div id=tendance>',
			'<h2>Top 10 du jour</h2>',
			'<ol>';
			$exist=true;
			while($TJ=mysqli_fetch_assoc($Topjour)){
				$tag=wa_html_proteger_sortie($TJ['taID']);
				echo
				'<li><a href="tendances.php?tags=',cryptage($tag),'">',$tag,'(',wa_html_proteger_sortie($TJ['NB']),')','</a></li>';
				$exist=false;
			}
			if($exist){
				echo
				'<p>aucune tendance ...</p>';
			}	
	echo
			'</ol>',
			'<h2>Top 10 de la semaine</h2>',
			'<ol>';
			$exist=true;
			while($TS=mysqli_fetch_assoc($Topsemaine)){
				$tag=wa_html_proteger_sortie($TS['taID']);
				echo
				'<li><a href="tendances.php?tags=',cryptage($tag),'">',$tag,'(',wa_html_proteger_sortie($TS['NB']),')','</a></li>';
				$exist=false;
			}
			if($exist){
				echo
				'<p>aucune tendance ...</p>';
			}
			echo
			'</ol>',
			'<h2>Top 10 du mois</h2>',
			'<ol>';
			$exist=true;
			while($TM=mysqli_fetch_assoc($Topmois)){
				$tag=wa_html_proteger_sortie($TM['taID']);
				echo
				'<li><a href="tendances.php?tags=',cryptage($tag),'">',$tag,'(',wa_html_proteger_sortie($TM['NB']),')','</a></li>';
				$exist=false;
			}
			if($exist){
				echo
				'<p>aucune tendance ...</p>';
			}	
	echo
			'</ol>',
			'<h2>Top 10 de l\'année</h2>',
				'<ol>';
			$exist=true;
			while($TA=mysqli_fetch_assoc($Topannee)){
				$tag=wa_html_proteger_sortie($TA['taID']);
				echo
				'<li><a href="tendances.php?tags=',cryptage($tag),'">',$tag,'(',wa_html_proteger_sortie($TA['NB']),')','</a></li>';
				$exist=false;
			}
			if($exist){
				echo
				'<p>aucune tendance ...</p>';
			}
	echo
				'</ol>',
			'</div>';
}

///////////HTML//////////////
//debut du html
wa_aff_debut('Cuiteur | Tendances', '../styles/cuiteur.css');

//verfiication de l'existance du tag
if (!isset($_GET['tags'])) {
    
	wa_aff_entete("");

	wa_aff_infos(true);

	//toute les tendances
	allTendance($bd);
}else{
	wa_aff_entete(decryptage(wa_html_proteger_sortie($_GET['tags'])));

	wa_aff_infos(true);

	$tag=mysqli_escape_string($bd,decryptage($_GET['tags']));

	//requete pour recuperer le nombre de blabla
	$sql="SELECT count(taIDBlabla) FROM tags WHERE taID='$tag'";
	$nb=wa_bd_send_request($bd, $sql);
	$NB=mysqli_fetch_assoc($nb);
	$nombrebl=wa_html_proteger_sortie($NB['count(taIDBlabla)']);
	if ($nombrebl==0) {
		//aucun blabla
		wa_aff_blablas($nb, $nombrebl);
	}else{
		//afficher les blablas concercés
		$sql="SELECT auteur.usID AS autID, auteur.usPseudo AS autPseudo, auteur.usNom AS autNom, auteur.usAvecPhoto AS autPhoto,  blID, blTexte, blDate, blHeure, origin.usID AS oriID, origin.usPseudo AS oriPseudo, origin.usNom As oriNom, origin.usAvecPhoto AS oriPhoto
			FROM blablas 
			INNER JOIN tags ON taIDBlabla = blID
			INNER JOIN users AS auteur ON blIDAuteur=usID
			LEFT OUTER JOIN users AS origin ON blIDAutOrig=origin.usID
			WHERE taID='$tag'
			ORDER BY blID DESC";
		$blabla=wa_bd_send_request($bd, $sql);
		wa_aff_blablas($blabla, $nombrebl);
	}
}
//affichage du pied de page 
wa_aff_pied();
//fin du html
wa_aff_fin();
exit;
?>