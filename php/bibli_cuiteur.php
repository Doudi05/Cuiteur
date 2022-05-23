<?php

/*********************************************************
 *        Bibliothèque de fonctions spécifiques          *
 *               à l'application Cuiteur                 *
 *********************************************************/

 // Force l'affichage des erreurs
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting( E_ALL );

// Définit le fuseau horaire par défaut à utiliser. Disponible depuis PHP 5.1
date_default_timezone_set('Europe/Paris');

//définition de l'encodage des caractères pour les expressions rationnelles multi-octets
mb_regex_encoding ('UTF-8');

define('IS_DEV', true);//true en phase de développement, false en phase de production

// Paramètres pour accéder à la base de données
define('BD_SERVER', 'localhost');
define('BD_NAME', 'cuiteur_bd');
define('BD_USER', 'root');
define('BD_PASS', '');

// define('BD_SERVER', 'localhost');
// define('BD_NAME', 'akel_cuiteur');
// define('BD_USER', 'akel_u');
// define('BD_PASS', 'akel_p');


// paramètres de l'application
define('LMIN_PSEUDO', 4);
define('LMAX_PSEUDO', 30); //longueur du champ dans la base de données
define('LMAX_EMAIL', 80); //longueur du champ dans la base de données
define('LMAX_NOMPRENOM', 60); //longueur du champ dans la base de données
define('LMAX_VILLE', 50); //longueur du champ dans la base de données
define('LMAX_BIO', 255); //longueur du champ dans la base de données


define('LMIN_PASSWORD', 4);
define('LMAX_PASSWORD', 20);

define('AGE_MIN', 18);
define('AGE_MAX', 120);

//Nombre de tags et blablas souhait�s a l'�cran
define('MAX_TAG',4);
define('MAX_BLA',4);


//_______________________________________________________________
/**
 * Génération et affichage de l'entete des pages
 *
 * @param ?string    $titre  Titre de l'entete (si null, affichage de l'entete de cuiteur.php avec le formulaire)
 */
function wa_aff_entete(?string $titre = null):void{
    if ($titre === null){
        echo    
            '<div id="bcContenu">',
                '<header>',
                    '<a href="deconnexion.php" title="Se déconnecter de cuiteur"></a>',
                    '<a href="cuiteur.php" title="Ma page d\'accueil"></a>',
                    '<a href="recherche.php" title="Rechercher des personnes à suivre"></a>',
                    '<a href="compte.php" title="Modifier mes informations personnelles"></a>',
                    '<form action="cuiteur.php" method="POST">',
                        '<textarea name="txtMessage"></textarea>',
                        '<input type="submit" name="btnPublier" value="" title="Publier mon message">',
                    '</form>';
    }
    elseif($titre === "Connectez-vous"){
        echo    
            '<div id="bcContenu">',
                '<header id="deconn">',
                    '<h1>Connectez-vous</h1>';
    }
    elseif($titre === "Inscription"){
        echo
            '<div id="bcContenu">',
                '<header id="deconn">',
                    '<h1>Inscription</h1>';
    }
    else {
        echo    
            '<div id="bcContenu">',
                '<header>',
                    '<a href="deconnexion.php" title="Se déconnecter de cuiteur"></a>',
                    '<a href="cuiteur.php" title="Ma page d\'accueil"></a>',
                    '<a href="recherche.php" title="Rechercher des personnes à suivre"></a>',
                    '<a href="compte.php" title="Modifier mes informations personnelles"></a>',
                    '<h1>'.$titre.'</h1>';
    }
    echo    '</header>';    
}

//_______________________________________________________________
/**
 * Génération et affichage du bloc d'informations utilisateur
 *
 * @param bool    $connecte  true si l'utilisateur courant s'est authentifié, false sinon
 */
