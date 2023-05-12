<?php
// globals
// config variable.
$APP = array();

$APP["loginpage"] = "/component/users/?view=login";

include_once('../db.php');
include_once("../_includes/functions.global.php");

include_once('./current-players.php');

?>
<!DOCTYPE html>
<html>

<head>
  <style type="text/css">
    @media screen {
      table td:nth-last-child(-n + 2) {display:none}
      table th:nth-last-child(-n + 2) {display:none}
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
    
    td, th {
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
      #SortBy{
        display: none;
      }
      #SummaryTable {
        display: none;
      }

      * {
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
  if ( isset($_GET["sort"]) ) {
    $tableSort = urldecode($_GET["sort"]);
  }
  else {
    $tableSort = "oc_fn asc";
  }
  $sql2 = "SELECT title FROM jml_eb_events where id = $EVENTID;";
  $res2 = $UPLINK->query($sql2);
  $row2 = mysqli_fetch_array($res2);
  $sql = "select r.id, r.first_name as oc_fn, r.register_date, r.email as email, tussenvoegsel.field_value as oc_tv,
  r.last_name as oc_ln, substring_index(charname.field_value,' - ',1) as ic_name, soort_inschrijving.field_value as type
from joomla.jml_eb_registrants r
join joomla.jml_eb_field_values charname on (charname.registrant_id = r.id and charname.field_id = 21 )
left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
where soort_inschrijving.field_value = 'Speler' AND r.event_id = $EVENTID and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
(r.published in (0,1) AND r.payment_method = 'os_offline'))
UNION
select r.id, r.first_name as oc_fn, r.register_date, r.email as email, tussenvoegsel.field_value as oc_tv,
r.last_name as oc_ln, NULL as ic_name, soort_inschrijving.field_value as type
from joomla.jml_eb_registrants r
left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
WHERE soort_inschrijving.field_value != 'Speler' AND r.event_id = $EVENTID and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
(r.published in (0,1) AND r.payment_method = 'os_offline')) ORDER BY $tableSort";
  $res = $UPLINK->query($sql);
  $row_count = mysqli_num_rows($res);


  $sql4 = "select r.email as email from joomla.jml_eb_registrants r
where r.event_id = $EVENTID and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
(r.published in (0,1) AND r.payment_method = 'os_offline'))";
  $res4 = $UPLINK->query($sql4);

  $sql3 = "select COUNT(r.id) as count, soort_inschrijving.field_value as type
  from joomla.jml_eb_registrants r       
  left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
  left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
  where  soort_inschrijving.field_value IS NOT NULL AND r.event_id = $EVENTID  and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
  (r.published in (0,1) AND r.payment_method = 'os_offline'))
  GROUP BY soort_inschrijving.field_value";
  $res3 = $UPLINK->query($sql3);
  echo "<button class=\"button\" id=\"printPageButton\" style=\"width: 100px;\" onClick=\"window.print();\">Print</button>";
  echo '<font size="5">Participants for ' . $row2['title'] . ' - '
    . "($row_count participants)</font>";
  
    echo '<div id="SummaryTable"><table style="width:30%">';
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
    echo "</table></div>";
echo "<br><br>";
# ic_name, oc_fn
// echo "<button class='button' id='printPageButton' style='width: 300px;' onClick='copyText(\"$emails\")'>Copy Participant E-mails</button><br>";
// echo "<input type=\"text\" value=\"$emails\" id=\"myInput\">";
?>
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
<?php 
$emails = '';
while ($row4 = mysqli_fetch_array($res4)) {
  $emails = $emails . $row4['email'].';';
}
echo "<input type=\"text\" class=\"hidden-text\" value=\"$emails\" id=\"myInput\">";
?>
<button class="button" id="CopyEmailButton" onclick="copyTo()">Copy Participant E-mails</button><br><br>
<div id="SortBy">Sorteer op:
  <select id="sort_table" style="padding: 5px; border-radius: 2px; margin-bottom: 1rem;"
  onchange="location.href = '/eoschargen/exports/participants.php?sort=' + this.value; ">
    <option value="">Kies een optie</option>
    <option value="oc_fn asc"> OC Naam (Oplopend)</option>
    <option value="oc_fn desc">OC Naam (Aflopend)</option>
    <option value="ic_name asc">IC Naam (Oplopend)</option>
    <option value="ic_name desc">IC Naam (Aflopend)</option>
    <option value="type asc">Soort Inschrijf (Oplopend)</option>
    <option value="type desc">Soort Inschrijf (Aflopend)</option>
    <option value="register_date asc">Inschrijf Datum (Oplopend)</option>
    <option value="register_date desc">Inschrijf Datum (Aflopend)</option>
  </select>
</div>
<?php
  echo "<table>";
    echo '<th width="20%">OC Name</th>';
    echo '<th width="20%">IC Name</th>';
    echo '<th width="15%">Soort Inschrijf</th>';
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
    echo '<td>' . $row['register_date']. "</td>";
    echo '<td height="40px">&nbsp;</td>'; 
    echo '<td height="40px">&nbsp;</td>'; 
    echo "</tr>";
  }
  echo "</table>";
  ?>
</body>

</html>
