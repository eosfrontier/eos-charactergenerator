<?php
// globals
// config variable.
$APP = array();

$APP["loginpage"] = "/component/users/?view=login";

include_once ('../db.php');
include_once ("../_includes/functions.global.php");
include_once ("../_includes/joomla.php");
include_once ('./current-players.php');



if (!in_array("32", $jgroups, true) && !in_array("30", $jgroups, true)) {
  header('Status: 303 Moved Temporarily', false, 303);
  header('Location: ../');
}

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
    /* @media screen {
      table td:nth-last-child(-n + 2) {
        display: none
      }

      table th:nth-last-child(-n + 2) {
        display: none
      }
    } */

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

    .backButton {
      background-color: rgb(247 54 9);
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
  $participant_id = $_GET['participant_id'];
  $sql2 = "SELECT title FROM jml_eb_events where id = $EVENTID;";
  $res2 = $UPLINK->query($sql2);
  $row2 = mysqli_fetch_array($res2);
  $sql = "SELECT r.id, r.first_name as oc_fn, r.register_date, r.email as email, tussenvoegsel.field_value as oc_tv, r.phone as phone,
  r.address, r.address2, r.city, r.state, r.zip, r.country,  r.last_name as oc_ln, faction.character_name as ic_name, faction.faction as faction,
   soort_inschrijving.field_value as type, noodgevallen_naam.field_value as noodgevallen_naam, 
  noodgevallen_telefoonnummer.field_value as noodgevallen_telefoonnummer, med_info.field_value as med_info, allergies.field_value as allergies, other.field_value as other
  from joomla.jml_eb_registrants r
  left join joomla.jml_eb_field_values charname on (charname.registrant_id = r.id and charname.field_id = 21 )
  left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
  left join joomla.ecc_characters faction ON (faction.characterID = substring_index(charname.field_value,' - ',-1))
  left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
  left join joomla.jml_eb_field_values noodgevallen_naam on (noodgevallen_naam.registrant_id = r.id and noodgevallen_naam.field_id = 18)
  left join joomla.jml_eb_field_values noodgevallen_telefoonnummer on (noodgevallen_telefoonnummer.registrant_id = r.id and noodgevallen_telefoonnummer.field_id = 19)
  left join joomla.jml_eb_field_values med_info on (med_info.registrant_id = r.id and med_info.field_id = 17)
  left join joomla.jml_eb_field_values allergies on (allergies.registrant_id = r.id and allergies.field_id = 56)
  left join joomla.jml_eb_field_values other on (other.registrant_id = r.id and other.field_id = 57)
  where r.id = $participant_id";
  $res = $UPLINK->query($sql);
  $row = mysqli_fetch_array($res);
  $row_count = mysqli_num_rows($res);
  $item = rtrim(
    str_replace(
      "Allium(ui,prei,knoflook,bieslook,etc)",
      "Allium(ui;prei;knoflook;bieslook;etc)",
      str_replace(
        "\\/",
        "/",
        str_replace(
          "\"",
          "",
          str_replace(
            "\",\"",
            ",  ",
            str_replace(
              "\\u00eb",
              "e",
              str_replace("\\u00cb", "E", $row['allergies'])
            )
          )
        )
      )
    ),
    ","
  );
  $row_things = explode(",", $item);
  $event_title = $row2['title'];
  echo "<button class=\"backButton\" id=\"backButton\" style=\"width: 100px;\" onClick=\"window.history.go(-1); return false;\">Back</button>";

  echo "<button class=\"button\" id=\"printPageButton\" style=\"width: 100px;\" onClick=\"window.print();\">Print</button><br>";
  echo '<font size="5">Participant details for ' . $event_title . " </font>";


  echo '<hr><h2>OC Informatie</h2>';
  ?>
  <table>
    <tr>
      <td width="20%"><strong>OC Name:</strong> </td>
      <td><?php echo $row['oc_fn'] . " " . $row['oc_tv'] . " " . $row['oc_ln']; ?></td>
    </tr>
    <tr>
      <td width="20%"><strong>Soort inschrijf:</strong> </td>
      <td><?php echo $row['type']; ?></td>
    </tr>
    <tr>
      <td width="20%"><strong>Telefoonnummer:</strong> </td>
      <td><?php echo $row['phone']; ?></td>
    </tr>
    <tr>
      <td width="20%"><strong>adres:</strong> </td>
      <td><?php 
        if (!in_array("30", $jgroups, true)) { echo "<font color='red'>You must be bestuur to retrieve this info.<br> If you need this info, please ask a member of the board to access this record for you.</font>";}
        else {
        echo $row['address'] . "<br>"; 
        if ($row['address2'] != ''){
          echo $row ['address2'] . "<br>";
        }
        echo $row['zip'] . " " . $row['city'] . "<br>";
        echo $row['country'];}
      ?></td>
    </tr>
  </table>
  <?php
  if ($row['type'] == 'Speler') {
    echo '<hr><h2>IC Informatie</h2>';
    ?>
    <table>
      <tr>
        <td width="20%"><strong>IC Name:</strong> </td>
        <td><?php echo $row['ic_name']; ?></td>
      </tr>
      <tr>
        <td width="20%"><strong>Factie:</strong> </td>
        <td><?php echo $row['faction']; ?></td>
      </tr>
    </table>
    <?php
  }
  echo '<hr><h2>Nood Informatie</h2>';

  ?>
  <table>
    <tr>
      <td width="20%"><strong>Te contacteren bij noodgevallen (naam):</strong> </td>
      <td><?php echo $row['noodgevallen_naam']; ?></td>
    </tr>
    <tr>
      <td width="20%"><strong><strong>Noodgevalen Telefoonnummer:</strong> </td>
      <td><?php echo $row['noodgevallen_telefoonnummer']; ?></td>
    </tr>
    <tr>
      <td width="20%"><strong>Medische aandoeningen indien relevant:</strong> </td>
      <td><?php echo $row['med_info']; ?></td>
    </tr>
    <tr>
      <td width="20%"><strong>Dieet: </strong></td>
      <td>
        <?php
        for ($x = 0; $x < count($row_things); $x++) {
          if (preg_match("/Dieet:.*$/i", $row_things[$x]) == 1) {
            echo str_replace("[", '', str_replace("Dieet: ", '', $row_things[$x])) . ",";
          }
        }
        ;
        ?>
      </td>
    </tr>
    <tr>
      <td width="20%" <strong>Allergies: </strong></td>
      <td>
        <?php
        for ($x = 0; $x < count($row_things); $x++) {
          if (preg_match("/Allergie.*$/i", $row_things[$x]) == 1) {
            echo str_replace("Allergie: ", '', $row_things[$x]) . ", <br>";
          }
        }
        echo $row['other'];
        ;
        ?>
      </td>
    </tr>
  </table>
</body>

</html>