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

//_______________________________________________________________________________________________________
/*
*Fonction permettant de poster un blabla
*En cas de probl�mes elle retournera l'erreur (Le message ne doit pas être vide)
*
*@param	object_mysqli_connect	$bd		il s'agit de l'objet repr�sentant la connection au serveur MySQL
*
*@return  array	retourne un tableau avec les erreurs rencontr�s, si les donn�es sont valides le tableau est vide
*/
function pbl_postBlabla($bd){
    $erreurs=array();

    foreach($_POST as &$val){
        $val = trim($val);
    }

    if(empty($_POST['txtMessage'])){
        $erreurs[]="Le message ne doit pas être vide";
    }else {
        $noTags = strip_tags($_POST['txtMessage']);
        if ($noTags != $_POST['txtMessage']){
            $erreurs[] = "Le nom et le prénom ne peuvent pas contenir de code HTML.";
        }
    }
    
    //Si pas derreur
    if(count($erreurs)==0){
        //On insére le cuiteur
        $text=mysqli_real_escape_string ($bd, ($_POST['txtMessage']));
        $date= date('Y').date('m').date('d');
        $heure= date('H:i:s');
        $sql="INSERT INTO blablas (blIDAuteur,blDate,blHeure,blTexte,blIDAutORig)
        VALUES ({$_SESSION['id']},$date,'$heure','$text', NULL)";
        
        //On analyse le texte pour retrouv� les mentions et tags
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
            VALUES ('".mysqli_real_escape_string($bd, (substr($tag, 1)))."','".$last_id."');";
            wa_bd_send_request($bd, $sql);
        }
        
        //insertion des mentions apr�s v�rification que le pseudo correspond a quelqu'un pr�sent dans la base de donn�e
        foreach($mentions as $mention){
            $sql="SELECT usID
                FROM users
                WHERE usPseudo='".mysqli_real_escape_string ($bd, (substr($mention, 1)))."'";
            $res=wa_bd_send_request($bd, $sql);
            $tab=mysqli_fetch_assoc($res);
            wa_html_proteger_sortie($tab);
            if(count($tab)==1){
                $sql="INSERT INTO mentions (meIDUser, meIDBlabla)
                VALUES ('".mysqli_real_escape_string ($bd, ($tab['usID']))."','".$last_id."');";
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
    $erreurs=pbl_postBlabla($bd);
    if(count($erreurs)==0){
        header ('location: cuiteur.php');
        exit();
    }
}
else{
    $_POST['txtMessage']="";
}

//Permet de savoir le nombre de blablas a afficher (plus de blablas)
if(!isset($_GET['blablas'])){
    $_GET['blablas']=1;
    $countBlablas=MAX_BLA;
}
else{
    $countBlablas=MAX_BLA*($_GET['blablas']);
}

//Si l'utilisateur recuite un message
if(isset($_GET['recuiter'])){
    //On verifie au préalable que l'id du blabla renseigné existe bien
    $sql="SELECT blID, blTexte
        FROM blablas
        WHERE blID='".mysqli_real_escape_string ($bd, ($_GET['recuiter']))."'";
    $res=wa_bd_send_request($bd, $sql);
    $tab=mysqli_fetch_assoc($res);
    mysqli_free_result($res);
    //Si oui
    if(count($tab)==2){
        //On recuite le message
        $text=mysqli_real_escape_string ($bd, ($tab['blTexte']));
        $date= date('Y').date('m').date('d');
        $heure= date('H:i:s');
        $sql="INSERT INTO blablas (blIDAuteur,blDate,blHeure,blTexte,blIDAutORig)
        VALUES ({$_SESSION['id']},$date,'$heure','$text', '".mysqli_real_escape_string ($bd, ($tab['blID']))."')";

        wa_bd_send_request($bd, $sql);
        $last_id=mysqli_insert_id($bd);
        //On insere les meme tags contenus
        $sql="SELECT * FROM tags WHERE taIDBlabla='".mysqli_real_escape_string ($bd, ($_GET['recuiter']))."'";
        $res=wa_bd_send_request($bd, $sql);
        while($tags=mysqli_fetch_assoc($res)){
            $sql="INSERT INTO tags (taID, taIDBlabla)
            VALUES ('".mysqli_real_escape_string($bd, $tags['taID'])."','".$last_id."');";
            wa_bd_send_request($bd, $sql);
        }
        //On insere les meme mentions contenus
        $sql="SELECT * FROM mentions WHERE meIDBlabla='".mysqli_real_escape_string ($bd, ($_GET['recuiter']))."'";
        $res=wa_bd_send_request($bd, $sql);
        while($mentions=mysqli_fetch_assoc($res)){
            $sql="INSERT INTO tags (meIDUser, meIDBLabla)
            VALUES ('".mysqli_real_escape_string($bd, $mentions['meIDUser'])."','".$last_id."');";
            wa_bd_send_request($bd, $sql);
        }
    }
    //Rechargement de la page sans $_GET['recuiter']
    header ('location: cuiteur.php');
    exit();
}

//Si l'utilisateur supprime un message
if(isset($_GET['delete'])){
    //On verifie au pr�alable que l'id du blabla renseign� appartient bien a l'utilisateur avant de supprimer les donn�es
    $sql="SELECT blID FROM blablas WHERE blIDAuteur='".mysqli_real_escape_string ($bd, ($_SESSION['id']))."'";
    $res=wa_bd_send_request($bd, $sql);
    $tab=mysqli_fetch_assoc($res);
    mysqli_free_result($res);
    //Si oui
    if(count($tab)==1){
        //On supprime les donn�es relatives a ce blabla dans les tables blablas, mentions, et tags
        $sql="DELETE FROM blablas
            WHERE blID='".mysqli_real_escape_string ($bd, ($_GET['delete']))."'
            AND blIDAuteur='".mysqli_real_escape_string ($bd, ($_SESSION['id']))."'";
            wa_bd_send_request($bd, $sql);
        $sql="DELETE FROM mentions
            WHERE meIDBlabla='".mysqli_real_escape_string ($bd, ($_GET['delete']))."'";
            wa_bd_send_request($bd, $sql);
        $sql="DELETE FROM tags
            WHERE taIDBlabla='".mysqli_real_escape_string ($bd, ($_GET['delete']))."'";
            wa_bd_send_request($bd, $sql);
    }
    //Rechargement de la page sans $_GET['delete']
    header ('location: cuiteur.php');
    exit();
}
    
//REQUEST FOR CONNECTED USER DATAS
/*$sql=get_Request_User(mysqli_real_escape_string($bd, ($_SESSION['id'])));
$res=wa_bd_send_request($bd, $sql);
$tabUser=mysqli_fetch_assoc($res);
wa_html_proteger_sortie($tabUser);
mysqli_free_result($res);

$sql=get_Request_User_Count_Blablas(mysqli_real_escape_string($bd, ($_SESSION['id'])));	
$res=wa_bd_send_request($bd, $sql);
$tabUserCountBlablas=mysqli_fetch_assoc($res);
$sql=get_Request_User_Count_Mentions(mysqli_real_escape_string($bd, ($_SESSION['id'])));	
$res=wa_bd_send_request($bd, $sql);
$tabUserCountMentions=mysqli_fetch_assoc($res);
$sql=get_Request_User_Count_Abonnes(mysqli_real_escape_string($bd, ($_SESSION['id'])));	
$res=wa_bd_send_request($bd, $sql);
$tabUserCountAbonnes=mysqli_fetch_assoc($res);
$sql=get_Request_User_Count_Abonnements(mysqli_real_escape_string($bd, ($_SESSION['id'])));	
$res=wa_bd_send_request($bd, $sql);
$tabUserCountAbonnements=mysqli_fetch_assoc($res);
wa_html_proteger_sortie($tabUserCountAbonnes);
wa_html_proteger_sortie($tabUserCountMentions);
wa_html_proteger_sortie($tabUserCountAbonnements);
wa_html_proteger_sortie($tabUserCountBlablas);
mysqli_free_result($res);


//REQUEST F0R TAGS
$sql=get_Request_Aside_Tags();
$resTags=wa_bd_send_request($bd, $sql);

//REQUEST FOR SUGGESTIONS
$sql=get_Request_Suggestions_Aside(mysqli_real_escape_string($bd, ($_SESSION['id'])));
$resSug=wa_bd_send_request($bd, $sql);*/

wa_aff_debut('Cuiteur | Principale', '../styles/cuiteur.css');
wa_aff_entete();
wa_aff_infos(true);

        
/*pb_aff_aside($tabUser, $tabUserCountBlablas, $tabUserCountAbonnements, $tabUserCountAbonnes, $resTags, $resSug);
mysqli_free_result($resTags);
mysqli_free_result($resSug);*/

if (count($erreurs) > 0) {
    echo '<p class="error">Les erreurs suivantes ont été détectées: ';
    foreach ($erreurs as $v) {
        echo '<br> - ', $v;
    }
    echo '</p>';    
}

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
    (wa_aff_blablas($res, $countBlablas)>=$countBlablas);
}else{
    (wa_aff_blablas($existbl, $countBlablas)>=$countBlablas);
}

wa_aff_pied();
wa_aff_fin();
// facultatif car fait automatiquement par PHP
ob_end_flush();
?>