function wa_aff_infos(bool $connecte = true):void{
    echo '<aside>';
    if ($connecte){
        echo
            '<h3>Utilisateur</h3>',
            '<ul>',
                '<li>',
                    '<img src="../images/pdac.jpg" alt="photo de l\'utilisateur">',
                    '<a href="../index.html" title="Voir mes infos">pdac</a> Pierre Dac',
                '</li>',
                '<li><a href="../index.html" title="Voir la liste de mes messages">100 blablas</a></li>',
                '<li><a href="../index.html" title="Voir les personnes que je suis">123 abonnements</a></li>',
                '<li><a href="../index.html" title="Voir les personnes qui me suivent">34 abonnés</a></li>',                 
            '</ul>',
            '<h3>Tendances</h3>',
            '<ul>',
                '<li>#<a href="../index.html" title="Voir les blablas contenant ce tag">info</a></li>',
                '<li>#<a href="../index.html" title="Voir les blablas contenant ce tag">lol</a></li>',
                '<li>#<a href="../index.html" title="Voir les blablas contenant ce tag">imbécile</a></li>',
                '<li>#<a href="../index.html" title="Voir les blablas contenant ce tag">fairelafete</a></li>',
                '<li><a href="tendances.php">Toutes les tendances</a><li>',
            '</ul>',
            '<h3>Suggestions</h3>',             
            '<ul>',
                '<li>',
                    '<img src="../images/yoda.jpg" alt="photo de l\'utilisateur">',
                    '<a href="../index.html" title="Voir mes infos">yoda</a> Yoda',
                '</li>',       
                '<li>',
                    '<img src="../images/paulo.jpg" alt="photo de l\'utilisateur">',
                    '<a href="../index.html" title="Voir mes infos">paulo</a> Jean-Paul Sartre',
                '</li>',
                '<li><a href="suggestions.php">Plus de suggestions</a></li>',
            '</ul>';
    }
    echo '</aside>',
         '<main>';   
}

//_______________________________________________________________
/**
 * Génération et affichage du pied de page
 *
 */
function wa_aff_pied(): void{
    echo    '</main>',
            '<footer>',
                '<a href="../index.html">A propos</a>',
                '<a href="../index.html">Publicité</a>',
                '<a href="../index.html">Patati</a>',
                '<a href="../index.html">Aide</a>',
                '<a href="../index.html">Patata</a>',
                '<a href="../index.html">Stages</a>',
                '<a href="../index.html">Emplois</a>',
                '<a href="../index.html">Confidentialité</a>',
            '</footer>',
    '</div>';
}

//_______________________________________________________________
/**
* Affichages des résultats des SELECT des blablas.
*
* La fonction gére la boucle de lecture des résultats et les
* encapsule dans du code HTML envoyé au navigateur 
*
* @param mysqli_result  $r       Objet permettant l'accès aux résultats de la requête SELECT
*/
function wa_aff_blablas(mysqli_result $r, $nombrebl=1): void {
    if ($nombrebl==0) {
        echo '<div id="blablavide">',
                 '<p>Aucun blablas à afficher</p>',
             '</div>';
    }else{
        while ($t = mysqli_fetch_assoc($r)) {
            if ($t['oriID'] === null){
                $id_orig = $t['autID'];
                $pseudo_orig = $t['autPseudo'];
                $photo = $t['autPhoto'];
                $nom_orig = $t['autNom'];
            }
            else{
                $id_orig = $t['oriID'];
                $pseudo_orig = $t['oriPseudo'];
                $photo = $t['oriPhoto'];
                $nom_orig = $t['oriNom'];
            }
            echo    
                '<li>', 
                    '<img src="../', ($photo == 1 ? "upload/$id_orig.jpg" : 'images/anonyme.jpg'), 
                    '" class="imgAuteur" alt="photo de l\'auteur">',
                    wa_html_a('utilisateur.php', '<strong>'.wa_html_proteger_sortie($pseudo_orig).'</strong>','id', $id_orig, 'Voir mes infos'), 
                    ' ', wa_html_proteger_sortie($nom_orig),
                    ($t['oriID'] !== null ? ', recuité par '
                                            .wa_html_a( 'utilisateur.php','<strong>'.wa_html_proteger_sortie($t['autPseudo']).'</strong>',
                                                        'id', $t['autID'], 'Voir mes infos') : ''),
                    '<br>',
                    wa_html_proteger_sortie($t['blTexte']),
                    '<p class="finMessage">',
                    wa_amj_clair($t['blDate']), ' à ', wa_heure_clair($t['blHeure']);
                    if ($t['autID']!=$_SESSION['id']){
                        echo '<a href="cuiteur.php?txtBlabla=@'.$t['oriPseudo'].'&blID='.$t['blID'].'">Répondre</a> <a href="cuiteur.php?recuiter='.$t['blID'].'">Recuiter</a><p>';
                    }
                    else{
                        echo '<a href="cuiteur.php?delete='.$t['blID'].'">Supprimer</a><p>';
                    }
                '</li>';
        }
    }
}

