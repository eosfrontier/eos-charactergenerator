<?php
$row = explode(',',str_replace('"','','"Allergie: alcohol","Allergie: Garnalen","Allergie: lactose","Allergie: schelpdier","Allergie: vis",'));
$allergies = array();
foreach ($row as $item){
    $item2 = explode(": ", $item);
    array_push($allergies, $item2[1]);
    // $item[1] => $dietsandallergies[$item[0]];
}

echo $allergies[*];