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

// si utilisateur déjà authentifié, on le redirige vers la page cuiteur_1.php
if (wa_est_authentifie()){
  header('Location: ../index.php');
  exit();
}

// traitement si soumission des formulaires
$err = isset($_POST['btnValider1']) ? wal_traitement_info_perso() : array();
/*$err = isset($_POST['btnValider2']) ? wal_traitement_connexion() : array();
$err = isset($_POST['btnValider3']) ? wal_traitement_connexion() : array();*/

if(!isset($_POST['btnValider1'])){
  $bd = wa_bd_connect();
  $sql = "SELECT * FROM users WHERE usID = '".$_SESSION['id']."'"; 
  $res = wa_bd_send_request($bd, $sql);
  $t = mysqli_fetch_assoc($res);
  mysqli_free_result($res);
  mysqli_close($bd);
}

/*------------------------- Etape 2 --------------------------------------------
- génération du code HTML de la page
------------------------------------------------------------------------------*/

wa_aff_debut('Cuiteur | Compte', '../styles/cuiteur.css');

wa_aff_entete('Paramètres de mon compte');
wa_aff_infos(true);

wal_aff_formulaire($err);

wa_aff_pied();
wa_aff_fin();

// facultatif car fait automatiquement par PHP
ob_end_flush();

// ----------  Fonctions locales du script ----------- //

/**
 * Affiche le contenu de la page compte.php
 * avec toutes les information sur l'utilisateur connecté ainsi que les liens menant vers la page pour les modifier 
 *
 * @param   array   $err    tableau d'erreurs à afficher
 * @global  array   $_POST
 */
function wal_aff_formulaire(array $err): void {
  global $t;

  // réaffichage des données soumises en cas d'erreur, sauf les mots de passe 
  if (isset($_POST['btnValider1'])){
    $values = wa_html_proteger_sortie($_POST);
  }
  else{
    $bd = wa_bd_connect();
    $sql = "SELECT * FROM users WHERE usID = '".$_SESSION['id']."'"; 
    $res = wa_bd_send_request($bd, $sql);
    $t = mysqli_fetch_assoc($res);
    
    $values['nomprenom'] = isset($_POST['nomprenom'])?  wa_html_proteger_sortie($_POST['nomprenom']) : wa_html_proteger_sortie($t['usNom']);
    $values['naissance'] = isset($_POST['naissance'])?  wa_html_proteger_sortie($_POST['naissance']) : wa_html_proteger_sortie($t['usDateNaissance']);
    $values['ville'] = isset($_POST['ville'])?  wa_html_proteger_sortie($_POST['ville']) : wa_html_proteger_sortie($t['usVille']);
    $values['bio'] = isset($_POST['bio'])?  wa_html_proteger_sortie($_POST['bio']) : wa_html_proteger_sortie($t['usBio']);
  }

  if (isset($_POST['btnValider2'])){
    $values = wa_html_proteger_sortie($_POST);
  }
  else{
    $values['email'] = isset($_POST['email'])?  wa_html_proteger_sortie($_POST['email']) : wa_html_proteger_sortie($t['usMail']);
    $values['web'] = isset($_POST['web'])?  wa_html_proteger_sortie($_POST['web']) : wa_html_proteger_sortie($t['usWeb']);

    mysqli_free_result($res);
    mysqli_close($bd);
  }

  if (count($err) > 0) {
    echo '<p class="error">Les erreurs suivantes ont été détectées : ';
    foreach ($err as $v) {
        echo '<br> - ', $v;
    }
    echo '</p>';    
  }


  echo    
          '<div id="divCompte">',
          '<p>Cette page vous permet de modifier les informations relatives à votre compte. </p>',
          '<br>',
          '<h3>Informations personnelles</h3>',
          '<form method="post" action="compte.php">',
              '<table>';

  wa_aff_ligne_input('Nom', array('type' => 'text', 'name' => 'nomprenom', 'value' => $values['nomprenom'], 'required' => null));
  wa_aff_ligne_input('Date de naissance', array('type' => 'date', 'name' => 'naissance', 'value' => $values['naissance'], 'required' => null));
  wa_aff_ligne_input('Ville', array('type' => 'text', 'name' => 'ville', 'value' => $values['ville']));
  echo '<tr><td><p id="bio">Mini-bio</p></td><td><textarea name="bio" cols="40" rows="13">', $values['bio'] ,'</textarea></td></tr>';

  echo 
                  '<tr>',
                      '<td colspan="2">',
                          '<input type="submit" name="btnValider1" value="Valider">',
                      '</td>',
                  '</tr>',
              '</table>',
          '</form>',

          '<h3>Informations sur voytre compte Cuiteur</h3>',
          '<form method="post" action="compte.php">',
              '<table>';

  wa_aff_ligne_input('Adresse mail:', array('type' => 'email', 'name' => 'email', 'value' => $values['email'], 'required' => null));
  wa_aff_ligne_input('Site web', array('type' => 'text', 'name' => 'web', 'value' => $values['web']));

  echo 
                  '<tr>',
                      '<td colspan="2">',
                          '<input type="submit" name="btnValider2" value="Valider">',
                      '</td>',
                  '</tr>',
              '</table>',
          '</form>',

          '<h3>Paramètres de votre compte Cuiteur</h3>',
          '<form method="post" action="compte.php">',
              '<table>';

  wa_aff_ligne_input('Votre mot de passe :', array('type' => 'password', 'name' => 'passe1', 'value' => ''));
  wa_aff_ligne_input('Répétez le mot de passe :', array('type' => 'password', 'name' => 'passe2', 'value' => ''));
  $photo="../upload/1.jpg";
  $usAvecPhoto=0;
  echo
  
      '<tr><td><p class="textform" id=photo>Votre photo actuelle</p></td><td><img src="',$photo,'" alt="nono"/></td></tr>',
      '<tr><td></td><td>',
      '<p>Taille 20ko maximum</p>',
      '<p>Image JPG carrée (mini 50x50px)</p>',
      wa_aff_ligne_input('', array('type' => 'file', 'name' => 'leFichier', 'value' => '', 'size' => '10', 'required' => null)),
      '<tr><td><p class="textform">Utiliser votre photo</p></td>';
if($usAvecPhoto==0){
  echo
    '<td><input type="radio" name="pp" value="0" checked>
    <label>non</label>
    <input type="radio" name="pp" value="1">
    <label>oui</label></td>';
}else{
  echo
    '<td><input type="radio" name="pp" value="0">
    <label>non</label>
    <input type="radio" name="pp" value="1" checked><label>oui</label></td>';
}

  echo 
                  '<tr>',
                      '<td colspan="2">',
                          '<input type="submit" name="btnValider3" value="Valider">',
                      '</td>',
                  '</tr>',
              '</table>',
          '</form>',
          '</div>';
}