//_______________________________________________________________
/**
* Détermine si l'utilisateur est authentifié
*
* @global array    $_SESSION 
* @return bool     true si l'utilisateur est authentifié, false sinon
*/
function wa_est_authentifie(): bool {
    return  isset($_SESSION['usID']);
}

//_______________________________________________________________
/**
 * Termine une session et effectue une redirection vers la page transmise en paramètre
 *
 * Elle utilise :
 *   -   la fonction session_destroy() qui détruit la session existante
 *   -   la fonction session_unset() qui efface toutes les variables de session
 * Elle supprime également le cookie de session
 *
 * Cette fonction est appelée quand l'utilisateur se déconnecte "normalement" et quand une 
 * tentative de piratage est détectée. On pourrait améliorer l'application en différenciant ces
 * 2 situations. Et en cas de tentative de piratage, on pourrait faire des traitements pour 
 * stocker par exemple l'adresse IP, etc.
 * 
 * @param string    URL de la page vers laquelle l'utilisateur est redirigé
 */
function wa_session_exit(string $page = '../index.php'):void {
    session_destroy();
    session_unset();
    $cookieParams = session_get_cookie_params();
    setcookie(session_name(), 
            '', 
            time() - 86400,
            $cookieParams['path'], 
            $cookieParams['domain'],
            $cookieParams['secure'],
            $cookieParams['httponly']
        );
    header("Location: $page");
    exit();
}

/*
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
****************************************************************************
*/

/**
 * Fonction qui affiche les checkbox pour la list des utilisateur (s'abonner / se desabonner)
 *
 * @param mysqli     $bd    base de donnée
 * @param int    $id    id de l'utilisateur concerné
**/
function wa_checkbox($bd,$id){
	$sql="SELECT count(eaIDUser) FROM estabonne WHERE eaIDUser= {$_SESSION['id']} AND eaIDAbonne=$id";
	$checkbox=wa_bd_send_request($bd, $sql);
	$check=mysqli_fetch_assoc($checkbox);
	if ($check['count(eaIDUser)']==0) {
		echo
		'<p class=abodesabo>',
		'<input type=checkbox id=',$id,' name=',$id,' value=0 /><label for=',$id,'>S\'abonner</label>',
		'</p>';
	}else{
		echo
		'<p class=abodesabo>',
		'<input type=checkbox id=',$id,' name=',$id,' value=1 /><label for=',$id,'>Se desabonner</label>',
		'</p>';
	}
}

