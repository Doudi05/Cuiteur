<?php

ob_start(); //démarre la bufferisation

require_once 'bibli_generale.php';
require_once 'bibli_cuiteur.php';

$bd = wa_bd_connect();

$sql = 'SELECT usID, usPseudo, usNom, usAvecPhoto, blTexte, blDate, blHeure
        FROM users
        INNER JOIN blablas ON blIDAuteur = usID
        WHERE usID = 2
        ORDER BY blID DESC';

$res = wa_bd_send_request($bd, $sql);

wa_aff_debut('Cuiteur | Blablas', '../styles/cuiteur.css');

// Récupération des données et encapsulation dans du code HTML envoyé au navigateur
$i = 0;
while ($t = mysqli_fetch_assoc($res)) {
    if ($i == 0){
        wa_aff_entete(wa_html_proteger_sortie("Les blablas de {$t['usPseudo']}"));
        wa_aff_infos();
        echo '<ul>';
    }
    echo    '<li>', 
                '<img src="../', ($t['usAvecPhoto'] == 1 ? "upload/{$t['usID']}.jpg" : 'images/anonyme.jpg'), 
                '" class="imgAuteur" alt="photo de l\'auteur">',
                wa_html_proteger_sortie($t['usPseudo']), ' ', wa_html_proteger_sortie($t['usNom']), '<br>',
                wa_html_proteger_sortie($t['blTexte']),
                '<p class="finMessage">',
                wa_amj_clair($t['blDate']), ' à ', wa_heure_clair($t['blHeure']),
                '<a href="../index.html">Répondre</a> <a href="../index.html">Recuiter</a></p>',
            '</li>';
    ++$i;
}

echo '</ul>';

// libération des ressources
mysqli_free_result($res);
mysqli_close($bd);

wa_aff_pied();
wa_aff_fin();

// facultatif car fait automatiquement par PHP
ob_end_flush();



?>