function wal_traitement_info_perso(): array {
  $erreurs = array();
  
  if( !wa_parametres_controle('post', array('nomprenom', 'naissance', 'ville', 'bio', 'btnValider1'))) {
    $erreurs [] = 'Tous les champs doivent être remplis';
    echo 'blabla',$_POST['pseudo'],'';
    return $erreurs; 
    $erreurs = array();
    wa_session_exit();   
  }
    
  foreach($_POST as &$val){
    $val = trim($val);
  }

  // vérification des noms et prenoms
  if( !wa_parametres_controle('post', array('nomprenom', 'btnValider1'))) {
    $erreurs [] = 'Tous les champs doivent être remplis';
    echo $_POST['nomprenom'];
    return $erreurs; 
    $erreurs = array();
  }
  if (empty($_POST['nomprenom'])) {
    $erreurs[] = 'Le nom et le prénom doivent être renseignés.'; 
  }
  else {
    if (mb_strlen($_POST['nomprenom'], 'UTF-8') > LMAX_NOMPRENOM){
        $erreurs[] = 'Le nom et le prénom ne peuvent pas dépasser ' . LMAX_NOMPRENOM . ' caractères.';
    }
    $noTags = strip_tags($_POST['nomprenom']);
    if ($noTags != $_POST['nomprenom']){
        $erreurs[] = 'Le nom et le prénom ne peuvent pas contenir de code HTML.';
    }
    else {
        if( !mb_ereg_match('^[[:alpha:]]([\' -]?[[:alpha:]]+)*$', $_POST['nomprenom'])){
            $erreurs[] = 'Le nom et le prénom contiennent des caractères non autorisés.';
        }
    }
  }
  $bd = wa_bd_connect();
  $nomprenom = wa_bd_proteger_entree($bd, $_POST['nomprenom']);
  $sql = "UPDATE user SET usNom = '$nomprenom' WHERE usID = ".$_SESSION['id']; 
  wa_bd_send_request($bd, $sql);
  mysqli_close($bd);
  header('Location: compte.php'); //TODO : à modifier dans le projet
  exit();

  // vérification de la date de naissance
  if( !wa_parametres_controle('post', array('naissance', 'btnValider1'))) {
    $erreurs [] = 'Tous les champs doivent être remplis';
    echo $_POST['naissance'];
    return $erreurs; 
    $erreurs = array();
  }
  if (empty($_POST['naissance'])){
    $erreurs[] = 'La date de naissance doit être renseignée.'; 
  }
  else{
    if( !mb_ereg_match('^\d{4}(-\d{2}){2}$', $_POST['naissance'])){ //vieux navigateur qui ne supporte pas le type date ?
      $erreurs[] = 'la date de naissance doit être au format "AAAA-MM-JJ".'; 
    }
    else{
      list($annee, $mois, $jour) = explode('-', $_POST['naissance']);
      if (!checkdate($mois, $jour, $annee)) {
        $erreurs[] = 'La date de naissance n\'est pas valide.'; 
      }
      else if (mktime(0,0,0,$mois,$jour,$annee + AGE_MIN) > time()) {
        $erreurs[] = 'Vous devez avoir au moins '.AGE_MIN.' ans pour vous inscrire.'; 
      }
      else if (mktime(0,0,0,$mois,$jour,$annee + AGE_MAX + 1) < time()) {
        $erreurs[] = 'Vous devez avoir au plus '.AGE_MAX.' ans pour vous inscrire.'; 
      }
    }
  }
  $bd = wa_bd_connect();
  $naissance = wa_bd_proteger_entree($bd, $_POST['naissance']);
  $sql = "UPDATE user SET usDateNaissance = '$naissance' WHERE usID = ".$_SESSION['id']; 
  wa_bd_send_request($bd, $sql);
  mysqli_close($bd);
  header('Location: compte.php'); //TODO : à modifier dans le projet
  exit();

  // vérification de la ville
  if( !wa_parametres_controle('post', array('ville', 'btnValider1'))) {
    $erreurs [] = 'Tous les champs doivent être remplis';
    echo $_POST['ville'];
    return $erreurs; 
    $erreurs = array();
  }
  if (mb_strlen($_POST['ville'], 'UTF-8') > LMAX_VILLE){
    $erreurs[] = 'La ville ne peut pas dépasser ' . LMAX_VILLE . ' caractères.';
  }
  $noTags = strip_tags($_POST['ville']);
  if ($noTags != $_POST['ville']){
    $erreurs[] = 'La ville ne peut pas contenir de code HTML.';
  }
  $bd = wa_bd_connect();
  $ville = wa_bd_proteger_entree($bd, $_POST['ville']);
  $sql = "UPDATE user SET usVille = '$ville' WHERE usID = ".$_SESSION['id']; 
  wa_bd_send_request($bd, $sql);
  mysqli_close($bd);
  header('Location: compte.php'); //TODO : à modifier dans le projet
  exit();

  // vérification de la mini-bio
  if( !wa_parametres_controle('post', array('bio', 'btnValider1'))) {
    $erreurs [] = 'Tous les champs doivent être remplis';
    echo $_POST['bio'];
    return $erreurs; 
    $erreurs = array();
  }
  if (mb_strlen($_POST['bio'], 'UTF-8') > LMAX_BIO){
    $erreurs[] = 'La mini-bio ne peut pas dépasser ' . LMAX_BIO . ' caractères.';
  }
  $noTags = strip_tags($_POST['bio']);
  if ($noTags != $_POST['bio']){
    $erreurs[] = 'La mini-bio ne peut pas contenir de code HTML.';
  }
  $bd = wa_bd_connect();
  $bio = wa_bd_proteger_entree($bd, $_POST['bio']);
  $sql = "UPDATE user SET usBio = '$bio' WHERE usID = ".$_SESSION['id']; 
  wa_bd_send_request($bd, $sql);
  mysqli_close($bd);
  header('Location: compte.php'); //TODO : à modifier dans le projet
  exit();

  // s'il y a des erreurs ==> on retourne le tableau d'erreurs    
  if (count($erreurs) > 0) {  
    return $erreurs;
  }

  /*$bd = wa_bd_connect();
  // pas d'erreurs ==> enregistrement de l'utilisateur
  $nomprenom = wa_bd_proteger_entree($bd, $_POST['nomprenom']);
  $ville = wa_bd_proteger_entree($bd, $_POST['ville']);
  $bio = wa_bd_proteger_entree($bd, $_POST['bio']);
  
  $aaaammjj = $annee*10000  + $mois*100 + $jour;
  
  $sql = "UPDATE users SET usNom = '$nomprenom', usVille = '$ville', usBio = '$bio', usDateNaissance = $aaaammjj
          WHERE usID = ".$_SESSION['id'];
          
  wa_bd_send_request($bd, $sql);
  
  // mémorisation de l'ID dans une variable de session 
  // cette variable de session permet de savoir si le client est authentifié
  $_SESSION['id'] = mysqli_insert_id($bd);
  
  // libération des ressources
  mysqli_close($bd);

  header('Location: compte.php'); //TODO : à modifier dans le projet
  exit();*/
}