/**
 * Fonction qui affiche la liste des utilisateur que l'on souhaite
 *
 * @param mysqli     $bd    base de donnée
 * @param retour sql    $res    resultat de la requete sql
**/
function wa_aff_recherche($bd,$res,$php=''){
	echo
	'<form action=# method=post>',
		'<ul id="infoAbo">';
		$count=0;
		$arrayID=array();
		while ($RECH=mysqli_fetch_assoc($res)) {
			$pseudo=$RECH['usPseudo'];
			$nom=$RECH['usNom'];
			$id=$RECH['usID'];
			$photo=wa_html_proteger_sortie(profilePicture($RECH['usID'],$RECH['usAvecPhoto']));
			$lienprofil='utilisateur.php?id='.cryptage(wa_html_proteger_sortie($RECH['usID']));
			$lienblabla='blablas.php?id='.cryptage(wa_html_proteger_sortie($RECH['usID']));
			$lienabonnements='abonnements.php?id='.cryptage(wa_html_proteger_sortie($RECH['usID']));
			$lienabooné='abonnes.php?id='.cryptage(wa_html_proteger_sortie($RECH['usID']));
			$lienmentions='mentions.php?id='.cryptage(wa_html_proteger_sortie($RECH['usID']));

			//requete pour avoir nb blablas & nb mentions & nb abonnés & nb abonnements
			$bl= "SELECT * , COUNT(blID)
			FROM users , blablas
			WHERE users.usID = blablas.blIDAuteur
			AND usID='$id'";
			$nbbl=wa_bd_send_request($bd, $bl);
			$T=mysqli_fetch_assoc($nbbl);

			/*REQUETE POUR AVOIR LE NOMBRE D'ABONNEMENTS*/
			$nbabonnements= "SELECT COUNT(eaIDAbonne)
					FROM estabonne
					WHERE eaIDUser='$id'";
			$abo=wa_bd_send_request($bd, $nbabonnements);
			$A=mysqli_fetch_assoc($abo);

			//--------------------------------------------------------------------//
            
			/*REQUETE POUR AVOIR LE NOMBRE D'ABONNEES*/
			$nbabonne= "SELECT COUNT(estabonne.eaIDUser)
			FROM estabonne
			WHERE estabonne.eaIDAbonne ='$id'";
			$nbabo=wa_bd_send_request($bd, $nbabonne);
			$Abo=mysqli_fetch_assoc($nbabo);

			/*REQUETE POUR AVOIR LE NOMBRE DE MENTIONS*/
			$mentions= "SELECT COUNT(meIDBlabla)
			FROM mentions
			WHERE meIDUser ='$id'";
			$nbmentions=wa_bd_send_request($bd, $mentions);
			$M=mysqli_fetch_assoc($nbmentions);
			$nbblablas=wa_html_proteger_sortie($T['COUNT(blID)']);
			$nbment=wa_html_proteger_sortie($M['COUNT(meIDBlabla)']);
			$nbabo=wa_html_proteger_sortie($Abo['COUNT(estabonne.eaIDUser)']);
			$nbabonnements=wa_html_proteger_sortie($A['COUNT(eaIDAbonne)']);

			if($php!="suggestions.php"){
                echo
                    '<li><img class="imgAuteur" alt="',$nom,'" src="',$photo,'"/>',
                        wa_html_a('utilisateur.php?id='. $id, $pseudo), ' ', $nom,
                        '<br>',
                        '<a href="',$lienblabla,'">',$nbblablas,' blablas</a> - ',
                        '<a href="',$lienmentions,'">',$nbment,' mentions</a> - ',
                        '<a href="',$lienabooné,'">',$nbabo,' abonnés</a> - ',
                        '<a href="',$lienabonnements,'">',$nbabonnements,' abonnements</a>
                        <br><br><br>';
                        if($_SESSION['id']!=$RECH['usID']){
                            wa_checkbox($bd,$RECH['usID'],$nom);
                        }
                echo '</li>';
                //wa_afficher_profil($bd, $id, "abonnements");
			}else{
				$sql="SELECT count(eaDate) FROM estabonne WHERE eaIDAbonne= {$RECH['usID']} AND eaIDUser={$_SESSION['id']}";
				$estabo=wa_bd_send_request($bd, $sql);
				$EA=mysqli_fetch_assoc($estabo);
				if($EA['count(eaDate)']==0){
					echo
                    '<li><img class="imgAuteur" alt="',$nom,'" src="',$photo,'"/>',
                        wa_html_a('utilisateur.php?id='. $id, $pseudo), ' ', $nom,
                        '<br>',
                        '<a href="',$lienblabla,'">',$nbblablas,' blablas</a> - ',
                        '<a href="',$lienmentions,'">',$nbment,' mentions</a> - ',
                        '<a href="',$lienabooné,'">',$nbabo,' abonnés</a> - ',
                        '<a href="',$lienabonnements,'">',$nbabonnements,' abonnements</a>
                        <br><br><br>';
                        if($_SESSION['id']!=$RECH['usID']){
                            wa_checkbox($bd,$RECH['usID'],$nom);
                        }
                    echo '</li>';
				}
				$arrayID[]=$RECH['usID'];
			}
			$count=$count+1;
		}
	echo
		'</ul>',
		'<input type=submit name=btnValider value=Valider />',
	'</form>';
}

