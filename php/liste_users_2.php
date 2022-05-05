<?php
/** 2ème version : liste des utilisateurs */

ob_start(); //démarre la bufferisation

require_once 'bibli_generale.php';
require_once 'bibli_cuiteur.php';

$bd = wa_bd_connect();

$sql = 'SELECT *
        FROM users
        ORDER BY usID';

$res = wa_bd_send_request($bd, $sql);

wa_aff_debut('Cuiteur | Utilisateurs');

echo '<h1>', 'Liste des utilisateurs de Cuiteur', '</h1>';

// Récupération des données et encapsulation dans du code HTML envoyé au navigateur 
while ($t = mysqli_fetch_assoc($res)) {
    echo '<h2> Utilisateur ', $t['usID'], '</h2>',
        '<ul>',
            '<li>Pseudo : ', wa_html_proteger_sortie($t['usPseudo']), '</li>',
            '<li>Nom : ', wa_html_proteger_sortie($t['usNom']), '</li>',
            '<li>Inscription : ', wa_amj_clair($t['usDateInscription']), '</li>',         // pas nécessaire de protéger les entiers
            '<li>Ville : ', wa_html_proteger_sortie($t['usVille']), '</li>',
            '<li>Web : ', wa_html_proteger_sortie($t['usWeb']), '</li>',
            '<li>Mail : ', wa_html_proteger_sortie($t['usMail']), '</li>',
            '<li>Naissance : ', wa_amj_clair($t['usDateNaissance']), '</li>',
            '<li>Bio : ', wa_html_proteger_sortie($t['usBio']), '</li>',
        '</ul>';
}

// libération des ressources
mysqli_free_result($res);
mysqli_close($bd);

wa_aff_fin();

// facultatif car fait automatiquement par PHP
ob_end_flush();



?>
