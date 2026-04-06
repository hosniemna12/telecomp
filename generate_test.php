<?php

function makeDetail($rang, $montant, $numVirement, $ribDonneur, $nomDonneur, $codeInstDest, $codeCentreDest, $ribBenef, $nomBenef, $motif) {
    $line = '';
    $line .= '1';                                              // sens (1)         = 1
    $line .= '10';                                             // code valeur (2)  = 3
    $line .= '1';                                              // nature rem. (1)  = 4
    $line .= '11';                                             // code rem. (2)    = 6
    $line .= '026';                                            // code centre (3)  = 9
    $line .= '20260105';                                       // date op. (8)     = 17
    $line .= '3360';                                           // num lot (4)      = 21
    $line .= '21';                                             // code enr. (2)    = 23
    $line .= 'TND';                                            // devise (3)       = 26
    $line .= str_pad($rang, 2, '0', STR_PAD_LEFT);             // rang (2)         = 28
    $line .= str_pad($montant, 15, '0', STR_PAD_LEFT);         // montant (15)     = 43
    $line .= str_pad($numVirement, 7, '0', STR_PAD_LEFT);      // num virement (7) = 50
    $line .= str_pad($ribDonneur, 20);                         // RIB donneur (20) = 70
    $line .= str_pad($nomDonneur, 30);                         // nom donneur (30) = 100
    $line .= str_pad($codeInstDest, 2, '0', STR_PAD_LEFT);     // inst dest (2)    = 102
    $line .= str_pad($codeCentreDest, 3, '0', STR_PAD_LEFT);   // centre dest (3)  = 105
    $line .= str_pad($ribBenef, 20);                           // RIB benef (20)   = 125
    $line .= str_pad($nomBenef, 30);                           // nom benef (30)   = 155
    $line .= str_pad('', 20);                                  // ref dossier (20) = 175
    $line .= str_pad($motif, 45);                              // motif (45)       = 220
    $line .= '0';                                              // situation (1)    = 221
    $line .= '1';                                              // type compte (1)  = 222
    $line .= '0';                                              // nature cpt (1)   = 223
    $line .= '0';                                              // exist dossier(1) = 224
    $line .= str_pad('', 37);                                  // zone libre (37)  = 261
    // Total jusqu'ici = 261 → il manque 19 caractères pour 280
    // D'après doc SIBTEL champs 23-29 (image 2) :
    $line .= str_pad('', 8);                                   // date comp init (8) = 269
    $line .= str_pad('', 8);                                   // motif rejet (8)    = 277
    $line .= str_pad('', 3);                                   // complément (3)     = 280

    $longueur = strlen($line);
    echo "Detail rang={$rang} longueur={$longueur}";
    if ($longueur !== 280) {
        echo " ← ERREUR manque " . (280 - $longueur) . " car.";
    } else {
        echo " ✓";
    }
    echo "\n";

    return str_pad($line, 280);
}

// GLOBAL (rang=00)
$global = '';
$global .= '1';                                                // sens (1)
$global .= '10';                                               // code valeur (2)
$global .= '1';                                                // nature remettant (1)
$global .= '11';                                               // code remettant (2)
$global .= '026';                                              // code centre (3)
$global .= '20260105';                                         // date opération (8)
$global .= '3360';                                             // numéro lot (4)
$global .= '21';                                               // code enregistrement (2)
$global .= 'TND';                                              // code devise (3)
$global .= '00';                                               // rang = 00 (2)
$global .= str_pad('528000000', 15, '0', STR_PAD_LEFT);        // montant total (15)
$global .= str_pad('3', 10, '0', STR_PAD_LEFT);                // nombre total (10)
$global .= str_repeat(' ', 227);                               // zone libre (227)

$longueur = strlen($global);
echo "Global longueur={$longueur}";
echo ($longueur === 280) ? " ✓\n" : " ← ERREUR\n";

// DETAILS
$detail1 = makeDetail('1', '52800000',  '1',
    '05009000028314317192', 'STE NJAH FRERE',
    '05', '005',
    '11005331000000000000', 'AIR LIQUIDE',
    'AUTRE'
);

$detail2 = makeDetail('2', '27000000', '2',
    '08921511778800000000', 'STE KACHOUD',
    '03', '037',
    '07004011500452036000', 'STE L UNIVERS GOURMAND',
    'REG FACTURE'
);

$detail3 = makeDetail('3', '31187240', '3',
    '08903718979000000000', 'STE ASPIS PHARMA',
    '08', '037',
    '01209200242413300000', 'MOHAMED PHARMA GARBI',
    'SALAIRE'
);

// Créer le fichier
$chemin = __DIR__ . '/storage/app/test/26-999-10-21-test.ENV';
@mkdir(dirname($chemin), 0755, true);
$contenu = $global . "\n" . $detail1 . "\n" . $detail2 . "\n" . $detail3 . "\n";
file_put_contents($chemin, $contenu);

echo "Fichier créé : {$chemin}\n";
echo "Taille totale : " . strlen($contenu) . " octets\n";
echo "Attendu : " . (280 * 4 + 4) . " octets (4 lignes × 280 + 4 retours ligne)\n";