/*
function formulaire($valNom='',$valJ='',$valM='',$valA='',$valVille='',$valBio='',$valMail='',$valWeb='',$photo ,$usAvecPhoto,$array=''){
  $nom=KG_html_form_input('text','txtNom',$valNom,30);
  $inputNom=KG_html_table_ligne('<p  class="textform">Nom*</p>',$nom);
  $date=KG_html_form_date('selNais',130,$valJ,$valM,$valA);
  $inputDate=KG_html_table_ligne('<p  class="textform">Date de naissance*</p>',$date);
  $ville=KG_html_form_input('text','txtVille',$valVille,30);
  $inputVille=KG_html_table_ligne('<p  class="textform">Ville</p>',$ville);
  $boutton1=KG_html_form_input('submit','btnValider1','Valider',10);
  $Valider1=KG_html_table_ligne('',$boutton1);

  echo '<div id="divconnection">',
  '<p id=soustitre>Cette page vous permet de modifier les informations relatives à votre compte.</p>';
  if(is_array($array)){
    echo '<p>';
    foreach ($array as $i => $value) {
      echo $value;
    }
    echo '</p><br>';
  }

  echo
  '<h3>Informations personnelles</h3>',
  '<form method="POST" action="compte.php">',
          '<table>';
  echo $inputNom;
  echo $inputDate;
  echo $inputVille;
  echo
            '<tr><td><p class="textform" id="bio">Mini-bio</p></td><td><textarea name="txtBio" cols="45" rows="10">',$valBio,'</textarea></td></tr>';
  echo $Valider1;
  echo
          '</table>',
        '</form>';
  $mail=KG_html_form_input('text','txtMail',$valMail,30);
  $inputMail=KG_html_table_ligne('<p  class="textform">Adresse email*</p>',$mail);
  $web=KG_html_form_input('text','txtWeb',$valWeb,30);
  $inputWeb=KG_html_table_ligne('<p  class="textform">Site web</p>',$web);
  $boutton2=KG_html_form_input('submit','btnValider2','Valider',10);
  $Valider2=KG_html_table_ligne('',$boutton2);

  echo '<h3>Informations sur votre compte Cuiteur</h3>',
  '<form method="POST" action="compte.php">',
          '<table>';
  echo $inputMail;
  echo $inputWeb;
  echo $Valider2;
  echo
          '</table>',
        '</form>';
  $pass1=KG_html_form_input('password','txtPass','',10);
  $inputPass1=KG_html_table_ligne('<p  class="textform">Changer le mot de passe</p>',$pass1);
  $pass2=KG_html_form_input('password','txtVerif','',10);
  $inputPass2=KG_html_table_ligne('<p  class="textform">Retapez le mot de passe</p>',$pass2);
  
  $file='';
  if(isset($_FILES['leFichier'])){
    $file='<p id=filename>'.protect($_FILES['leFichier']['name']).'</p>';
  }
  
  echo '<h3>Paramètres de votre compte Cuiteur</h3>',
  '<form enctype="multipart/form-data" method="POST" action="compte.php ">',
          '<table>';
  echo $inputPass1;
  echo $inputPass2;
  echo
            '<tr><td><p class="textform" id=photo>Votre photo actuelle</p></td><td><img src="',$photo,'" alt="nono"/></td></tr>',
            '<tr><td></td><td>
          <p>Image JPG carrée (mini 50x50px)</p>
          <label for=browse id=file>',$file,'</label>
          <input id="browse" type="file" name="leFichier" size=10></td></tr>',
            '<tr><td><p class="textform">Utiliser votre photo</p></td>';
  if($usAvecPhoto==0){
    echo
      '<td><input type="radio" name="pp" value="0" checked><label>non</label><input type="radio" name="pp"
      value="1"><label>oui</label></td>';
  }else{
    echo
      '<td><input type="radio" name="pp" value="0"><label>non</label><input type="radio" name="pp"
      value="1" checked><label>oui</label></td>';
  }
  echo
              '</tr>',
              '<tr><td></td><td><input type="submit" name="btnValider3" value="Valider" size=10></td></tr>',
            '</table>',
          '</form>',
      '</div>';
}

function errorform1($bd){
  $array = array();
  $value=$_POST['txtNom'];
  $str=trim($value);
  $noTags=strip_tags($str);
  if($str=='' || $str != $noTags){
    $array[]= 'Le nom est obligatoire/ Le nom ne doit pas contenir de tags HTML<br>';
  }
  $value=$_POST['txtVille'];
  $str=trim($value);
  $noTags=strip_tags($str);
  if($str != $noTags){
    $array[]= 'La ville ne doit pas contenir de tag html<br>';
  }
  $value=$_POST['txtBio'];
  $str=trim($value);
  $noTags=strip_tags($str);
  if($str != $noTags){
    $array[]= 'La bio ne doit pas contenir de tag html<br>';
  }
  if (count($array)==0){
    $nom = mysqli_escape_string($bd,$_POST['txtNom']);
    $jour=(int)$_POST['selNais_j'];
    $mois=(int)$_POST['selNais_m'];
    $annee=(int)$_POST['selNais_a'];
    $date=$annee*10000+$mois*100+$jour;
    $ville= mysqli_escape_string($bd,$_POST['txtVille']);
    $bio= mysqli_escape_string($bd,$_POST['txtBio']);
    $sql="UPDATE users
        SET usNom='$nom', usDateNaissance='$date',usVille='$ville',usBio='$bio'
        WHERE usID={$_SESSION['id']}";
    $res=mysqli_query($bd, $sql) or KG_bd_erreur($bd, $sql);
  }
  return $array;
}

function errorform2($bd){
  $array = array();
  $value=$_POST['txtMail'];
  $str=trim($value);
  $noTags=strip_tags($str);
  $arob=filter_var($str, FILTER_VALIDATE_EMAIL);
  if(!$arob || $str == ''){
    $array[]= 'L\'adresse mail est obligatoire/L\'adresses mail n\'est pas valide<br>';
  }
  $value=$_POST['txtWeb'];
  $str=trim($value);
  $noTags=strip_tags($str);
  $url=filter_var($str, FILTER_VALIDATE_URL);
  if(!$url && $str!=''){
    $array[]= 'L\'adresse web n\'est pas correct<br>';
  }
  if (count($array)==0){
    $mail = mysqli_escape_string($bd,$_POST['txtMail']);
    $web=mysqli_escape_string($bd,$_POST['txtWeb']);
    $sql="UPDATE users
        SET usMail='$mail', usWeb='$web'
        WHERE usID={$_SESSION['id']}";
    $res=mysqli_query($bd, $sql) or KG_bd_erreur($bd, $sql);
  }
  return $array;
}

function errorform3($bd){
  $array=array();
  $value=$_POST['txtPass'];
  $str=trim($value);
  $taille=mb_strlen($str,'UTF-8');
  $value=$_POST['txtVerif'];
  $str2=trim($value);
  $mdp='';
  if($str!=''){
    if($taille<6){
      $array[]= 'Le mot de passe est obligatoire et doit avoir au moins 6 caractères<br>';
    }
    if($str != $str2){
      $array[]= 'Le mot de passe est différent dans les 2 zones<br>';
    }
    $pwd = mysqli_escape_string($bd,password_hash($str, PASSWORD_DEFAULT));
    $mdp=",usPasse='$pwd'";
  }
  if(!file_exists('../upload/'.$_SESSION['id'].'.jpg')&&$_POST['pp']==1){
    $array[]= 'Vous ne possedez pas de photo de profil<br>';
  }
  //test validité de l'image
  if($_FILES['leFichier']['size']!=0){
    if($_FILES['leFichier']['error']!=0){
      $array[]= 'Erreur lors du telechargement de l\'image<br>';
    }else{
      $infosfichier = pathinfo($_FILES['leFichier']['name']);
      $extension_upload = '';
      if (isset($infosfichier['extension'])) {
        $extension_upload = $infosfichier['extension'];
      }
      $extension_autorisees = array('jpg', 'jpeg');
      if (!in_array($extension_upload,$extension_autorisees)) {
        $array[]= 'Mauvais format de l\'image<br>';
      }
      if (!is_uploaded_file($_FILES['leFichier']['tmp_name'])){
        $array[]= 'Erreur lors de l\'upload<br>';
      }
    }
  }
  //uptade de la bd && upload + rename du fichier//
  if (count($array)==0) {
    if($_FILES['leFichier']['size']!=0){
      $Dest='../upload/'.$_FILES['leFichier']['name'];
      move_uploaded_file($_FILES['leFichier']['tmp_name'], $Dest);
      rename('../upload/'.$_FILES['leFichier']['name'], '../upload/'.$_SESSION['id'].'.jpg');
      imageResize('../upload/'.$_SESSION['id'].'.jpg');
    }
    $pp='usAvecPhoto='.(int)$_POST['pp'];
    $sql="UPDATE users
        SET $pp $mdp 
        WHERE usID={$_SESSION['id']}";
    $res=mysqli_query($bd, $sql) or KG_bd_erreur($bd, $sql);
  }
  return $array;
}

function imageResize($image){
  $xy=50;
  $size=getimagesize($image);
  $old_img=imagecreatefromjpeg($image);
  $new_img=imagecreate($xy,$xy);
  $mini_img=imagecreatetruecolor($xy,$xy)or$mini_img=imagecreate($xy,$xy);
  imagecopyresized($mini_img,$old_img,0,0,0,0,$xy,$xy,$size[0],$size[1]);
  imagejpeg($mini_img,$image);
  imagedestroy($mini_img);
  //demander si d'autre type de fichier sont accepté
  //faire la verification quqe limage est bien un jpg
}



$bd= KG_bd_connect();
session_start();
KG_verifie_authentification();
$usID=$_SESSION['id'];
//debut du html
KG_aff_debut('../styles/index.css', 'compte');
//header du html
KG_aff_entete('n','Paramètres de mon compte' , 'compte.php');
//patie infos du html
KG_aff_infos($bd, $_SESSION['id']);

$sql="SELECT * FROM users WHERE usID='$usID'";
$res=mysqli_query($bd, $sql) or KG_bd_erreur($bd, $sql);
$T=mysqli_fetch_assoc($res);
$nom=protect($T['usNom']);
$date=protect($T['usDateNaissance']);
$annee=substr($date, 0 , 4);
$jours=substr($date, -2,2);
$mois=substr($date, 4,2);
$ville=protect($T['usVille']);
$bio=protect($T['usBio']);
$mail=protect($T['usMail']);
$web=protect($T['usWeb']);
$photo=profilePicture($usID , $T['usAvecPhoto']);



if (isset($_POST['btnValider1'])) {
  $array=errorform1($bd);
  if(count($array)==0){
    header('location: compte.php');
  }
  $nom=protect($_POST['txtNom']);
  if (isset($_POST['txtVille'])) {
    $ville=protect($_POST['txtVille']);
  }
  $jours=(int)$_POST['selNais_j'];
  $mois=(int)$_POST['selNais_m'];
  $annee=(int)$_POST['selNais_a'];
  if (isset($_POST['txtBio'])) {
    $bio=protect($_POST['txtBio']);
  }
}
if (isset($_POST['btnValider2'])) {
  $array=errorform2($bd);
  if(count($array)==0){
    header('location: compte.php');
  }
  $mail=protect($_POST['txtMail']);
  if (isset($_POST['txtWeb'])) {
    $web=protect($_POST['txtWeb']);
  }
}
if(isset($_POST['btnValider3'])){
  $array=errorform3($bd);
  if(count($array)==0){
    header('location: compte.php');
  }
}

if(!isset($_POST['btnValider1'])&& !isset($_POST['btnValider2'])&& !isset($_POST['btnValider3'])){
    formulaire($nom,$jours,$mois,$annee,$ville,$bio,$mail,$web,$photo,$T['usAvecPhoto']);
}else{
    formulaire($nom,$jours,$mois,$annee,$ville,$bio,$mail,$web,$photo,$T['usAvecPhoto'],$array);
}

//affichage du pied de page 
KG_aff_pied();
//fin du html
KG_aff_fin();
exit;*/
?>