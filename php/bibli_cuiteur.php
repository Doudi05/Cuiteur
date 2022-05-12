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

/*define('BD_SERVER', 'localhost');
define('BD_NAME', 'akel_cuiteur');
define('BD_USER', 'akel_u');
define('BD_PASS', 'akel_p');*/


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
                '<li><a href="../index.html">Toutes les tendances</a><li>',
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
                '<li><a href="../index.html">Plus de suggestions</a></li>',
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
function wa_aff_blablas(mysqli_result $r): void {
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
function MLM_GK_checkbox($bd,$id){
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
function MLM_GK_aff_recherche($bd,$res,$php='',$nb=''){
	echo
	'<form action=# method=post>',
		'<ul>';
		$count=0;
		$arrayID=array();
		while ($RECH=mysqli_fetch_assoc($res)) {
			$pseudo=$RECH['usPseudo'];
			$nom=$RECH['usNom'];
			$id=$RECH['usID'];
			$photo=wa_html_proteger_sortie(profilePicture($RECH['usID'],$RECH['usAvecPhoto']));
			$lienprofil='utilisateur.php?id='.cryptage(wa_html_proteger_sortie($RECH['usID']));
			$lienblabla='blablas.php?id='.cryptage(wa_html_proteger_sortie($RECH['usID']));
			$lienabonnement='abonnement.php?id='.cryptage(wa_html_proteger_sortie($RECH['usID']));
			$lienabooné='abonnes.php?id='.cryptage(wa_html_proteger_sortie($RECH['usID']));
			$lienmentions='mentions.php?id='.cryptage(wa_html_proteger_sortie($RECH['usID']));
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

			if($php!='suggestions.php'){
			echo
			'<li class="resultatRecherche">',
				'<img class="imgAuteur" alt="',$nom,'" src="',$photo,'"/>',
                '<p><a href="',$lienprofil,'">',$pseudo,'</a> ',$nom,'</p>',
                '<ul>',
	            	'<li><a href="',$lienblabla,'">',$nbblablas,' blablas</a> - </li>',
					'<li><a href="',$lienmentions,'">',$nbment,' mentions</a> - </li>',
					'<li><a href="',$lienabooné,'">',$nbabo,' abonnés</a> - </li>',
					'<li><a href="',$lienabonnement,'">',$nbabonnement,' abonnement</a></li>',
				'</ul>';
				if($_SESSION['id']!=$RECH['usID']){
					MLM_GK_checkbox($bd,$RECH['usID'],$nom);
				}
			}else{
				$sql="SELECT count(eaDate) FROM estabonne WHERE eaIDAbonne= {$RECH['usID']} AND eaIDUser={$_SESSION['id']}";
				$estabo=wa_bd_send_request($bd, $sql);
				$EA=mysqli_fetch_assoc($estabo);
				if($EA['count(eaDate)']==0){
					echo
					'<li class="resultatRecherche">',
						'<img class="imgAuteur" alt="',$nom,'" src="',$photo,'"/>',
		                '<p><a href="',$lienprofil,'">',$pseudo,'</a> ',$nom,'</p>',
		                '<ul>',
			            	'<li><a href="',$lienblabla,'">',$nbblablas,' blablas</a> - </li>',
							'<li><a href="',$lienmentions,'">',$nbment,' mentions</a> - </li>',
							'<li><a href="',$lienabooné,'">',$nbabo,' abonnés</a> - </li>',
							'<li><a href="',$lienabonnement,'">',$nbabonnement,' abonnement</a></li>',
						'</ul>';
						if($_SESSION['id']!=$RECH['usID']){
							MLM_GK_checkbox($bd,$RECH['usID'],$nom);
						}
				}
				$arrayID[]=$RECH['usID'];
			}
			$count=$count+1;
		}
	echo
		'</li>',
		'</ul>',
		'<input type=submit name=btnValider value=Valider />',
	'</form>';
}

?>