/**
 * Fonction qui affiche la liste des suggestions
 *
 * @param mysqli     $bd    base de donnée
 * @param retour sql    $res    resultat de la requete sql
**/
function wa_aff_suggestions($bd,$php='',$nb=''){
	echo
	'<form action=# method=post>',
		'<ul>';
		$count=0;
		$arrayID=array();
        if($php=='suggestions.php'){
			$sql="SELECT * FROM users , estabonne
						WHERE eaIDAbonne = usID
						GROUP BY usID
						ORDER BY COUNT(eaIDUser) DESC
						LIMIT 10";
			$topabo=wa_bd_send_request($bd, $sql);
			while($TA=mysqli_fetch_assoc($topabo)){
				if($count==$nb){
					break;
				}
				$pseudo=$TA['usPseudo'];
				$nom=$TA['usNom'];
				$id=$TA['usID'];
				$photo=wa_html_proteger_sortie(profilePicture($TA['usID'],$TA['usAvecPhoto']));
				$lienprofil='utilisateur.php?id='.cryptage(wa_html_proteger_sortie($TA['usID']));
				$lienblabla='blablas.php?id='.cryptage(wa_html_proteger_sortie($TA['usID']));
				$lienabonnement='abonnement.php?id='.cryptage(wa_html_proteger_sortie($TA['usID']));
				$lienabooné='abonnes.php?id='.cryptage(wa_html_proteger_sortie($TA['usID']));
				$lienmentions='mentions.php?id='.cryptage(wa_html_proteger_sortie($TA['usID']));
				//requete pour avoir nb blablas & nb mentions & nb abonnés & nb abonnement
				$bl= "SELECT * , COUNT(blID)
				FROM users , blablas
				WHERE users.usID = blablas.blIDAuteur
				AND usID='$id'";
				$nbbl=wa_bd_send_request($bd, $bl);
				$T=mysqli_fetch_assoc($nbbl);
				/*REQUETE POUR AVOIR LE NOMBRE D'ABONNEMENT*/
				$nbabonnement= "SELECT COUNT(eaIDAbonne)
						FROM estabonne
						WHERE eaIDUser='$id'";
				$abo=wa_bd_send_request($bd, $nbabonnement);
				$A=mysqli_fetch_assoc($abo);
				//--------------------------------------------------------------------//
				/*REQUETE POUR AVOIR LE NOMBRE D'ABONNEE*/
				$nbabonne= "SELECT COUNT(estabonne.eaIDUser)
				FROM estabonne
				WHERE estabonne.eaIDAbonne ='$id'";
				$nbabo=wa_bd_send_request($bd, $nbabonne);
				$Abo=mysqli_fetch_assoc($nbabo);
				/*REQUETE POUR AVOIR LE NOMBRE DE MENTIONS*/
				$mentions= "SELECT COUNT(meIDBlabla)
				FROM mentions
				WHERE meIDUser ='$id'";
				$nbmentions=wa_bd_send_request($bd, $mentions);
				$M=mysqli_fetch_assoc($nbmentions);
				$nbblablas=wa_html_proteger_sortie($T['COUNT(blID)']);
				$nbment=wa_html_proteger_sortie($M['COUNT(meIDBlabla)']);
				$nbabo=wa_html_proteger_sortie($Abo['COUNT(estabonne.eaIDUser)']);
				$nbabonnement=wa_html_proteger_sortie($A['COUNT(eaIDAbonne)']);

				$sql="SELECT count(eaDate) FROM estabonne WHERE eaIDAbonne= {$TA['usID']} AND eaIDUser={$_SESSION['id']}";
				$estabo=wa_bd_send_request($bd, $sql);
				$EA=mysqli_fetch_assoc($estabo);
				if($EA['count(eaDate)']==0 && !in_array($TA['usID'],$arrayID) && $TA['usID']!=$_SESSION['id']){
					$count=$count+1;
					echo
                    '<li><img class="imgAuteur" alt="',$nom,'" src="',$photo,'"/>',
                        wa_html_a('utilisateur.php?id='. $id, $pseudo), ' ', $nom,
                        '<br>',
                        '<a href="',$lienblabla,'">',$nbblablas,' blablas</a> - ',
                        '<a href="',$lienmentions,'">',$nbment,' mentions</a> - ',
                        '<a href="',$lienabooné,'">',$nbabo,' abonnés</a> - ',
                        '<a href="',$lienabonnement,'">',$nbabonnement,' abonnements</a>
                        <br><br><br>';
                        if($_SESSION['id']!=$TA['usID']){
                            wa_checkbox($bd,$TA['usID'],$nom);
                        }
                echo '</li>';
                }
			}
		}
    echo
		'</ul>',
		'<input type=submit name=btnValider value=Valider />',
	'</form>';
}

