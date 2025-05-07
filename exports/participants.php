<?php
// globals
// config variable.
$APP = array();

$APP["loginpage"] = "/component/users/?view=login";

include_once('../db.php');
include_once("../_includes/functions.global.php");
include_once("../_includes/functions.playercap.php");
include_once("../_includes/joomla.php");
include_once('./current-players.php');

$UPLINK->set_charset("utf8mb4");

if (isset($_GET['selected_event'])) {
  $selected_event = $_GET['selected_event'];
} else {
  $selected_event = $EVENTID;
}


if (!in_array("32", $jgroups, true) && !in_array("30", $jgroups, true)) {
  header('Status: 303 Moved Temporarily', false, 303);
  header('Location: ../');
}
$tableSort = !empty($_GET['sort']) ? $_GET['sort'] : 'register_date desc';

if (isset($_POST['action'])) {
  if ($_POST['action'] == 'Export to CSV') {
    $tableSort = 'room + 0 asc, oc_fn asc';
    require './participants-sql.php';
    $data = array();
    while ($row = mysqli_fetch_array($res)) {
      if (strpos($row['foto'], 'afmelden') != false) {
        $photoconsent = "Yes";
      } else {
        $photoconsent = "";
      }
      if (preg_match("/[a-z]/i", $row['room'])) {
        $building = "Bastion";
      } else {
        $building = "Zonnedauw";
      }
      array_push($data, array(
        "OC Name" => utf8_decode($row['oc_fn']) . " " . utf8_decode($row['oc_tv']) . " " . utf8_decode($row['oc_ln']),
        "IC Name" => utf8_decode($row['ic_name']),
        "Factie" => ucfirst($row['faction']),
        "Soort inschrijf" => $row['type'],
        "Building" => $building,
        "Room" => $row['room'],
        "Foto Opt-Out" => $photoconsent
      ));
    }
    function filterData(&$str)
    {
      $str = preg_replace("/\t/", "\\t", $str);
      $str = preg_replace("/\r?\n/", "\\n", $str);
      if (strstr($str, '"'))
        $str = '"' . str_replace('"', '""', $str) . '"';
    }
    // Excel file name for download 
    $fileName = "registrant-export-" . date('Y-m-d H.i.s', time()) . ".csv";

    // Headers for download 
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    header("Content-Type:  text/csv; charset=UTF-8");

    $flag = false;
    foreach ($data as $row) {
      if (!$flag) {
        // display column names as first row 
        echo implode(",", array_keys($row)) . "\n";
        $flag = true;
      }
      // filter data 
      array_walk($row, 'filterData');
      echo implode(",", array_values($row)) . "\n";
    }
    exit;
  }
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
  <link rel="stylesheet" href="css/participants.css">
</head>

<body>
  <?php
  require './participants-sql.php';

  $event_title = $row2['title'];
  echo "<div id='printButton'>";
  echo "<button class=\"button\" id=\"printPageButton\" style=\"width: 100px;\" onClick=\"window.print();\">Print</button> <font color='red'>IMPORTANT: Before clicking print, change sorting to OC Naam (oplopend)!</font>
  </div>";
  echo '<font size="5">Participants for ';
  ?>
  <select name="eventid" id="eventid" onchange="location.href = '/eoschargen/exports/participants.php?&<?php if (isset($_GET["email"])) {
    echo 'email=' . $_GET["email"] . '&';
  } ?><?php if (isset($_GET["sort"])) {
     echo 'sort=' . $_GET["sort"] . '&';
   } ?>&selected_event=' + this.value; ">
    <?php while ($row_all_events = mysqli_fetch_array($res_all_events)) {
      if ($row_all_events['id'] == $selected_event) {
        $event_select = 'selected';
      } else {
        $event_select = '';
      }
      if ($row_all_events['id'] == $EVENTID) {
        $title = $row_all_events['title'] . " (Upcoming Event)";
      } else {
        $title = $row_all_events['title'];
      }
      echo '<option value="' . $row_all_events['id'] . '"' . $event_select . '>' . $title . "</option>";
    } ?>
  </select>
  <?php
  echo '- ' . "($row_count participants)</font><br>";
  ?>
  <div class="grid" id="pageHeader_noPrint">
    <table>
      <tr>
        <th>Soort Inschrijf</th>
        <th>Aantal Deelneemers</th>
      </tr>
      <?php while ($row2 = mysqli_fetch_array($res3)) {
        echo '<tr>';
        echo '<td>' . $row2['type'] . "</td>";
        echo '<td>' . $row2['count'] . "</td>";
        echo "</tr>";
      } ?>
      <?php if (in_array("32", $jgroups, true)) {
        echo '<tr><td>&nbsp;</td><td></td></tr>';
        echo '<tr>';
        echo '<td>Pending Payments (' . $event_title . ')</td> <td>€' . round($pending['amount'], 2) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>Number of Sponsor Tickets used this event:</td> <td>' . $sponsor['count'] . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>Number of Sponsor Tickets remaining:</td> <td>' . intval($remaining_tickets['tickets_remaining']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>Donations made this event:</td> <td>€' . round($donations['total_donations'], 2) . '</td>';
        echo '</tr>';
        if ((round($pending_old['amount'], 2) > 0) && $selected_event == $EVENTID) {
          echo '<tr>';
          echo '<td>Pending Payments (previous events)</td> <td>€' . round($pending_old['amount'], 2) . '</td>';
          echo '</tr>';
        }
      } ?>
    </table>
    <!-- <br><br> -->
    <table>
      <tr>
        <th width="10%">Faction</th>
        <th width="10%">Aantal Deelneemers op evenement</th>
      </tr>
      <?php while ($row3 = mysqli_fetch_array($res5)) {
        echo "<tr>";
        echo '<td>' . ucwords($row3['faction']) . "</td>";
        echo '<td>' . $row3['count'] . "</td>";
        echo '</tr>';
      } ?>
    </table>
    <table>
      <tr>
        <th width="10%">Faction</th>
        <th width="10%">Active Characters (since <?php echo player_cap_count_from(); ?>)</th>
      </tr>
      <?php
      $faction_caps = get_active_factions();
      while ($faction_cap_row = mysqli_fetch_array($faction_caps)) {
        echo "<tr>";
        echo '<td><a href="./player_cap/player_cap.php?faction=' . $faction_cap_row['faction'] . '">' . ucwords($faction_cap_row['faction']) . "</a></td>";
        echo '<td>' . $faction_cap_row['count'] . "</td>";
        echo '</tr>';
      } ?>
    </table>
  </div>
  <?php $email = !empty($_GET['email']) ? $_GET['email'] : '%%'; ?>
  <div id="CopyEmailButton">
    Type of E-mail Addresses to Copy:
    <select id="email_types" style="padding: 5px; border-radius: 2px; margin-bottom: 1rem;" onchange="location.href = '/eoschargen/exports/participants.php?<?php echo 'selected_event=' . $selected_event; ?>&<?php if (isset($_GET["sort"])) {
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
     where soort_inschrijving.field_value LIKE '$email' AND r.event_id = $selected_event and $notCancelled";
    $res4 = $UPLINK->query($sql4);
    $emails = '';
    while ($row4 = mysqli_fetch_array($res4)) {
      $emails = $emails . $row4['email'] . ';';
    }
    echo "<input type=\"text\" class=\"hidden-text\" value=\"$emails\" id=\"myInput\">";
    ?>
    <button class="button" onclick="copyTo()">Copy Participant E-mails</button><br><br>
  </div>
  <form method="post">
    <input type="submit" name="action" class="button" value="Export to CSV" />
  </form>
  <div id="SortBy">Sorteer op:
    <select id="sort_table" style="padding: 5px; border-radius: 2px; margin-bottom: 1rem;" onchange="location.href = '/eoschargen/exports/participants.php?<?php echo 'selected_event=' . $selected_event; ?>&<?php if (isset($_GET["email"])) {
           echo 'email=' . $_GET["email"] . '&';
         } ?>sort=' + this.value; ">
      <option value="oc_fn asc" <?php if ($tableSort === 'oc_fn asc')
        echo 'selected' ?>> OC Naam (Oplopend)</option>
        <option value="oc_fn desc" <?php if ($tableSort === 'oc_fn desc')
        echo 'selected' ?>>OC Naam (Aflopend)</option>
        <option value="ic_name asc" <?php if ($tableSort === 'ic_name asc')
        echo 'selected' ?>>IC Naam (Oplopend)</option>
        <option value="ic_name desc" <?php if ($tableSort === 'ic_name desc')
        echo 'selected' ?>>IC Naam (Aflopend)</option>
        <option value="type asc" <?php if ($tableSort === 'type asc')
        echo 'selected' ?>>Soort Inschrijf (Oplopend)</option>
        <option value="type desc" <?php if ($tableSort === 'type desc')
        echo 'selected' ?>>Soort Inschrijf (Aflopend)
        </option>
        <option value="room asc" <?php if ($tableSort === 'room desc')
        echo 'selected' ?>>Room (Oplopend)</option>
        <option value="room desc" <?php if ($tableSort === 'room asc')
        echo 'selected' ?>>Room (Aflopend)</option>
        <option value="register_date asc" <?php if ($tableSort === 'register_date asc')
        echo 'selected' ?>>Inschrijf
          Datum(Oplopend)</option>
        <option value="register_date desc" <?php if ($tableSort === 'register_date desc')
        echo 'selected' ?>>InschrijfDatum
          (Aflopend)</option>
        <option value="type desc, faction asc, oc_fn asc" <?php if ($tableSort === 'type desc, faction asc, oc_fn asc')
        echo 'selected' ?>>Factie(Oplopend)</option>
        <option value="type desc, faction desc, oc_fn asc" <?php if ($tableSort === 'type desc, faction desc, oc_fn asc')
        echo 'selected' ?>>Factie (Aflopend)</option>


      </select>
    </div>

    <table class="main">
      <thead>
        <th width="20%">OC Name</th>
        <th width="20%">IC Name</th>
        <th width="15%">Soort Inschrijf</th>
        <th width="15%">Factie</th>
        <th class="inschrijf_datum" width="10%">Inschrijf Datum</th>
        <th>Room</th>
        <th width="25%">Handtekening</th>
        <th width="10%">Foto Opt-Out</th>
      </thead>
      <?php

      ///START MAIN TABLE
      
      while ($row = mysqli_fetch_array($res)) {
        echo "<tr>" . "<td>";
        echo "<a class='playername' href='participant_detail.php?participant_id=" . $row['id'] . "'>" . $row['oc_fn'] . " " . $row['oc_tv'] . " " . $row['oc_ln'];
        echo "</td>";
        echo '<td>' . $row['ic_name'] . "</td>";
        echo '<td>' . $row['type'] . "</td>";
        echo '<td>' . ucwords($row['faction']) . "</td>";
        echo '<td class="inschrijf_datum">' . $row['register_date'] . "</td>";
        echo '<td>' . $row['room'] . '</td>';
        echo '<td height="40px">&nbsp;</td>';
        if (strpos($row['foto'], 'afmelden') != false) {
          echo '<td height="40px">Yes</td>';
        } else {
          echo '<td height="40px">&nbsp;</td>';
        }
        echo "</tr>";
      }
      echo "</table>";
      ?>
    </div>
</body>

</html>