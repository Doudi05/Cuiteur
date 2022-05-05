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
//wa_est_authentifie();

// traitement si soumission du formulaire de connexion
$err = isset($_POST['btnConnexion']) ? wal_traitement_connexion() : array();

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

    // réaffichage des données soumises en cas d'erreur, sauf les mots de passe 
    if (isset($_POST['btnValider'])){
        $values = wa_html_proteger_sortie($_POST);
    }
    else{
        $values['nomprenom'] = $values['naissance'] = $values['ville'] = $values['bio'] = $values['email'] = $values['web'] = '';
    }
        
    if (count($err) > 0) {
        echo '<p class="error">Les erreurs suivantes ont été détectées : ';
        foreach ($err as $v) {
            echo '<br> - ', $v;
        }
        echo '</p>';    
    }


    echo    
            '<div id="divconnection">',
            '<p>Cette page vous permet de modifier les informations relatives à votre compte. </p>',
            '<br>',
            '<h3>Informations personnelles</h3>',
            '<form method="post" action="compte.php">',
                '<table>';

    wa_aff_ligne_input('Nom', array('type' => 'text', 'name' => 'nomprenom', 'value' => $values['nomprenom'], 'required' => null));
    wa_aff_ligne_input('Date de naissance', array('type' => 'date', 'name' => 'naissance', 'value' => $values['naissance'], 'required' => null));
    wa_aff_ligne_input('Ville', array('type' => 'text', 'name' => 'ville', 'value' => $values['ville'], 'required' => null));
    wa_aff_ligne_input('Mini-bio', array('type' => 'text', 'name' => 'bio', 'value' => $values['bio'], 'required' => null));
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
    wa_aff_ligne_input('Site web', array('type' => 'text', 'name' => 'web', 'value' => $values['web'], 'required' => null));

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

    wa_aff_ligne_input('Votre mot de passe :', array('type' => 'password', 'name' => 'passe1', 'value' => '', 'required' => null));
    wa_aff_ligne_input('Répétez le mot de passe :', array('type' => 'password', 'name' => 'passe2', 'value' => '', 'required' => null));
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