/**
 * Fonction qui permet d'afficher le profil d'un utilisateur
 *
 * @param mysqli     $bd    base de donnée
 * @param int    $id    id de l'utilisateur a afficher
**/
function wa_afficher_profil($bd, $id, string $page = ""){
	/*REQUETE POUR AVOIR LES INFOS DE LA TABLE USERS*/
	$sql= "SELECT * , COUNT(blID)
			FROM users , blablas
			WHERE users.usID = blablas.blIDAuteur
			AND usID='$id'";
	$res=wa_bd_send_request($bd, $sql);
	$T=mysqli_fetch_assoc($res);
	/*REQUETE POUR AVOIR LE NOMBRE D'abonnements*/
	$nbabonnements= "SELECT COUNT(eaIDAbonne)
			FROM estabonne
			WHERE eaIDUser='$id'";
	$abo=wa_bd_send_request($bd, $nbabonnements);
	$A=mysqli_fetch_assoc($abo);
	//--------------------------------------------------------------------//
	/*REQUETE POUR AVOIR LE NOMBRE D'ABONNEE*/
	$nbabonne= "SELECT *, COUNT(estabonne.eaIDUser)
	FROM estabonne
	WHERE estabonne.eaIDAbonne ='$id'";
	$nbabo=wa_bd_send_request($bd, $nbabonne);
	$Abo=mysqli_fetch_assoc($nbabo);


	//test pour savoir si on est deja abonné
	$estabonner= "SELECT *
	FROM estabonne
	WHERE estabonne.eaIDAbonne ='$id'";
	$testabo=wa_bd_send_request($bd, $estabonner);
	$estabo=false;
	while ($TESTABO=mysqli_fetch_assoc($testabo)) {
		if($TESTABO['eaIDUser']==$_SESSION['id']){
			$estabo=true;
			break;
		}
	}
	/*REQUETE POUR AVOIR LE NOMBRE DE MENTIONS*/
	$mentions= "SELECT COUNT(meIDBlabla)
	FROM mentions
	WHERE meIDUser ='$id'";
	$nbmentions=wa_bd_send_request($bd, $mentions);
	$M=mysqli_fetch_assoc($nbmentions);

	/*INITIALISATION DE VARIABLE*/
	$nbment=wa_html_proteger_sortie($M['COUNT(meIDBlabla)']);
	$nbabos=wa_html_proteger_sortie($Abo['COUNT(estabonne.eaIDUser)']);
	$abonnements=wa_html_proteger_sortie($A['COUNT(eaIDAbonne)']);
	$pp=profilePicture($id, $T['usAvecPhoto']);
	$pseudo=wa_html_proteger_sortie($T['usPseudo']);
	$ville="Non renseigné";
	if($T['usVille']!=''){
		$ville=wa_html_proteger_sortie($T['usVille']);
	}
	$web='Non renseigné';
	if($T['usWeb']!=''){
		$web=wa_html_proteger_sortie($T['usWeb']);
	}
	$bio='Non renseigné';
	if($T['usBio']!=''){
		$bio=wa_html_proteger_sortie($T['usBio']);
	}
	$nbblablas=wa_html_proteger_sortie($T['COUNT(blID)']);
	$monprofil=false;
	if($_SESSION['id']==$id){
		$monprofil=true;
	}
	$nom=wa_html_proteger_sortie($T['usNom']);

	/*AFFICHER LE CONTENU*/
    echo '<div id=divCompte>';
    if ($page == "") {
        echo
            '<ul id="infoUtilisateur">',
                '<li><img class="imgAuteur" src="',$pp,'" alt="',$pseudo,'">',
                wa_html_a('utilisateur.php?id='. $id, $pseudo), ' ', $nom,
                '<br>',
                '<a href="blablas.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',$nbblablas,' blablas</a> - 
                <a href="mentions.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',$nbment,' mentions</a> - 
                <a href="abonnes.php?id=',cryptage(wa_html_proteger_sortie($id)),' ">',$nbabos,' abonnés</a> - 
                <a href="abonnements.php?id=',cryptage(wa_html_proteger_sortie($id)),' ">',$abonnements,' abonnements</a>
                <br><br><br>
                </li>',
            '</ul>';
    }elseif ($page == "utilisateur") {
        echo
            '<ul id="infoUtilisateur">',
                '<li><img class="imgAuteur" src="',$pp,'" alt="',$pseudo,'">',
                wa_html_a('utilisateur.php?id='. $id, $pseudo), ' ', $nom,
                '<br>',
                '<a href="blablas.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',$nbblablas,' blablas</a> - 
                <a href="mentions.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',$nbment,' mentions</a> - 
                <a href="abonnes.php?id=',cryptage(wa_html_proteger_sortie($id)),' ">',$nbabos,' abonnés</a> - 
                <a href="abonnements.php?id=',cryptage(wa_html_proteger_sortie($id)),' ">',$abonnements,' abonnements</a>
                </li>',
            '</ul>',

            '<table id=descriptionUtilisateur>',
                '<tr><td><STRONG>Date de naissance : </STRONG></td>', '<td>', wa_amj_clair($T['usDateNaissance']), '<br></td></tr>',
                '<tr><td><STRONG>Date d\'inscription : </STRONG></td>', '<td>' ,wa_amj_clair($T['usDateInscription']),'<br></td></tr>',
                '<tr><td><STRONG>Ville de résidence : </STRONG></td>', '<td>' ,$ville,'<br></td></tr>',
                
                '<tr><td><STRONG>Mini-bio : </STRONG></td>', '<td>', $bio, '<br></td></tr>',
                '<tr><td><STRONG>Site Web : </STRONG></td>', '<td>', $web, '<br></td></tr>',
            '</table>';
        if($estabo==true){
            echo
                '<form action=utilisateur.php method=post>',
                    '<input type=submit name=desabonne value="se désabonner" size=10 />',
                    '<input type=hidden name=id value=',wa_html_proteger_sortie($id),' />',
                '</form>';
        }else{
            if($monprofil==false){
                echo
                '<form action=utilisateur.php method=post>',
                    '<input type=submit name=sabonner value="s\'abonner" size=10 />',
                    '<input type=hidden name=id value=',wa_html_proteger_sortie($id),' >',
                '</form>';
            }
        }
    }elseif ($page == "abonnements") {
        echo
            '<div id=soustitre>',
                '<img src="',$pp,'" alt="',$pseudo,'">',
                wa_html_a('utilisateur.php?id='. $id, $pseudo), ' ', $nom,
                '<ul>',
                    '<li><a href="blablas.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',$nbblablas,' blablas</a> - 
                    <a href="mentions.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',$nbment,' mentions</a> - 
                    <a href="abonnes.php?id=',cryptage(wa_html_proteger_sortie($id)),' ">',$nbabos,' abonnés</a> - 
                    <a href="abonnements.php?id=',cryptage(wa_html_proteger_sortie($id)),' ">',$abonnements,' abonnements</a>
                    </li>',
                '</ul>',
            '</div>';
    }
    echo '</div>';
}
?>
