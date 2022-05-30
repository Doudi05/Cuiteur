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
$id = wa_bd_proteger_entree($bd,decryptage($_GET['id']));

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
    wa_aff_entete("Mes abonnements");
}else{
    //$pse=wa_html_proteger_sortie($P['usPseudo']);
    wa_aff_entete("Les abonnement de {$P['usPseudo']}");
}

//patie infos du html
wa_aff_infos(true, $bd);

/*REQUETE POUR AVOIR LE NOMBRE D'ABONNEMENT*/
$nbabonnement= "SELECT COUNT(eaIDAbonne)
        FROM estabonne
        WHERE eaIDUser='$id'";
$abo=wa_bd_send_request($bd, $nbabonnement);
$A=mysqli_fetch_assoc($abo);

$test=wa_bd_send_request($bd, $nbabonnement);
$T=mysqli_fetch_assoc($test);

if($T['COUNT(eaIDAbonne)']==0){
    //a revoir pour le div
    echo '<div id="blablavide">',
            '<p>Aucun abonnement à afficher</p>',
        '</div>';
}else{
    if($G['COUNT(usID)']!='0'){
        wa_afficher_profil($bd, $id, "");
    }

    $sql="SELECT * FROM users , estabonne WHERE eaIDUser='$id' AND usID = eaIDAbonne";
    $recherche=wa_bd_send_request($bd, $sql);
    echo '<div id=divCompte>';
        wa_aff_recherche($bd,$recherche);
    echo '</div>';
}
//affichage du pied de page 
wa_aff_pied();
//fin du html
wa_aff_fin();
exit;
?>