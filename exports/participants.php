<?php
// globals
// config variable.
$APP = array();

$APP["loginpage"] = "/component/users/?view=login";

include_once('../db.php');
include_once("../_includes/functions.global.php");
include_once("../_includes/joomla.php");
include_once('./current-players.php');



// if (!in_array("32", $jgroups, true) && !in_array("30", $jgroups, true)) {
// 	header('Status: 303 Moved Temporarily', false, 303);
// 	header('Location: ../');
// }

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
  <style type="text/css">
    @media screen {
      table td:nth-last-child(-n + 2) {
        display: none
      }

      table th:nth-last-child(-n + 2) {
        display: none
      }
    }

    body {
      background: #262e3e;
      color: white;
    }

    table {
      font-family: arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
      color: white;
    }

    td,
    th {
      border: 1px solid #dddddd;
      text-align: left;
      padding: 2px 4px;
      font-size: 16px;
    }

    @media screen {
      tr:nth-child(even) {
        background-color: #555555;
        color: white;
      }

      tr:nth-child(odd) {
        color: white;
      }
    }

    .button {
      background-color: #4CAF50;
      border: none;
      color: white;
      padding: 15px 32px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-size: 16px;
      margin: 4px 2px;
      cursor: pointer;
    }

    .hidden-text {
      display: none;
    }

    @media print {
      #printPageButton {
        display: none;
      }

      #CopyEmailButton {
        display: none;
      }

      #SortBy {
        display: none;
      }

      .row {
        display: flex;
        margin-left: -5px;
        margin-right: -5px;
      }

      .column {
        flex: 50%;
        padding: 5px;
      }


      * {
        box-sizing: border-box;
        -webkit-print-color-adjust: exact;
      }

      body {
        background-color: #fff;
        color: #000;
        font-size: 10px;
      }

      table {
        color: #000;
        border-collapse: collapse;
        padding: 1px 5px;
        font-size: 8px;
        width: 95%;
        margin-left: auto;
        margin-right: auto;
      }

      td,
      th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 0px 0px;
        font-size: 10px;
      }

      .single_record {
        page-break-after: always;
      }
    }
  </style>
</head>

<body>
  <?php
  $tableSort = !empty($_GET['sort']) ? $_GET['sort'] : 'oc_fn asc';
  $sql2 = "SELECT title FROM jml_eb_events where id = $EVENTID;";
  $res2 = $UPLINK->query($sql2);
  $row2 = mysqli_fetch_array($res2);
  $sql = "SELECT r.id, r.first_name as oc_fn, r.register_date, r.email as email, tussenvoegsel.field_value as oc_tv,
    r.last_name as oc_ln, faction.character_name as ic_name, soort_inschrijving.field_value as type, faction.faction as faction
    from joomla.jml_eb_registrants r
    left join joomla.jml_eb_field_values charname on (charname.registrant_id = r.id and charname.field_id = 21 )
    left join joomla.ecc_characters faction ON (faction.characterID = substring_index(charname.field_value,' - ',-1))
    left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
    left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
    where soort_inschrijving.field_value = 'Speler' AND r.event_id = $EVENTID and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
    (r.published in (0,1) AND r.payment_method = 'os_offline'))
    UNION
    select r.id, r.first_name as oc_fn, r.register_date, r.email as email, tussenvoegsel.field_value as oc_tv,
    r.last_name as oc_ln, NULL as ic_name, soort_inschrijving.field_value as type, NULL as faction
    from joomla.jml_eb_registrants r
    left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
    left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
    WHERE soort_inschrijving.field_value != 'Speler' AND r.event_id = $EVENTID and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
    (r.published in (0,1) AND r.payment_method = 'os_offline')) ORDER BY $tableSort";
  $res = $UPLINK->query($sql);
  $row_count = mysqli_num_rows($res);

  $sql3 = "SELECT COUNT(r.id) as count, soort_inschrijving.field_value as type
  from joomla.jml_eb_registrants r       
  left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
  left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
  where  soort_inschrijving.field_value IS NOT NULL AND r.event_id = $EVENTID  and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
  (r.published in (0,1) AND r.payment_method = 'os_offline'))
  GROUP BY soort_inschrijving.field_value";
  $res3 = $UPLINK->query($sql3);

  $sql5 = "SELECT faction.faction as faction, COUNT(*) as count
