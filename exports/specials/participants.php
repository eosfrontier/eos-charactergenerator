<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once("../../_includes/config.php");
$APP = array();

#$APP["loginpage"] = "/return-to-special-registrants-list";

include_once('../../db.php');
include_once('../../_includes/functions.global.php');
include_once('../../_includes/joomla.php');

$UPLINK->set_charset("utf8mb4");

if (isset($_GET['selected_event'])) {
  $selected_event = $_GET['selected_event'];
} else {
  $selected_event = $SPECIALEVENTID;
}


if (!in_array("32", $jgroups, true) && !in_array("30", $jgroups, true)) {
  header('Status: 303 Moved Temporarily', false, 303);
  header('Location: ../');
}
$tableSort = !empty($_GET['sort']) ? $_GET['sort'] : 'register_date desc';
require './participants-sql.php';
if (isset($_POST['action'])) {
  if ($_POST['action'] == 'Export to CSV') {
    $tableSort = 'oc_fn asc';
    require './participants-sql.php';
    $data = array();
    while ($row = mysqli_fetch_array($res)) {
      if (strpos($row['foto'], 'afmelden') != false) {
        $photoconsent = "Yes";
      } else {
        $photoconsent = "";
      }
      array_push($data, array(
        "OC Name" => $row['oc_fn'] . " " . $row['oc_tv'] . " " . $row['oc_ln'],
        "IC Name" => $row['ic_name'],
        "Factie" => ucfirst($row['faction']),
        "Soort inschrijf" => $row['type'],
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
    header("Content-Type:  text/csv; charset=-8");

    $flag = false;
    echo chr(0xEF) . chr(0xBB) . chr(0xBF);
    $file = fopen('php://output', 'w+');
    $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);
    fputs($file, $bom);
    foreach ($data as $row) {
      if (!$flag) {
        // display column names as first row 
        fputs($file, implode(",", array_keys($row)) . "\n");
        $flag = true;
      }
      // filter data 
      array_walk($row, 'filterData');
      fputs($file, implode(",", array_values($row)) . "\n");
    }
    fclose($file);
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
  <link rel="stylesheet" href="../css/participants.css">
</head>

<body>
  <?php
  $event_title = $row2['title'];
  echo "<div id='printButton'>";
  ?>
  &nbsp;
  <form method="post">
    <input type="submit" name="action" class="button" value="Export to CSV" />
  </form>
  <?php
  echo "<button class=\"button\" id=\"printPageButton\" style=\"width: 100px;\" onClick=\"window.print();\">Print</button> <font color='red'>IMPORTANT: Before clicking print, change sorting to OC Naam (oplopend)!</font>
  </div>";
  echo '<font size="5">Participants for ';
  $is_set_params = '';
  if (isset($_GET["email"])) {
    $is_set_params = $is_set_params . 'email=' . $_GET["email"] . '&';
  };
  if (isset($_GET["sort"])) {
    $is_set_params = $is_set_params . 'sort=' . $_GET["sort"] . '&';
  }

  ?>
  <select name="eventid" id="eventid" onchange="location.href = '/eoschargen/exports/specials/participants.php?<?php echo $is_set_params; ?>selected_event=' + this.value; ">
    <?php while ($row_all_events = mysqli_fetch_array($res_all_events)) {
      if ($row_all_events['id'] == $selected_event) {
        $event_select = 'selected';
      } else {
        $event_select = '';
      }
      if ($row_all_events['id'] == $SPECIALEVENTID) {
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
        // echo '<td>Number of Sponsor Tickets used on ' . $event_title . ':</td> <td>' . $sponsor['count'] . '</td>';
        // echo '</tr>';
        // echo '<tr>';
        // echo '<td>Number of Sponsor Tickets remaining:</td> <td>' . intval($remaining_tickets['tickets_remaining']) . '</td>';
        // echo '</tr>';
        // echo '<tr>';
        echo '<td>Donations made this event:</td> <td>€' . round($donations['total_donations'], 2) . '</td>';
        echo '</tr>';
        if ((round($pending_old['amount'], 2) > 0) && $selected_event == $SPECIALEVENTID) {
          echo '<tr>';
          echo '<td>Pending Payments (other events)</td> <td>€' . round($pending_old['amount'], 2) . '</td>';
          echo '</tr>';
        }
      } ?>
    </table>
    <?php if ($res5 && $res5->num_rows > 0) { ?>
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
    <?php } ?>
    <!-- <table>
      <tr>
        <th width="10%">Faction</th>
        <th width="10%">Active Characters (since 
          <?php 
          // echo player_cap_count_from(); 
          ?>
        )</th>
      </tr>
      <?php
      // $faction_caps = get_active_factions();
      // while ($faction_cap_row = mysqli_fetch_array($faction_caps)) {
      //   echo "<tr>";
      //   echo '<td><a href="./player_cap/player_cap.php?faction=' . $faction_cap_row['faction'] . '">' . ucwords($faction_cap_row['faction']) . "</a></td>";
      //   echo '<td>' . $faction_cap_row['count'] . "</td>";
      //   echo '</tr>';
      // } 
      ?>
    </table>  -->

  </div>
  <?php $email = !empty($_GET['email']) ? $_GET['email'] : '%%'; ?>
  <div id="CopyEmailButton">
    Type of E-mail Addresses to Copy:
    <select id="email_types" style="padding: 5px; border-radius: 2px; margin-bottom: 1rem;" onchange="location.href = '/eoschargen/exports/specials/participants.php?<?php echo 'selected_event=' . $selected_event . '&';if (isset($_GET["sort"])) {echo 'sort=' . $_GET["sort"] . '&';} ?>email=' + this.value; ">
      <option value="%%" <?php echo $email == '%%' ? 'selected' : ''; ?>>Alles</option>
      <?php
      $seen_types = [];
      if ($res3 && $res3->num_rows > 0) {
        // Reset the result pointer to the start (in case it was used elsewhere)
        $res3->data_seek(0);

        while ($row = $res3->fetch_assoc()) {
            $current_type = $row['type'];

            // Only proceed if we haven't seen this type yet and it's not empty
            if (!empty($current_type) && !in_array($current_type, $seen_types)) {

                // Add this type to our "seen" list so it doesn't repeat
                $seen_types[] = $current_type;

                // Escape for HTML safety
                $safe_type = htmlspecialchars($current_type);
                echo '<option value="' . $safe_type . '"';
                echo $email == $safe_type ? 'selected' : '';
                echo '>' . $safe_type . '</option>';
            }
        }
      }
    ?>
    </select>
    <?php
    $sql4 = "SELECT r.email as email from joomla.jml_eb_registrants r
     left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 118)
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

  <div id="SortBy">Sorteer op:
    <select id="sort_table" style="padding: 5px; border-radius: 2px; margin-bottom: 1rem;" onchange="location.href = '/eoschargen/exports/specials/participants.php?<?php echo 'selected_event=' . $selected_event; ?>&<?php if (isset($_GET["email"])) {
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
      <option value="room asc" <?php if ($tableSort === 'room + 0 desc')
                                  echo 'selected' ?>>Room (Oplopend)</option>
      <option value="room desc" <?php if ($tableSort === 'room + 0 asc')
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