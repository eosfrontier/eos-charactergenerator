<?php
// config variable.
include_once($_SERVER["DOCUMENT_ROOT"] . '/eoschargen/db.php');
?>
<!DOCTYPE html>
<html>

<head>
  <script>
    function copyTo() {
      // Get the text field
      var copyText = document.getElementById("myInput");

      // Select the text field
      copyText.select();
      copyText.setSelectionRange(0, 99999); // For mobile devices

      // Copy the text inside the text field
      navigator.clipboard.writeText(copyText.value);
    }
  </script>
  <style>
    .hidden-text {
      display: none;
    }
    body {
      background: #000;
      color: #fff;
      font-family: 'Orbitron', sans-serif;
      /*padding-top:20px;*/
    }

    table {
      font-family: arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }

    a {
      color: lightblue;
    }

    @page {
      size: auto;
      margin: 0;
    }

    @media print {
      #printPageButton {
        display: none;
      }

      * {
        -webkit-print-color-adjust: exact;
      }

      body {
        background: #fff;
        color: #000;
      }

      table {
        font-family: orbitron;
        border-collapse: collapse;
        font-size: 18px;
        width: 70%;
        margin-left: auto;
        margin-right: auto;
      }
    }

    thead {
      color: #41bee8;
    }

    tr:nth-child(even) {
      background-color: #262e3e;
      /* color:#000; */
    }
  </style>
</head>
<?php

$sql = "SELECT title FROM jml_eb_events where id = $EVENTID;";
$res = $UPLINK->query($sql);
$row = mysqli_fetch_array($res);

echo '<h2>New Card Needed for ' . $row['title'] . '</h2>';
?>

<body>
  <?php

  $sqlpart1 = "SELECT c.character_name, r.email as email, c.faction ,c.ICC_number,  c.card_id, c.characterID
              from jml_eb_registrants r
              join jml_eb_field_values v1 on (v1.registrant_id = r.id and v1.field_id = 21)
              join jml_eb_field_values v2 on (v2.registrant_id = r.id and v2.field_id = 14)
              join ecc_characters c ON (c.characterID = SUBSTRING_INDEX(v1.field_value,' - ',-1))
              join jml_users u ON (c.accountID = u.id) 
              WHERE c.card_id IS NULL AND r.event_id = $EVENTID AND v2.field_value = 'Speler' AND 
              ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
              (r.published in (0,1) AND r.payment_method = 'os_offline'))";
  if (isset($NPCCards))
    $sqlpart2 = " UNION SELECT character_name, NULL as email, faction, ICC_number, card_id, characterID from ecc_characters WHERE (characterID in ($NPCCards) AND card_id is NULL)";
  else
    $sqlpart2 = ' ';
  $sqlpart3 = " ORDER BY faction, character_name";

  $sql = $sqlpart1 . $sqlpart2 . $sqlpart3;
  $res = $UPLINK->query($sql);
  echo "<table>";
  echo "<th>E-Mail</th>";
  echo "<th>Faction</th>";
  echo "<th>Name</th>";
  echo "<th>ICC Number</th>";
  echo "<th>Image Name</th>";
  echo "</tr>";
  $emails = '';
  while ($row = mysqli_fetch_array($res)) {
    $filepath = '../img/passphoto/' . $row['characterID'] . '.jpg';
    if (file_exists($filepath)) {} else {$emails = $emails . $row['email'] . ';'; }
    echo "<tr>";
    echo "<td><center>" . $row['email'] . "</center></td>";
    echo "<td><center>" . $row['faction'] . "</center></td>";
    if ($row['characterID'] == 402) {
      echo '<td><center>Invalid character specified by player.</center></td>';
      echo "<td><center>xxxx xxxxx xxxx</center></td>";
      echo "<td><center>n/a</center></td>";
    } else {
      echo '<td><center> <a href="/admin_sl/character-edit.php?id=' . $row['characterID'] . '">' . $row['character_name'] . "</a></center></td>";
      echo "<td><center>" . $row['ICC_number'] . "</center></td>";
      if (file_exists($filepath)) {
      echo '<td><center><img src="' . $filepath . '" alt="Character photo" width="42"><a href="../img/passphoto/' . $row['characterID'] . '.jpg " target="_blank" download">' . $row['characterID'] . '.jpg</a></center></td>';}
      else {
        echo '<td><center><i>Not yet uploaded...</i></center></td>';
      }
    }
    echo "</tr></center>";
  }
  echo "</table>";
  echo "<input type=\"text\" class=\"hidden-text\" value=\"$emails\" id=\"myInput\">";
  echo '<button class="button" onclick="copyTo()">Copy Participant E-mails with Mising Photos</button><br><br>';
  ?>
</body>

</html>