from joomla.jml_eb_registrants r
join joomla.jml_eb_field_values charname on (charname.registrant_id = r.id and charname.field_id = 21 )
join joomla.ecc_characters faction ON (faction.characterID = substring_index(charname.field_value,' - ',-1))
left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
where soort_inschrijving.field_value = 'Speler' AND r.event_id = $EVENTID and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
(r.published in (0,1) AND r.payment_method = 'os_offline')) GROUP by faction";
  $res5 = $UPLINK->query($sql5);


  echo "<button class=\"button\" id=\"printPageButton\" style=\"width: 100px;\" onClick=\"window.print();\">Print</button>";
  echo '<font size="5">Participants for ' . $row2['title'] . ' - '
    . "($row_count participants)</font>";

  echo '<div class="row">';
  echo '<div class="column">';
  echo '<table style="width:30%">';
  echo '<th width="10%">Soort Inschrijf</th>';
  echo '<th width="10%">Aantal Deelneemers</th>';
  echo '<th width="80%">&nbsp;</th><th>&nbsp;</th>';

  echo "</tr>";
  while ($row2 = mysqli_fetch_array($res3)) {
    echo '<td>' . $row2['type'] . "</td>";
    echo '<td>' . $row2['count'] . "</td>";
    echo "<td>&nbsp;</td><td>&nbsp;</td>";
    echo "</tr>";
  }
  if (in_array("32", $jgroups, true)) {
    $sql_pending = 'SELECT SUM(payment_amount) as amount FROM jml_eb_registrants WHERE payment_method="os_offline" AND published=0 AND event_id = ' . $EVENTID;
    $res_pending = $UPLINK->query($sql_pending);
    $pending = mysqli_fetch_array($res_pending);
    echo '<td>Pending Payments</td> <td>€' . round($pending['amount'],2) . '</td>';
    echo "<td>&nbsp;</td><td>&nbsp;</td> </tr>";
  }
  echo "</table>";
  echo "<br><br>";
  echo "</div>"; #div column
  
  echo '<div class="column">';
  echo '<table style="width:30%">';
  echo '<th width="10%">Faction</th>';
  echo '<th width="10%">Aantal Deelneemers</th>';
  echo '<th width="80%">&nbsp;</th><th>&nbsp;</th>';
  echo "</tr>";
  while ($row3 = mysqli_fetch_array($res5)) {
    echo '<td>' . ucwords($row3['faction']) . "</td>";
    echo '<td>' . $row3['count'] . "</td>";
    echo "<td>&nbsp;</td><td>&nbsp;</td>";
    echo "</tr>";
  }
  echo "</table>";
  echo "</div>"; #div column
  echo "</div>"; #div row
  ?>
  <?php $email = !empty($_GET['email']) ? $_GET['email'] : '%%'; ?>
  <div id="CopyEmailButton">
    Type of E-mail Addresses to Copy:
    <select id="email_types" style="padding: 5px; border-radius: 2px; margin-bottom: 1rem;" onchange="location.href = '/eoschargen/exports/participants.php?<?php if (isset($_GET["sort"])) {
      echo 'sort=' . $_GET["sort"] . '&';
    } ?>email=' + this.value; ">
      <option value="%%" <?php echo $email == '%%' ? 'selected' : ''; ?>>Alles</option>
      <option value="Speler" <?php echo $email == 'Speler' ? 'selected' : ''; ?>>Speler</option>
      <option value="Figurant" <?php echo $email == 'Figurant' ? 'selected' : ''; ?>>Figurant</option>
      <option value="Spelleider" <?php echo $email == 'Spelleider' ? 'selected' : ''; ?>>Spelleider</option>
      <option value="Keuken Crew" <?php echo $email == 'Keuken Crew' ? 'selected' : ''; ?>>Keuken Crew</option>
    </select>
    <?php
    $sql4 = "SELECT r.email as email from joomla.jml_eb_registrants r
    left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
    where soort_inschrijving.field_value LIKE '$email' AND r.event_id = $EVENTID and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
    (r.published in (0,1) AND r.payment_method = 'os_offline'))";
    $res4 = $UPLINK->query($sql4);
    $emails = '';
    while ($row4 = mysqli_fetch_array($res4)) {
      $emails = $emails . $row4['email'] . ';';
    }
    echo "<input type=\"text\" class=\"hidden-text\" value=\"$emails\" id=\"myInput\">";
    ?>
    <button class="button" onclick="copyTo()">Copy Participant E-mails</button><br><br>
  </div>
  <div id="SortBy">Sorteer op:
    <select id="sort_table" style="padding: 5px; border-radius: 2px; margin-bottom: 1rem;" onchange="location.href = '/eoschargen/exports/participants.php?<?php if (isset($_GET["email"])) {
      echo 'email=' . $_GET["email"] . '&';
    } ?>sort=' + this.value; ">
      <option value="oc_fn asc" <?php echo $tableSort == 'oc_fn asc' ? 'selected' : ''; ?>> OC Naam (Oplopend)</option>
      <option value="oc_fn desc" <?php echo $tableSort == 'oc_fn desc' ? 'selected' : ''; ?>>OC Naam (Aflopend)</option>
      <option value="ic_name asc" <?php echo $tableSort == 'ic_name asc"' ? 'selected' : ''; ?>>IC Naam (Oplopend)
      </option>
      <option value="ic_name desc" <?php echo $tableSort == 'ic_name desc' ? 'selected' : ''; ?>>IC Naam (Aflopend)
      </option>
      <option value="type asc" <?php echo $tableSort == 'type asc' ? 'selected' : ''; ?>>Soort Inschrijf (Oplopend)
      </option>
      <option value="type desc" <?php echo $tableSort == 'type desc' ? 'selected' : ''; ?>>Soort Inschrijf (Aflopend)
      </option>
      <option value="register_date asc" <?php echo $tableSort == 'register_date asc' ? 'selected' : ''; ?>>Inschrijf Datum
        (Oplopend)</option>
      <option value="register_date desc" <?php echo $tableSort == 'register_date desc' ? 'selected' : ''; ?>>Inschrijf
        Datum (Aflopend)</option>
    </select>
  </div>
  <?php
  echo "<table>";
  echo '<th width="20%">OC Name</th>';
  echo '<th width="20%">IC Name</th>';
  echo '<th width="15%">Soort Inschrijf</th>';
  echo '<th width="15%">Factie</th>';
  echo '<th width="10%">Inschrif Datum</th>';
  echo '<th width="25%">Handtekening</th>';
  echo '<th width="10%">Foto Opt-Out</th>';
  echo "</tr>";

  while ($row = mysqli_fetch_array($res)) {
    echo "<tr>" . "<td>";
    echo $row['oc_fn'] . " " . $row['oc_tv'] . " " . $row['oc_ln'];
    echo "</td>";
    echo '<td>' . $row['ic_name'] . "</td>";
    echo '<td>' . $row['type'] . "</td>";
    echo '<td>' . ucwords($row['faction']) . "</td>";
    echo '<td>' . $row['register_date'] . "</td>";
    echo '<td height="40px">&nbsp;</td>';
    echo '<td height="40px">&nbsp;</td>';
    echo "</tr>";
  }
  echo "</table>";
  ?>
</body>

</html>
