<?php
include_once __DIR__ . "/../_includes/includes.php";
include_once  APP_ROOT . "/_includes/functions.playercap.php";
require './participants-sql.php';
$UPLINK->set_charset("utf8mb4");

// 1. Permissions & Redirects
if (!in_array("32", $jgroups, true) && !in_array("30", $jgroups, true)) {
  header('Location: ../', true, 303);
  exit;
}
// 2. CSV Export Logic (Memory Efficient)
if (isset($_POST['action']) && $_POST['action'] === 'Export to CSV') {
  exportParticipantsToCSV($res, 'oc_fn');
}

?>

<!-- 3. The rest of the page-->
<!DOCTYPE html>
<html>

<head>
  <script src="../_includes/js/print.js" defer></script>
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
  <link rel="stylesheet" href="../_includes/css/participants.css">
</head>

<body>
  <?php $event_title = $row2['title']; ?>
  <div id='printButton'>
    &nbsp;
    <a href="<?= build_url(null, ['sort' => 'oc_fn asc', 'print' => 'true']) ?>"
      class="button"
      style="width: 100px; text-decoration: none; display: inline-block;">
      Print
    </a>
  </div>
  <font size="5">Participants for
    <select name="eventid" id="eventid"
      onchange="location.href = '<?= build_url(null, ['selected_event' => null]) ?>&selected_event=' + this.value;">

      <?php while ($row = mysqli_fetch_array($res_all_events)):
        // 1. Determine if selected
        $selected = ($row['id'] == $selected_event) ? 'selected' : '';

        // 2. Format the title
        $title = $row['title'] . ($row['id'] == $EVENTID ? " (Upcoming Event)" : "");
      ?>
        <option value="<?= $row['id'] ?>" <?= $selected ?>><?= $title ?></option>
      <?php endwhile; ?>

    </select>
    <?php
    echo '- ' . "($row_count participants)</font><br>";
    ?>
    <div class="grid" id="pageHeader_noPrint">
      <table>
        <thead>
          <tr>
            <th>Soort Inschrijf</th>
            <th>Aantal Deelnemers</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row2 = mysqli_fetch_array($res3)): ?>
            <tr>
              <td><?= htmlspecialchars($row2['type']) ?></td>
              <td><?= $row2['count'] ?></td>
            </tr>
          <?php endwhile; ?>

          <?php if (in_array("32", $jgroups, true)):
            // Collect summary items into an array for a cleaner loop
            $summaries = [
              ["Pending Payments ($event_title)", "€" . number_format($pending['amount'] ?? 0, 2)],
              ["Number of Sponsor Tickets used on $event_title:", $sponsor['count'] ?? 0],
              ["Number of Sponsor Tickets remaining:", intval($remaining_tickets['tickets_remaining'] ?? 0)],
              ["Donations made this event:", "€" . number_format($donations['total_donations'] ?? 0, 2)]
            ];

            if (round($pending_old['amount'] ?? 0, 2) > 0 && $selected_event == $EVENTID) {
              $summaries[] = ["Pending Payments (previous events)", "€" . number_format($pending_old['amount'] ?? 0, 2)];
            }
          ?>
            <tr>
              <td>&nbsp;</td>
              <td></td>
            </tr>
            <?php foreach ($summaries as $item): ?>
              <tr>
                <td><?= $item[0] ?></td>
                <td><?= $item[1] ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <table>
        <thead>
          <tr>
            <th width="50%">Faction</th>
            <th width="50%">Aantal Deelnemers op evenement</br></th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row3 = mysqli_fetch_array($res5)): ?>
            <tr>
              <td><?= ucwords($row3['faction']) ?></td>
              <td><?= $row3['count'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <table>
        <thead>
          <tr>
            <th width="50%">Faction</th>
            <th width="50%">Active Characters (since <?= player_cap_count_from() ?>)</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $faction_caps = get_active_factions();
          while ($faction_cap_row = mysqli_fetch_array($faction_caps)):
          ?>
            <tr>
              <td>
                <a href="./player_cap/player_cap.php?faction=<?= urlencode($faction_cap_row['faction']) ?>">
                  <?= ucwords($faction_cap_row['faction']) ?>
                </a>
              </td>
              <td><?= $faction_cap_row['count'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
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
    <?php
    // Define table columns: [Label, Data Key, Width, Class, Sort Asc, Sort Desc, Match Patterns]
    $tableColumns = [
      'oc_name' => [
        'label'     => 'OC Name',
        'key'       => 'oc_full_name',
        'width'     => '20%',
        'class'     => '',
        'sort_asc'  => 'oc_fn asc',
        'sort_desc' => 'oc_fn desc',
        'match'     => ['oc_fn asc', 'oc_fn desc', 'oc_fn']
      ],
      'ic_name' => [
        'label'     => 'IC Name',
        'key'       => 'ic_name',
        'width'     => '20%',
        'class'     => '',
        'sort_asc'  => 'ic_name asc',
        'sort_desc' => 'ic_name desc',
        'match'     => ['ic_name asc', 'ic_name desc', 'ic_name']
      ],
      'type' => [
        'label'     => 'Soort Inschrijf',
        'key'       => 'type',
        'width'     => '15%',
        'class'     => '',
        'sort_asc'  => 'type asc',
        'sort_desc' => 'type desc',
        'match'     => ['type asc', 'type desc', 'type']
      ],
      'faction' => [
        'label'     => 'Factie',
        'key'       => 'faction_clean',
        'width'     => '15%',
        'class'     => '',
        'sort_asc'  => 'type desc, faction asc, oc_fn asc',
        'sort_desc' => 'type desc, faction desc, oc_fn asc',
        'match'     => [
          'type desc, faction asc, oc_fn asc',
          'type desc, faction desc, oc_fn asc',
          'faction asc',
          'faction desc',
          'faction'
        ]
      ],
      'register_date' => [
        'label'     => 'Inschrijf Datum',
        'key'       => 'register_date',
        'width'     => '10%',
        'class'     => 'inschrijf_datum',
        'sort_asc'  => 'register_date asc',
        'sort_desc' => 'register_date desc',
        'match'     => ['register_date asc', 'register_date desc', 'register_date']
      ],
      'room' => [
        'label'     => 'Room',
        'key'       => 'room',
        'width'     => 'auto',
        'class'     => '',
        'sort_asc'  => 'room asc',
        'sort_desc' => 'room desc',
        'match'     => ['room asc', 'room desc', 'room']
      ],
      'signature' => [
        'label'     => 'Handtekening',
        'key'       => 'signature',
        'width'     => '25%',
        'class'     => '',
        'sort_asc'  => null,
        'sort_desc' => null
      ],
      'photo_status' => [
        'label'     => 'Foto Opt-Out',
        'key'       => 'photo_status',
        'width'     => '10%',
        'class'     => '',
        'sort_asc'  => 'foto asc',
        'sort_desc' => 'foto desc',
        'match'     => ['foto asc', 'foto desc', 'foto']
      ]
    ];
    ?>

    <table class="main">
      <thead>
        <tr>
          <?php foreach ($tableColumns as $colId => $col):
            $isSortable = !empty($col['sort_asc']);
            $isActive = false;
            $currentDir = '';
            $nextSort = $col['sort_asc'] ?? '';
            $sortIcon = '';

            if ($isSortable) {
              if ($tableSort === $col['sort_asc']) {
                $isActive = true;
                $currentDir = 'asc';
                $nextSort = $col['sort_desc'];
                $sortIcon = '&#9650;'; // ▲
              } elseif ($tableSort === $col['sort_desc']) {
                $isActive = true;
                $currentDir = 'desc';
                $nextSort = $col['sort_asc'];
                $sortIcon = '&#9660;'; // ▼
              } elseif (!empty($col['match']) && in_array($tableSort, $col['match'], true)) {
                $isActive = true;
                $currentDir = (stripos($tableSort, 'desc') !== false) ? 'desc' : 'asc';
                $nextSort = ($currentDir === 'asc') ? $col['sort_desc'] : $col['sort_asc'];
                $sortIcon = ($currentDir === 'asc') ? '&#9650;' : '&#9660;';
              } else {
                $nextSort = $col['sort_asc'];
                $sortIcon = '';
              }
            }
          ?>
            <th width="<?= $col['width'] ?>" class="<?= $col['class'] ?><?= $isSortable ? ' sortable' : '' ?>">
              <?php if ($isSortable): ?>
                <a href="<?= htmlspecialchars(build_url(null, ['selected_event' => $selected_event, 'sort' => $nextSort])) ?>"
                   class="sort-link<?= $isActive ? ' active ' . $currentDir : '' ?>"
                   title="Sort by <?= htmlspecialchars($col['label']) ?> (<?= $isActive && $currentDir === 'asc' ? 'descending' : 'ascending' ?>)">
                  <span><?= htmlspecialchars($col['label']) ?></span>
                  <span class="sort-indicator">
                    <?php if ($sortIcon !== ''): ?>
                      <?= $sortIcon ?>
                    <?php else: ?>
                      <span class="sort-idle">&#8645;</span>
                    <?php endif; ?>
                  </span>
                </a>
              <?php else: ?>
                <?= htmlspecialchars($col['label']) ?>
              <?php endif; ?>
            </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_array($res)):
          // Data Preparation
          $row['oc_full_name']  = trim($row['oc_fn'] . " " . ($row['oc_tv'] ?? '') . " " . $row['oc_ln']);
          $row['faction_clean'] = ucwords($row['faction'] ?? '');
          $row['signature']     = '&nbsp;';
          $row['photo_status']  = str_contains($row['foto'] ?? '', 'afmelden') ? 'Yes' : '&nbsp;';
        ?>
          <tr>
            <?php foreach ($tableColumns as $colId => $col):
              $key = $col['key'];
            ?>
              <?php if ($colId === 'oc_name'): ?>
                <td>
                  <a class="playername" href="participant_detail.php?participant_id=<?= $row['id'] ?>">
                    <?= htmlspecialchars($row['oc_full_name']) ?>
                  </a>
                </td>
              <?php else:
                $cellValue = $row[$key] ?? '';
              ?>
                <td class="<?= $col['class'] ?>"
                  style="<?= ($key === 'signature' || $key === 'photo_status') ? 'height:40px;' : '' ?>">
                  <?php if ($key === 'signature' || $key === 'photo_status'): ?>
                    <?= $cellValue ?>
                  <?php else: ?>
                    <?= htmlspecialchars($cellValue) ?>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
            <?php endforeach; ?>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    </div>
</body>

</html>