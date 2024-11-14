<?php
include_once('current-players.php');
include_once($_SERVER["DOCUMENT_ROOT"] . '/eoschargen/db.php');

$notCancelled = "((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR (r.published in (0,1) AND r.payment_method = 'os_offline'))";
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <!-- <meta charset="utf-8"> -->
  <title></title>

  <!-- 336 width  192 height -->

  <style media="screen">
    body {
      font-family: arial;
      color: white;
    }

    p {
      margin-top: 2px;
    }
  </style>
   <link rel="stylesheet" href="css/participants.css">

</head>

<body>
  <div class="" style="max-width: 700px;">

    <?php

    include_once('../db.php');

    $sql = "SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1) as characterID, c1.character_name, c1.faction, c1.rank
				from jml_eb_registrants r
				join jml_eb_field_values v1 on (v1.registrant_id = r.id and v1.field_id = 21)
				join ecc_characters c1 on c1.characterID = SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1)
				where r.event_id = $EVENTID and $notCancelled AND (`rank` LIKE '%conc%' OR `rank` like '%Governor%') ORDER by character_name;";
    $res = $UPLINK->query($sql);


    if ($res) {
      if (mysqli_num_rows($res) > 0) {

        $count = 0;
        echo "<h1>The Conclave</h1>";
        echo "<table>";
        while ($row = mysqli_fetch_assoc($res)) {

          if ($count == 0) {
            echo "<div style=\"float: left; margin: 5px; border: 1px solid #222; width: 336px; height: 192px;\">";
            $count = 1;
          }
          
          echo "<div style=\"padding: 5px; float: left; height: 86px; width: 100%;\">"
            . '<a href="../img/passphoto/' . $row['characterID'] . '.jpg"><img src="../img/passphoto/' . $row['characterID'] . '.jpg" style="height: 80px; width: 80px; float: left; border-radius: 100%;" /></a>'

            . "<p style=\"position: relative; padding-left: 5px;\">"
            . "<strong>" . ucfirst($row['character_name']) . "</strong>"
            . "<br/>" . ucfirst($row['rank'])
            . "<br/>" . ucfirst($row['faction'])
            . "</p>"

            . "</div>";

          if ($count == 2) {
            echo "</div>";
            $count = 0;
          } else if ($count == 1) {
            $count = 2;
          }
        }
        echo "</table>";
        // echo "</div>";
      }
    }

    $sql = "SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1) as characterID, c1.character_name, c1.faction, c1.rank
				from jml_eb_registrants r
				left join jml_eb_field_values v1 on (v1.registrant_id = r.id and v1.field_id = 21)
				left join ecc_characters c1 on c1.characterID = SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1)
				join joomla.jml_eb_field_values soort on (soort.registrant_id = r.id and soort.field_id = 14) /* Soort inschrijving */
				where soort.field_value = 'Speler' AND r.event_id = $EVENTID and $notCancelled 
        AND (c1.rank IS NULL OR c1.rank = '' OR (c1.rank NOT LIKE 'Conclave%' AND c1.rank NOT LIKE 'Governor%'));";
    $res = $UPLINK->query($sql);

    if ($res) {
      if (mysqli_num_rows($res) > 0) {

        $count = 0;
        echo "<h1>The Rest of the Colony</h1>";
        echo "<table>";
        while ($row = mysqli_fetch_assoc($res)) {

          if ($count == 0) {
            echo "<div style=\"float: left; margin: 5px; border: 1px solid #222; width: 336px; height: 192px;\">";
            $count = 1;
          }

          echo "<div style=\"padding: 5px; float: left; height: 86px; width: 100%;\">"
            . '<a href="../img/passphoto/' . $row['characterID'] . '.jpg"><img src="../img/passphoto/' . $row['characterID'] . '.jpg" style="height: 80px; width: 80px; float: left; border-radius: 100%;" /></a>'
            . "<p style=\"position: relative; padding-left: 5px;\">"
            . "<strong>" . ucfirst($row['character_name']) . "</strong>"
            . "<br/>" . ucfirst($row['rank'])
            . "<br/>" . ucfirst($row['faction'])
            . "</p>"

            . "</div>";

          if ($count == 2) {
            echo "</div>";
            $count = 0;
          } else if ($count == 1) {
            $count = 2;
          }
        }
        echo "</table>";

        // echo "</div>";
      }
    }

    ?>
  </div>
</body>

</html>