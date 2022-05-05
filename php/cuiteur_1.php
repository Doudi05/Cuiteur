<?php

ob_start(); //démarre la bufferisation

require_once 'bibli_generale.php';
require_once 'bibli_cuiteur.php';

$bd = wa_bd_connect();

$sql = 'SELECT  DISTINCT auteur.usID AS autID, auteur.usPseudo AS autPseudo, auteur.usNom AS autNom, auteur.usAvecPhoto AS autPhoto, 
                blTexte, blDate, blHeure,
                origin.usID AS oriID, origin.usPseudo AS oriPseudo, origin.usNom AS oriNom, origin.usAvecPhoto AS oriPhoto
        FROM (((users AS auteur
        INNER JOIN blablas ON blIDAuteur = usID)
        LEFT OUTER JOIN users AS origin ON origin.usID = blIDAutOrig)
        LEFT OUTER JOIN estabonne ON auteur.usID = eaIDAbonne)
        LEFT OUTER JOIN mentions ON blID = meIDBlabla
        WHERE   auteur.usID = 23
        OR      eaIDUser = 23
        OR      meIDUser = 23
        ORDER BY blID DESC';

$res = wa_bd_send_request($bd, $sql);

wa_aff_debut('Cuiteur', '../styles/cuiteur.css');
wa_aff_entete();
wa_aff_infos();
echo '<ul>';

if (mysqli_num_rows($res) == 0){
    echo '<li>Votre fil de blablas est vide</li>';
}
else{
    wa_aff_blablas($res);
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
