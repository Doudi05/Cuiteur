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

define('MAX_PHOTO_PROFILE_WEIGHT_KB', 20);

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
function wa_aff_entete(?string $titre = null, string $rep=''):void{
    if ($titre === null){
        if($rep!==''){
			$rep='@'.$rep.' ';
            echo    
            '<div id="bcContenu">',
                '<header>',
                    '<a href="deconnexion.php" title="Se déconnecter de cuiteur"></a>',
                    '<a href="cuiteur.php" title="Ma page d\'accueil"></a>',
                    '<a href="recherche.php" title="Rechercher des personnes à suivre"></a>',
                    '<a href="compte.php" title="Modifier mes informations personnelles"></a>',
                    '<form action="cuiteur.php" method="POST">',
                        '<textarea name="txtMessage">',wa_html_proteger_sortie($rep),'</textarea>',
                        '<input type="submit" name="btnPublier" value="" title="Publier mon message">',
                    '</form>';
		}else{
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
 * @param mysqli  $bd        objet mysqli de la connexion à la base de données
 */
function wa_aff_infos(mysqli $bd = null, bool $connecte = true):void{
    $profil= "SELECT * , COUNT(blID)
			FROM users , blablas
			WHERE users.usID = blablas.blIDAuteur
			AND usID={$_SESSION['id']}";
	$pprofil=wa_bd_send_request($bd, $profil);
	$T=mysqli_fetch_assoc($pprofil);

    $lienblabla='blablas.php?id='.cryptage(wa_html_proteger_sortie($T['usID']));
    $lienabonnements='abonnements.php?id='.cryptage(wa_html_proteger_sortie($T['usID']));
    $lienabooné='abonnes.php?id='.cryptage(wa_html_proteger_sortie($T['usID']));

	//test pour avoir le nombre d'abonnement
	$nbabonnement= "SELECT COUNT(eaIDAbonne)
			FROM estabonne
			WHERE eaIDUser={$_SESSION['id']}";
	$abonnement=wa_bd_send_request($bd, $nbabonnement);
	$A=mysqli_fetch_assoc($abonnement);

	//test pour avoir le nombre d'abonne
	$nbabonne= "SELECT COUNT(estabonne.eaIDUser)
	FROM estabonne
	WHERE estabonne.eaIDAbonne ={$_SESSION['id']}";
	$abonne=wa_bd_send_request($bd, $nbabonne);
	$M=mysqli_fetch_assoc($abonne);

	//requete pour avoir les top tendances
	$toptendances= "SELECT taID , COUNT(taID)
	FROM tags
	GROUP BY taID
	ORDER BY COUNT(taIDBlabla)DESC
	LIMIT 0,4";
	$tendances=wa_bd_send_request($bd, $toptendances);
    
	//requete pour avoir les suggestions
	$suggestions= "SELECT *
					FROM users
					WHERE usID IN
					            (SELECT eaIDAbonne
					            FROM estabonne
					            WHERE eaIDUser IN
					                            (SELECT eaIDAbonne
					                            FROM estabonne
					                            WHERE eaIDUser = {$_SESSION['id']}))
					AND usID NOT IN(SELECT eaIDAbonne
					                            FROM estabonne
					                            WHERE eaIDUser = {$_SESSION['id']})
					AND usID != {$_SESSION['id']}
					LIMIT 2";
	$sugg=wa_bd_send_request($bd, $suggestions);

    $nbblablas=wa_html_proteger_sortie($T['COUNT(blID)']);
    $nbabo=wa_html_proteger_sortie($M['COUNT(estabonne.eaIDUser)']);
    $nbabonnements=wa_html_proteger_sortie($A['COUNT(eaIDAbonne)']);

    echo '<aside>';
    if ($connecte){
        echo
            '<h3>Utilisateur</h3>',
            '<ul>',
                '<li>',
                    '<img class="photoProfil" src="../',wa_html_proteger_sortie(profilePicture($T['usID'] , $T['usAvecPhoto'])), '" alt="photo de l\'utilisateur">',
                    '<label>',
                        '<a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($T['usID'])),'">',
                        wa_html_proteger_sortie($T['usPseudo']) ,
                        '</a>',
                        '  ',
                        wa_html_proteger_sortie($T['usNom']),
                    '</label>',
                '</li>',
                '<li><a href="',$lienblabla,'">',$nbblablas,' blablas</a></li>',
                '<li><a href="',$lienabonnements,'">',$nbabonnements,' abonnements</a></li>',
                '<li><a href="',$lienabooné,'">',$nbabo,' abonnés</a></li>',
            '</ul>',

            '<h3>Tendances</h3>',
            '<ul>';
            while($R=mysqli_fetch_assoc($tendances)){
                echo '<li>', '#',
						'<a title="Voir les blablas contenant ce tag" href="tendances.php?tags=',cryptage(wa_html_proteger_sortie($R['taID'])),'">',wa_html_proteger_sortie($R['taID']),'</a>',
					'</li>';
            }
            echo
                '<li><a href="tendances.php">Toutes les tendances</a><li>',
            '</ul>',

            '<h3>Suggestions</h3>',             
            '<ul>';
                $count=0;
                while($S=mysqli_fetch_assoc($sugg)){
                    $sql="SELECT count(eaDate) FROM estabonne WHERE eaIDAbonne= {$S['usID']} AND eaIDUser={$_SESSION['id']}";
                    $estabo=wa_bd_send_request($bd, $sql);
                    $EA=mysqli_fetch_assoc($estabo);
                    if($EA['count(eaDate)']==0){
                            echo
                        '<li>',
                            '<img src="',wa_html_proteger_sortie(profilePicture($S['usID'] , $S['usAvecPhoto'])),'" alt="',wa_html_proteger_sortie($S['usNom']),'" />',
                            '<label>',
                                '<a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($S['usID'])),'">',
                                wa_html_proteger_sortie($S['usPseudo']),
                                '</a>',
                                '  ',
                                wa_html_proteger_sortie($S['usNom']),
                            '</label>',
                        '</li>';
                        $count=$count+1;
                    }
                }
                $diff=2-$count;
                $sql="SELECT * FROM users , estabonne
                            WHERE eaIDAbonne = usID
                            GROUP BY usID
                            ORDER BY COUNT(eaIDUser) DESC
                            LIMIT $diff";
                $topabo=wa_bd_send_request($bd, $sql);
                while($TA=mysqli_fetch_assoc($topabo)){
                        $sql="SELECT count(eaDate) FROM estabonne WHERE eaIDAbonne= {$TA['usID']} AND eaIDUser={$_SESSION['id']}";
                        $estabo=wa_bd_send_request($bd, $sql);
                        $EA=mysqli_fetch_assoc($estabo);
                        if($EA['count(eaDate)']==0){
                            echo
                            '<li>',
                                '<img src="',wa_html_proteger_sortie(profilePicture($TA['usID'] , $TA['usAvecPhoto'])),'" alt="',wa_html_proteger_sortie($TA['usNom']),'" />',
                                '<label>',
                                    '<a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($TA['usID'])),'">',
                                    wa_html_proteger_sortie($TA['usPseudo']),
                                    '</a>',
                                    '  ',
                                    wa_html_proteger_sortie($TA['usNom']),
                                '</label>',
                            '</li>';
                        }
                }
            echo
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
* @param mysqli     $bd    base de donnée
* @param mysqli_result  $r       Objet permettant l'accès aux résultats de la requête SELECT
*/
function wa_aff_blablas(mysqli $bd, mysqli_result $r, $nombrebl=1): void {
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
                    '<label>',
                        '<strong><a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($id_orig)),'">',
                        wa_html_proteger_sortie($pseudo_orig) ,
                        '</strong></a>',
                        '  ',
                        wa_html_proteger_sortie($nom_orig),
                    '</label>';
                    if ($t['oriID'] !== null) {
                        echo
                        ', recuité par ' ,
                        '<label><strong>',
                            '<a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($t['autID'])),'">',
                            wa_html_proteger_sortie($t['autPseudo']) ,
                            '</a>',
                        '</strong></label>';
                    } else {
                        echo '';
                    }
                    echo
                    '<br>',
                    $text=blablasTagAndMentionsLink($bd,wa_html_proteger_sortie($t['blTexte'])),
                    '<p class="finMessage">',
                    wa_amj_clair($t['blDate']), ' à ', wa_heure_clair($t['blHeure']);
                    if ($t['autID']!=$_SESSION['id']){
                        echo '<a href="cuiteur.php?repondre='.$pseudo_orig.'">Répondre</a>
                            <a href="cuiteur.php?recuiter='.$t['blID'].'">Recuiter</a><p>';
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
 * Get stats about a user (usID, usPseudo, usNom, usAvecPhoto, nbBlablas, nbMentions, nbAbonnes, nbAbonnements)
 * 
 * @param mysqli   $mysqli    MySQLi object
 * @param int      $id        User's id
 * @return array              User's stats
 */
function wa_get_infos(mysqli $bd, int $id): array {
    $sql = "SELECT usID, usPseudo, usNom, usAvecPhoto
            FROM users
            WHERE usId = $id
            UNION ALL
            SELECT COUNT(*), NULL, NULL, NULL
            FROM blablas
            WHERE blIDAuteur = $id
            UNION ALL
            SELECT COUNT(*), NULL, NULL, NULL
            FROM mentions
            WHERE meIDUser = $id
            UNION ALL
            SELECT COUNT(*), NULL, NULL, NULL
            FROM estabonne
            WHERE eaIDAbonne = $id
            UNION ALL
            SELECT COUNT(*), NULL, NULL, NULL
            FROM estabonne
            WHERE eaIDUser = $id";

    $results = wa_bd_send_request($bd, $sql);
    $row = mysqli_fetch_array($results);

    // if no data, return empty array
    if ($row[0] == "0") {
        return [];
    }

    $data = array(
        'usID' => $row[0],
        'usPseudo' => $row[1],
        'usNom' => $row[2],
        'usAvecPhoto' => $row[3]
    );
    $row = mysqli_fetch_array($results);
    $data['nbBlablas'] = $row[0];
    $row = mysqli_fetch_array($results);
    $data['nbMentions'] = $row[0];
    $row = mysqli_fetch_array($results);
    $data['nbAbonnes'] = $row[0];
    $row = mysqli_fetch_array($results);
    $data['nbAbonnements'] = $row[0];
    return wa_html_proteger_sortie($data);
}

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
		$arrayID=array();
		while ($RECH=mysqli_fetch_assoc($res)) {
			$pseudo=$RECH['usPseudo'];
			$nom=$RECH['usNom'];
			$id=$RECH['usID'];
			$photo=wa_html_proteger_sortie(profilePicture($RECH['usID'],$RECH['usAvecPhoto']));
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
                        '<label>',
                            '<a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',
                            wa_html_proteger_sortie($pseudo) ,
                            '</a>',
                            '  ',
                            wa_html_proteger_sortie($nom),
                        '</label>',
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
			}else{
				$sql="SELECT count(eaDate) FROM estabonne WHERE eaIDAbonne= {$RECH['usID']} AND eaIDUser={$_SESSION['id']}";
				$estabo=wa_bd_send_request($bd, $sql);
				$EA=mysqli_fetch_assoc($estabo);
				if($EA['count(eaDate)']==0){
					echo
                    '<li><img class="imgAuteur" alt="',$nom,'" src="',$photo,'"/>',
                        '<label>',
                            '<a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',
                            wa_html_proteger_sortie($pseudo) ,
                            '</a>',
                            '  ',
                            wa_html_proteger_sortie($nom),
                        '</label>',
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
				$lienblabla='blablas.php?id='.cryptage(wa_html_proteger_sortie($TA['usID']));
				$lienabonnement='abonnements.php?id='.cryptage(wa_html_proteger_sortie($TA['usID']));
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
                        '<label>',
                            '<a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',
                            wa_html_proteger_sortie($pseudo) ,
                            '</a>',
                            '  ',
                            wa_html_proteger_sortie($nom),
                        '</label>',
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
                '<label>',
                    '<a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',
                    wa_html_proteger_sortie($pseudo) ,
                    '</a>',
                    '  ',
                    wa_html_proteger_sortie($nom),
                '</label>',
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
                '<label>',
                    '<a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',
                    wa_html_proteger_sortie($pseudo) ,
                    '</a>',
                    '  ',
                    wa_html_proteger_sortie($nom),
                '</label>',
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
                '<label>',
                    '<a title="Afficher ma bio" href="utilisateur.php?id=',cryptage(wa_html_proteger_sortie($id)),'">',
                    wa_html_proteger_sortie($pseudo) ,
                    '</a>',
                    '  ',
                    wa_html_proteger_sortie($nom),
                '</label>',
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




/**
 * Fonction qui permet de gerer les recuit de cuit.
 *
 * @param mysqli    $bd     connexion a la base
**/
function wa_rec($bd){
    //test pour verification du recuite
    if(isset($_GET['recuiter'])){
        $blid=wa_bd_proteger_entree($bd,($_GET['recuiter']));
        tooManyArg($_GET, 2);
        $sql="SELECT * FROM blablas WHERE blID=$blid";
        $send=wa_bd_send_request($bd, $sql);
        $rec=mysqli_fetch_assoc($send);
        $auteur=$rec['blIDAutOrig'];
        $text=wa_bd_proteger_entree($bd,$rec['blTexte']);
        $original=$rec['blIDAuteur'];
        $date= date('Y').date('m').date('d');
        $heure= date('H:i:s');
        if($_SESSION['id']== $original || $_SESSION['id']==$auteur){
            header('location: cuiteur.php');
        }
        $sql="INSERT INTO blablas (blIDAuteur,blDate,blHeure,blTexte,blIDAutOrig)
            VALUES ({$_SESSION['id']},$date,'$heure','$text',$original)";
        wa_bd_send_request($bd, $sql);
        $blID=mysqli_insert_id($bd);
        //recuperer toute les mentiions du blablas
        $mentions=all_mentions($text);
        foreach ($mentions as $key) {
            $key=wa_bd_proteger_entree($bd,$key);
            $sql="SELECT usID, count(usID) FROM users WHERE usPseudo = '$key'";
            $users=wa_bd_send_request($bd, $sql);
            $US=mysqli_fetch_assoc($users);
            if($US['count(usID)']!=0){
                $sql="INSERT INTO mentions (meIDUser,meIDBlabla)
                    VALUES ({$US['usID']},$blID)";
                wa_bd_send_request($bd, $sql);
            }
        }
        //recup toute les tags du blablas
        $tags=all_tags($text);
        foreach ($tags as $key) {
            $key=wa_bd_proteger_entree($bd,$key);
            $sql="INSERT INTO tags (taID,taIDBlabla)
                VALUES ('$key',$blID)";
            wa_bd_send_request($bd, $sql);
        }
    header('location: cuiteur.php');
    }
}

/**
 * Fonction qui permet de gerer les suppression de cuit.
 *
 * @param mysqli    $bd     connexion a la base
**/
function wa_supp($bd){
    //test pour savoir si le blablas que l'utilisateur veut supprimer est a bien a lui
	if(isset($_GET['delete'])){
		tooManyArg($_GET, 2);
		$blid=wa_bd_proteger_entree($bd,($_GET['delete']));
		$sql="SELECT * FROM blablas WHERE blID=$blid";
		$test=wa_bd_send_request($bd, $sql);
		$UT=mysqli_fetch_assoc($test);
		if ($_SESSION['id']!=$UT['blIDAuteur']) {
		   header('location: cuiteur.php');
           exit;
		}
		$text=wa_bd_proteger_entree($bd,$UT['blTexte']);
		//recuperer toute les mentiions du blablas
	   	$mentions=all_mentions($text);
	    foreach ($mentions as $key) {
	      $key=wa_bd_proteger_entree($bd,$key);
	      $sql="SELECT usID, count(usID) FROM users WHERE usPseudo = '$key'";
	      $users=wa_bd_send_request($bd, $sql);
	      $US=mysqli_fetch_assoc($users);
	      if($US['count(usID)']!=0){
		      $sql="DELETE FROM mentions WHERE meIDUser={$US['usID']}
		      		AND meIDBlabla=$blid";
		      wa_bd_send_request($bd, $sql);
	  		}
	  	}
	    //recup toute les tags du blablas
	    $tags=all_tags($text);
	    foreach ($tags as $key) {
	      $key=wa_bd_proteger_entree($bd,$key);
	      $sql="DELETE FROM tags WHERE taID='$key' AND taIDBlabla=$blid";
	      wa_bd_send_request($bd, $sql);
	    }
        $sql="DELETE FROM blablas WHERE blID=$blid";
		wa_bd_send_request($bd, $sql);

		header('location: cuiteur.php');
        exit();
	}
}

/**
 * Fonction qui permet de gerer les reponses a un blablas 
 * @param mysqli    $bd     connexion a la base
**/
function wa_rep($bd){
	if (isset($_GET['repondre'])) {
		$pseudo=wa_bd_proteger_entree($bd, ($_GET['repondre']));
		$sql="SELECT COUNT(usID),usPseudo FROM users WHERE usPseudo='$pseudo'";
	  	$rep=wa_bd_send_request($bd, $sql);
	  	$R=mysqli_fetch_assoc($rep);
	  	if($R['COUNT(usID)']==0){
	  		header('location: cuiteur.php');
	  	}
	  	return $R['usPseudo'];
	}
	return '';
}

/**
 * Fonction qui permet de compter et de varifier le nombre d'argument dans l'url.
 *
 * @param array    $GET   tableau de l'url
 * @param int    $nb    nombre d'argument que l'on veut avoir
**/
function tooManyArg($GET , $nb){
    if (count($GET)>$nb) {
        header('location: cuiteur.php');
    }
}

/**
* Fonction qui permet de renvoyer toute les mentions d'un blablas sous forme de tableau
* @param string   $blablas  equivalent de blText de la base de donnée
**/
function all_mentions($blablas){
    preg_match_all("/[@]([\p{L}w\.]+)/", $blablas , $matches);
    $empty_array=array();
    foreach ($matches[1] as $key) {
        if (!in_array($key,$empty_array)) {
            $empty_array[]=$key;
        }
    }
    return $empty_array;
}

/**
* Fonction qui permet de renvoyer tout les tags d'un blablas sous forme de tableau
* @param string   $blablas  equivalent de blText de la base de donnée
**/
function all_tags($blablas){
    preg_match_all("/[#]([\p{L}?w\.]+)/", $blablas , $matches);
    $empty_array=array();
    foreach ($matches[1] as $key) {
        if (!in_array($key,$empty_array)) {
            $empty_array[]=$key;
        }
    }
    return $empty_array;
}

/**
* Fonction qui permet de renvoyer un blablas avec en transformant les mentions et tags en lien
* @param mysqli 	   $bd       connexion a la base de donnée
* @param string   $blablas  equivalent de blText de la base de donnée
**/
function blablasTagAndMentionsLink($bd,$blablas){
    //gereration des liens pours le mentions
    $mentions=all_mentions($blablas);
    $res=$blablas;
    foreach ($mentions as $key) {
        $sql="SELECT usID, count(usID) FROM users WHERE usPseudo = '$key'";
        $users=wa_bd_send_request($bd, $sql);
        $US=mysqli_fetch_assoc($users);
        if($US['count(usID)']!=0){
            $replace='@<a href="utilisateur.php?id='.cryptage(wa_html_proteger_sortie($US['usID'])).'">'.wa_html_proteger_sortie($key).'</a>';
            $res=str_replace('@'.$key, $replace, $res);
        }
    }
    //gereration des liens pour les tags
    $tags=all_tags($blablas);
    foreach ($tags as $key) {
        $replace='#<a href="tendances.php?tags='.cryptage(wa_html_proteger_sortie($key)).'">'.wa_html_proteger_sortie($key).'</a>';
        $res=str_replace('#'.$key, $replace, $res);
    }
    return $res;
}
?>
