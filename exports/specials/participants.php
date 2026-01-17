<?php
include_once __DIR__ . "/../../_includes/includes.php";
include_once  $APP["root"] . "/_includes/functions.playercap.php";
require './participants-sql.php';
$UPLINK->set_charset("utf8mb4");


if (!in_array("32", $jgroups, true) && !in_array("30", $jgroups, true)) {
  header('Status: 303 Moved Temporarily', false, 303);
  header('Location: ../');
}
if (isset($_POST['action'])) {
  if ($_POST['action'] == 'Export to CSV') {
    $tableSort = 'oc_fn asc';
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
  <button class="button" id="printPageButton" style="width: 100px;" onClick="window.print();">Print</button>
  <font color='red'>IMPORTANT: Before clicking print, change sorting to OC Naam (oplopend)!</font>
  </div>
  <font size="5">Participants for
    <select name="eventid" id="eventid"
      onchange="location.href = '<?= build_url(null, ['selected_event' => null]) ?>&selected_event=' + this.value;">

      <?php while ($row = mysqli_fetch_array($res_all_events)):
        // 1. Determine if selected
        $selected = ($row['id'] == $selected_event) ? 'selected' : '';

        // 2. Format the title
        $title = $row['title'] . ($row['id'] == $SPECIALEVENTID ? " (Upcoming Event)" : "");
      ?>
        <option value="<?= $row['id'] ?>" <?= $selected ?>><?= $title ?></option>
      <?php endwhile; ?>

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
    <div id="CopyEmailButton">
      Type of E-mail Addresses to Copy:
      <?php
      // 1. Extract unique signup categories (Player, NPC, Crew, etc.)
      $signupTypes = [];
      if ($res3 && $res3->num_rows > 0) {
        $res3->data_seek(0);
        while ($row = $res3->fetch_assoc()) {
          if (!empty($row['type'])) {
            $signupTypes[] = $row['type'];
          }
        }
        // Remove duplicates and sort them alphabetically
        $signupTypes = array_unique($signupTypes);
        sort($signupTypes);
      }
      ?>

      <select id="email_types"
        style="padding: 5px; border-radius: 2px; margin-bottom: 1rem;"
        onchange="location.href = '<?= build_url(null, ['selected_event' => $selected_event, 'email' => null]) ?>&email=' + this.value;">

        <option value="%%" <?= ($email == '%%') ? 'selected' : '' ?>>Alles (All Types)</option>

        <?php foreach ($signupTypes as $type):
          $safeType = htmlspecialchars($type);
        ?>
          <option value="<?= $safeType ?>" <?= ($email == $safeType) ? 'selected' : '' ?>>
            <?= ucfirst($safeType) ?>
          </option>
        <?php endforeach; ?>

      </select>
      <?php
      $emails = '';
      while ($row4 = mysqli_fetch_array($res4)) {
        $emails = $emails . $row4['email'] . ';';
      }
      echo "<input type=\"text\" class=\"hidden-text\" value=\"$emails\" id=\"myInput\">";
      ?>
      <button class="button" onclick="copyTo()">Copy Participant E-mails</button><br><br>
    </div>

    <div id="SortBy">Sorteer op:
      <?php
      $sortOptions = [
        'oc_fn asc'                         => 'OC Naam (Oplopend)',
        'oc_fn desc'                        => 'OC Naam (Aflopend)',
        'ic_name asc'                       => 'IC Naam (Oplopend)',
        'ic_name desc'                      => 'IC Naam (Aflopend)',
        'type asc'                          => 'Soort Inschrijf (Oplopend)',
        'type desc'                         => 'Soort Inschrijf (Aflopend)',
        'room asc'                          => 'Room (Oplopend)',
        'room desc'                         => 'Room (Aflopend)',
        'register_date asc'                 => 'Inschrijf Datum (Oplopend)',
        'register_date desc'                => 'Inschrijf Datum (Aflopend)',
        'type desc, faction asc, oc_fn asc'  => 'Factie (Oplopend)',
        'type desc, faction desc, oc_fn asc' => 'Factie (Aflopend)',
      ];
      ?>
      <select id="sort_table"
        style="padding: 5px; border-radius: 2px; margin-bottom: 1rem;"
        onchange="location.href = '<?php echo build_url(null, ['selected_event' => $selected_event, 'sort' => null]); ?>&sort=' + this.value;">
        <?php foreach ($sortOptions as $value => $label):
          $selected = ($tableSort === $value) ? 'selected' : '';
        ?>
          <option value="<?= $value ?>" <?= $selected ?>><?= $label ?></option>
        <?php endforeach; ?>
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