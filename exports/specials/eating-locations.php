<?php
// globals
// config variable.
$APP = array();

$APP["loginpage"] = "/component/users/?view=login";

include_once('../../db.php');
include_once("../../_includes/functions.global.php");

include_once('../current-players.php');

?>
<!DOCTYPE html>
<html>

<head>
  <style>
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

    tr:nth-child(even) {
      background-color: #dddddd;
      color: black;
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

    @media print {
      #printPageButton {
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
  $sql2 = "SELECT title FROM jml_eb_events where id = $SPECIALEVENTID;";
  $res2 = $UPLINK->query($sql2);
  $row2 = mysqli_fetch_array($res2);
  $sql = "select r.id, r.first_name as oc_fn, tussenvoegsel.field_value as oc_tv, 
    r.last_name as oc_ln, substring_index(v1.field_value,' - ',1) as ic_name
  from joomla.jml_eb_registrants r
  join joomla.jml_eb_field_values v1 on (v1.registrant_id = r.id and v1.field_id = 101)
  left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
  left join joomla.jml_eb_field_values v4 on (v4.registrant_id = r.id and v4.field_id = 100)
  where v4.field_value = 'Speler' AND r.event_id = $SPECIALEVENTID and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
  (r.published in (0,1) AND r.payment_method = 'os_offline'))
UNION
select r.id, r.first_name as oc_fn, tussenvoegsel.field_value as oc_tv, 
  r.last_name as oc_ln, NULL as ic_name
  from joomla.jml_eb_registrants r
  left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
  left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 100)
  WHERE soort_inschrijving.field_value != 'Speler' AND r.event_id = $SPECIALEVENTID and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
  (r.published in (0,1) AND r.payment_method = 'os_offline')) ORDER BY oc_fn";
  $res = $UPLINK->query($sql);
  $row_count = mysqli_num_rows($res);
  echo '<button class="button" id="printPageButton" style="width: 100px;" onClick="window.print();">Print</button>';
  echo '<font size="5">Participants for ' . $row2['title'] . '</font> - '
    . "<font size='4'>Bastion ($row_count)</font>";
  echo "<table>";
  echo "<th>OC Name</th>";
  echo "<th>IC Name</th>";
  echo "</tr>";

  while ($row = mysqli_fetch_array($res)) {
    echo "<tr>" . "<td>";
    if (isset($row['eetlocatie_override'])) {
    echo "<span style=\"color:red;\">****</span>" ; } 
    echo $row['oc_fn'] . " " . $row['oc_tv'] . " " . $row['oc_ln'];
    if (isset($row['eetlocatie_override'])) {
      echo "<span style=\"color:red;\">****</span>" ; }
    echo "</td>";
    echo '<td>' . $row['ic_name'] . "</td>";
    echo "</tr>";
  }
  echo "</table>";
  ?>
</body>

</html>