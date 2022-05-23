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

/*------------------------------------------------------------------------------
- Get user's data (current user if id is not set or invalid)
------------------------------------------------------------------------------*/
$id = mysqli_escape_string($bd,decryptage($_GET['id']));

//test pour savoir si l'utilsateur existe
$Exist= "SELECT COUNT(usID)
        FROM users
        WHERE users.usID = '$id'";
$test=wa_bd_send_request($bd, $Exist);
$G=mysqli_fetch_assoc($test);



//si l'utilisateur d'existe pas redirection vers page utilisateur pour afficher message
if($G['COUNT(usID)']==0){
    header('location: utilisateur.php?id='.cryptage(wa_html_proteger_sortie($id)));
}
//traitement du formulaire
foreach($_POST as &$val){
    $val = trim($val);
}
if (isset($_POST['btnValider'])) {
    foreach ($_POST as $key => $value) {
        if (wa_est_entier($key)) {
            if ($value==1) {
                $desabonner="DELETE FROM estabonne WHERE eaIDUser={$_SESSION['id']} AND eaIDAbonne=$key";
                $desa=wa_bd_send_request($bd, $desabonner);
            }else{
                $date= date('Ymd');
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
wa_aff_debut('Cuiteur | Abonnement', '../styles/cuiteur.css');
//header du html
/*REQUETE POUR AVOIR LES INFOS DE LA TABLE USERS*/
$sql= "SELECT * , COUNT(blID)
        FROM users , blablas
        WHERE users.usID = blablas.blIDAuteur
        AND usID='$id'";
$info=wa_bd_send_request($bd, $sql);
$I=mysqli_fetch_assoc($info);
$pseudo=wa_bd_send_request($bd, $sql);
$P=mysqli_fetch_assoc($pseudo);
if ($_SESSION['id']==decryptage($_GET['id'])) {
    wa_aff_entete("Mes mentions");
}else{
    //$pse=wa_html_proteger_sortie($P['usPseudo']);
    wa_aff_entete("Les mentions de {$P['usPseudo']}");
}

//patie infos du html
wa_aff_infos(true);

//REQUETE SQL QUI PERMET DE SAVOIR SI IL EXISTE DES BLABLAS A AFFICHER
$bl= "SELECT COUNT(blID) , usPseudo
		FROM users ,blablas
		WHERE users.usID = '$id'
		AND blIDAuteur = usID";
$existbl=wa_bd_send_request($bd, $bl);
$B=mysqli_fetch_assoc($existbl);
$nombrebl=wa_html_proteger_sortie($B['COUNT(blID)']);

if($nombrebl==0){
    //a revoir pour le div
    echo '<div id="blablavide">',
            '<p>Aucune mention à afficher</p>',
        '</div>';
}else{
    // //affichage de la recherche
    // /*REQUETE POUR AVOIR LE NOMBRE D'ABONNEE*/
    // $nbabonne= "SELECT *, COUNT(estabonne.eaIDUser)
    // 			FROM estabonne
    // 			WHERE estabonne.eaIDAbonne ='$id'";
    // $nbabo=wa_bd_send_request($bd, $nbabonne);
    // $Abo=mysqli_fetch_assoc($nbabo);
    // /*REQUETE POUR AVOIR LE NOMBRE DE MENTIONS*/
    // $mentions= "SELECT COUNT(meIDBlabla)
    // 			FROM mentions
    // 			WHERE meIDUser ='$id'";
    // $nbmentions=wa_bd_send_request($bd, $mentions);
    // $M=mysqli_fetch_assoc($nbmentions);
    // /*INITIALISATION DE VARIABLE*/
    // $nbbla=wa_html_proteger_sortie($I['COUNT(blID)']);
    // $nbment=wa_html_proteger_sortie($M['COUNT(meIDBlabla)']);
    // $nbabos=wa_html_proteger_sortie($Abo['COUNT(estabonne.eaIDUser)']);
    // $abonnement=wa_html_proteger_sortie($A['COUNT(eaIDAbonne)']);
    // $pp=profilePicture($id, $I['usAvecPhoto']);
    // $pseudo=wa_html_proteger_sortie($I['usPseudo']);
    // $nom=wa_html_proteger_sortie($I['usNom']);
    // echo
    // '<div id=soustitre>',
    //     '<img src="',$pp,'" alt="',$pseudo,'">
    //     <p><a href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($id)),'" >',$pseudo,'</a> ',$nom,'</p>',
    //     '<ul id=infoUtilisateur>',
    //     '<li><a href="blablas.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',$nbbla,' blablas</a> - </li>',
    //     '<li><a href="mentions.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',$nbment,' mentions</a> - </li>',
    //     '<li><a href="abonnes.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',$nbabos,' abonnés</a> - </li>',
    //     '<li><a href=#>',$abonnement,' abonnement</a></li>',
    //     '</ul>',
    // '</div>';

    if($G['COUNT(usID)']!='0'){
        wa_afficher_profil($bd, $id, "");
    }

    echo '<br><br><br><br><br>';

    // $sql="SELECT * FROM users , estabonne WHERE eaIDUser='$id' AND usID = eaIDAbonne";
    // $recherche=wa_bd_send_request($bd, $sql);
    // echo '<div id=divCompte>';
    //     wa_aff_recherche($bd,$recherche);
    // echo '</div>';

    //REQUETE SQL QUI PERMET DE RECUPERER TOUTE LES MENTIONS DE L UTILISATEUR
	//revoir la requette
	$sql = "SELECT auteur.usID AS autID, auteur.usPseudo AS autPseudo, auteur.usNom AS autNom, auteur.usAvecPhoto AS autPhoto,blID,  blTexte, blDate, blHeure, origin.usID AS oriID, origin.usPseudo AS oriPseudo, origin.usNom As oriNom, origin.usAvecPhoto AS oriAvecPhoto
    FROM blablas 
    INNER JOIN mentions ON meIDBlabla = blID
    INNER JOIN users AS auteur ON blIDAuteur=usID
    LEFT OUTER JOIN users AS origin ON blIDAutOrig=origin.usID
    WHERE meIDUser='$id'
    ORDER BY blID DESC";
    $sqlblablas=wa_bd_send_request($bd, $sql);
    //affichage des blablas
    wa_aff_blablas($sqlblablas);
}
//affichage du pied de page 
wa_aff_pied();
//fin du html
wa_aff_fin();
exit;
?>