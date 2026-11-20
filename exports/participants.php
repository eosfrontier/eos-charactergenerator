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

<?php $event_title = $row2['title'] ?? ''; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Participants<?= $event_title !== '' ? ' - ' . htmlspecialchars($event_title) : '' ?></title>
  <script src="../_includes/js/print.js" defer></script>
  <script>
    function copyTo() {
      var copyText = document.getElementById("myInput");
      var btn = document.getElementById("copyEmailsBtn");

      var markCopied = function() {
        if (!btn) return;
        if (!btn.dataset.defaultText) {
          btn.dataset.defaultText = btn.textContent;
        }
        btn.textContent = "Copied!";
        btn.classList.add("is-copied");
        clearTimeout(btn._copyTimer);
        btn._copyTimer = setTimeout(function() {
          btn.textContent = btn.dataset.defaultText;
          btn.classList.remove("is-copied");
        }, 2000);
      };

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(copyText.value).then(markCopied).catch(function() {
          copyText.select();
          copyText.setSelectionRange(0, 99999);
          document.execCommand("copy");
          markCopied();
        });
      } else {
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        markCopied();
      }
    }
  </script>
  <link rel="stylesheet" href="../_includes/css/participants.css">
</head>

<body>
  <div class="page-container">
    <header class="page-header">
      <div class="page-title-group">
        <span class="page-title-label">Participants for</span>
        <select name="eventid" id="eventid" class="event-select"
          onchange="location.href = '<?= build_url(null, ['selected_event' => null]) ?>&selected_event=' + this.value;">
          <?php while ($row = mysqli_fetch_array($res_all_events)):
            // 1. Determine if selected
            $selected = ($row['id'] == $selected_event) ? 'selected' : '';

            // 2. Format the title
            $title = $row['title'] . ($row['id'] == $EVENTID ? " (Upcoming Event)" : "");
          ?>
            <option value="<?= htmlspecialchars((string)$row['id']) ?>" <?= $selected ?>><?= htmlspecialchars($title) ?></option>
          <?php endwhile; ?>
        </select>
        <span class="participant-count-badge" id="headerCountBadge">(<?= (int)$row_count ?> participants)</span>
      </div>
      <div id="printButton">
        <a href="<?= htmlspecialchars(build_url(null, ['sort' => 'oc_fn asc', 'print' => 'true'])) ?>"
          class="button print-btn">
          Print
        </a>
      </div>
    </header>

    <div class="grid" id="pageHeader_noPrint">
      <div class="table-card summary-card">
        <table class="summary-table">
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
                <td><?= htmlspecialchars((string)$row2['count']) ?></td>
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
              <tr class="summary-spacer">
                <td colspan="2"></td>
              </tr>
              <?php foreach ($summaries as $item): ?>
                <tr class="summary-meta-row">
                  <td><?= htmlspecialchars((string)$item[0]) ?></td>
                  <td><?= htmlspecialchars((string)$item[1]) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="table-card summary-card">
        <table class="summary-table">
          <thead>
            <tr>
              <th>Faction</th>
              <th>Aantal Deelnemers op evenement</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row3 = mysqli_fetch_array($res5)): ?>
              <tr>
                <td><?= htmlspecialchars(ucwords($row3['faction'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string)$row3['count']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <div class="table-card summary-card">
        <table class="summary-table">
          <thead>
            <tr>
              <th>Faction</th>
              <th>Active Characters (since <?= htmlspecialchars((string)player_cap_count_from()) ?>)</th>
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
                    <?= htmlspecialchars(ucwords($faction_cap_row['faction'] ?? '')) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars((string)$faction_cap_row['count']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div id="CopyEmailButton" class="action-bar">
      <label for="email_types" class="action-bar-label">Type of E-mail Addresses to Copy:</label>
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
      <div class="action-bar-controls">
        <select id="email_types"
          class="modern-select"
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
        ?>
        <input type="text" class="hidden-text" value="<?= htmlspecialchars($emails, ENT_QUOTES, 'UTF-8') ?>" id="myInput">
        <button type="button" id="copyEmailsBtn" class="button" onclick="copyTo()">Copy Participant E-mails</button>
      </div>
    </div>
    <?php
    // Define table columns: [Label, Data Key, Width, Class, Sort Asc, Sort Desc, Match Patterns, Filterable]
    $tableColumns = [
      'oc_name' => [
        'label'      => 'OC Name',
        'key'        => 'oc_full_name',
        'width'      => '20%',
        'class'      => '',
        'sort_asc'   => 'oc_fn asc',
        'sort_desc'  => 'oc_fn desc',
        'match'      => ['oc_fn asc', 'oc_fn desc', 'oc_fn'],
        'filterable' => true
      ],
      'ic_name' => [
        'label'      => 'IC Name',
        'key'        => 'ic_name',
        'width'      => '20%',
        'class'      => '',
        'sort_asc'   => 'ic_name asc',
        'sort_desc'  => 'ic_name desc',
        'match'      => ['ic_name asc', 'ic_name desc', 'ic_name'],
        'filterable' => true
      ],
      'type' => [
        'label'      => 'Soort Inschrijf',
        'key'        => 'type',
        'width'      => '15%',
        'class'      => '',
        'sort_asc'   => 'type asc',
        'sort_desc'  => 'type desc',
        'match'      => ['type asc', 'type desc', 'type'],
        'filterable' => true
      ],
      'faction' => [
        'label'      => 'Factie',
        'key'        => 'faction_clean',
        'width'      => '15%',
        'class'      => '',
        'sort_asc'   => 'type desc, faction asc, oc_fn asc',
        'sort_desc'  => 'type desc, faction desc, oc_fn asc',
        'match'      => [
          'type desc, faction asc, oc_fn asc',
          'type desc, faction desc, oc_fn asc',
          'faction asc',
          'faction desc',
          'faction'
        ],
        'filterable' => true
      ],
      'register_date' => [
        'label'      => 'Inschrijf Datum',
        'key'        => 'register_date',
        'width'      => '10%',
        'class'      => 'inschrijf_datum',
        'sort_asc'   => 'register_date asc',
        'sort_desc'  => 'register_date desc',
        'match'      => ['register_date asc', 'register_date desc', 'register_date'],
        'filterable' => false
      ],
      'room' => [
        'label'      => 'Room',
        'key'        => 'room',
        'width'      => 'auto',
        'class'      => '',
        'sort_asc'   => 'room asc',
        'sort_desc'  => 'room desc',
        'match'      => ['room asc', 'room desc', 'room'],
        'filterable' => true
      ],
      'signature' => [
        'label'      => 'Handtekening',
        'key'        => 'signature',
        'width'      => '25%',
        'class'      => '',
        'sort_asc'   => null,
        'sort_desc'  => null,
        'filterable' => false
      ],
      'photo_status' => [
        'label'      => 'Foto Opt-Out',
        'key'        => 'photo_status',
        'width'      => '10%',
        'class'      => '',
        'sort_asc'   => 'foto asc',
        'sort_desc'  => 'foto desc',
        'match'      => ['foto asc', 'foto desc', 'foto'],
        'filterable' => true
      ]
    ];

    $initialFilters = [];
    foreach ($tableColumns as $colId => $col) {
      if (!empty($col['filterable'])) {
        $paramName = 'f_' . $colId;
        $val = $_GET[$paramName] ?? '';
        $initialFilters[$colId] = is_string($val) ? trim($val) : '';
      }
    }
    ?>

    <div id="filterStatusBar" class="filter-status-bar" style="display: none;">
      <span id="filterStats" class="filter-stats"></span>
      <button type="button" id="clearAllFiltersBtn" class="clear-all-filters-btn">Clear filters</button>
    </div>

    <div class="table-card main-table-card">
      <div class="table-scroll">
        <table class="main">
          <thead>
            <tr class="sort-row">
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
                <th width="<?= $col['width'] ?>" data-col="<?= htmlspecialchars($colId) ?>" class="<?= trim($col['class'] . ($isSortable ? ' sortable' : '') . ($isActive ? ' is-sorted' : '')) ?>">
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
            <tr class="filter-row">
              <?php foreach ($tableColumns as $colId => $col): ?>
                <th class="filter-cell <?= $col['class'] ?>" data-col="<?= htmlspecialchars($colId) ?>" data-label="<?= htmlspecialchars($col['label']) ?>">
                  <?php if (!empty($col['filterable'])):
                    $val = $initialFilters[$colId] ?? '';
                  ?>
                    <div class="col-filter-wrapper">
                      <input type="text"
                             class="col-filter-input<?= $val !== '' ? ' is-active' : '' ?>"
                             data-col="<?= htmlspecialchars($colId) ?>"
                             value="<?= htmlspecialchars($val) ?>"
                             placeholder="Filter..."
                             aria-label="Filter by <?= htmlspecialchars($col['label']) ?>"
                             autocomplete="off" />
                      <button type="button"
                              class="col-filter-clear"
                              data-col="<?= htmlspecialchars($colId) ?>"
                              title="Clear <?= htmlspecialchars($col['label']) ?> filter"
                              aria-label="Clear <?= htmlspecialchars($col['label']) ?> filter"
                              style="display: <?= $val !== '' ? 'inline-flex' : 'none' ?>;">&times;</button>
                    </div>
                  <?php endif; ?>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_array($res)):
              // Data Preparation
              $row['oc_full_name']  = trim(preg_replace('/\s+/', ' ', ($row['oc_fn'] ?? '') . " " . ($row['oc_tv'] ?? '') . " " . ($row['oc_ln'] ?? '')));
              $row['faction_clean'] = ucwords($row['faction'] ?? '');
              $row['signature']     = '&nbsp;';
              $row['photo_status']  = str_contains($row['foto'] ?? '', 'afmelden') ? 'Yes' : '&nbsp;';
            ?>
              <tr>
                <?php foreach ($tableColumns as $colId => $col):
                  $key = $col['key'];
                ?>
                  <?php if ($colId === 'oc_name'): ?>
                    <td data-col="oc_name" data-label="<?= htmlspecialchars($col['label']) ?>"><a class="playername" href="participant_detail.php?participant_id=<?= urlencode((string)$row['id']) ?>"><?= htmlspecialchars($row['oc_full_name']) ?></a></td>
                  <?php elseif ($key === 'signature' || $key === 'photo_status'): ?>
                    <td data-col="<?= htmlspecialchars((string)$colId) ?>" data-label="<?= htmlspecialchars($col['label']) ?>" class="<?= $col['class'] ?>" style="height:40px;"><?= $row[$key] ?></td>
                  <?php else:
                    $cellValue = trim((string)($row[$key] ?? ''));
                  ?>
                    <td data-col="<?= htmlspecialchars((string)$colId) ?>" data-label="<?= htmlspecialchars($col['label']) ?>" class="<?= $col['class'] ?>"><?= htmlspecialchars($cellValue) ?></td>
                  <?php endif; ?>
                <?php endforeach; ?>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <div id="noMatchesMsg" class="no-matches-msg" style="display: none;">No matching participants found.</div>
    </div>
  </div>

  <script>
    (function() {
      const tbody = document.querySelector('table.main tbody');
      const filterInputs = Array.from(document.querySelectorAll('.col-filter-input'));
      const statusBar = document.getElementById('filterStatusBar');
      const filterStats = document.getElementById('filterStats');
      const clearAllBtn = document.getElementById('clearAllFiltersBtn');
      const noMatchesMsg = document.getElementById('noMatchesMsg');

      if (!tbody || filterInputs.length === 0) return;

      const rows = Array.from(tbody.querySelectorAll('tr'));
      const totalCount = rows.length;

      function normalizeText(value) {
        return (value || '').replace(/\u00a0/g, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
      }

      function updateLinkParams(link, filterValues) {
        const linkUrl = new URL(link.href, window.location.origin);
        filterInputs.forEach(input => {
          const col = input.dataset.col;
          const paramKey = 'f_' + col;
          const rawVal = filterValues[col] || '';
          if (rawVal !== '') {
            linkUrl.searchParams.set(paramKey, rawVal);
          } else {
            linkUrl.searchParams.delete(paramKey);
          }
        });
        link.href = linkUrl.toString();
      }

      function applyFilters() {
        const activeFilters = [];
        const rawFilterValues = {};

        filterInputs.forEach(input => {
          const col = input.dataset.col;
          const rawVal = input.value.trim();
          const normalizedVal = normalizeText(rawVal);
          rawFilterValues[col] = rawVal;

          const clearBtn = document.querySelector('.col-filter-clear[data-col="' + col + '"]');
          if (rawVal !== '') {
            input.classList.add('is-active');
            if (clearBtn) clearBtn.style.display = 'inline-flex';
            activeFilters.push({ col: col, query: normalizedVal });
          } else {
            input.classList.remove('is-active');
            if (clearBtn) clearBtn.style.display = 'none';
          }
        });

        const isFiltering = activeFilters.length > 0;
        let visibleCount = 0;

        tbody.classList.toggle('is-filtered', isFiltering);

        rows.forEach(row => {
          let matches = true;

          for (let i = 0; i < activeFilters.length; i++) {
            const filter = activeFilters[i];
            const cell = row.querySelector('td[data-col="' + filter.col + '"]');
            const cellText = cell ? normalizeText(cell.textContent) : '';
            if (!cellText.includes(filter.query)) {
              matches = false;
              break;
            }
          }

          row.classList.remove('stripe-odd', 'stripe-even');
          if (matches) {
            row.style.display = '';
            visibleCount++;
            if (isFiltering) {
              row.classList.add(visibleCount % 2 === 1 ? 'stripe-odd' : 'stripe-even');
            }
          } else {
            row.style.display = 'none';
          }
        });

        if (statusBar && filterStats) {
          if (isFiltering) {
            filterStats.textContent = 'Showing ' + visibleCount + ' of ' + totalCount + ' participants';
            statusBar.style.display = 'flex';
          } else {
            filterStats.textContent = '';
            statusBar.style.display = 'none';
          }
        }

        if (noMatchesMsg) {
          noMatchesMsg.style.display = (isFiltering && visibleCount === 0) ? 'block' : 'none';
        }

        // Keep URL, column sort links, and print link synchronized
        try {
          const currentUrl = new URL(window.location.href);
          filterInputs.forEach(input => {
            const col = input.dataset.col;
            const paramKey = 'f_' + col;
            const rawVal = rawFilterValues[col] || '';
            if (rawVal !== '') {
              currentUrl.searchParams.set(paramKey, rawVal);
            } else {
              currentUrl.searchParams.delete(paramKey);
            }
          });
          window.history.replaceState({}, '', currentUrl.toString());

          document.querySelectorAll('table.main th a.sort-link').forEach(link => {
            updateLinkParams(link, rawFilterValues);
          });

          const printLink = document.querySelector('#printButton a');
          if (printLink) {
            updateLinkParams(printLink, rawFilterValues);
          }
        } catch (e) {}
      }

      filterInputs.forEach(input => {
        input.addEventListener('input', applyFilters);
        input.addEventListener('keydown', function(e) {
          if (e.key === 'Escape' && input.value !== '') {
            input.value = '';
            applyFilters();
          }
        });
      });

      document.querySelectorAll('.col-filter-clear').forEach(btn => {
        btn.addEventListener('click', function() {
          const col = btn.dataset.col;
          const input = document.querySelector('.col-filter-input[data-col="' + col + '"]');
          if (input) {
            input.value = '';
            applyFilters();
            input.focus();
          }
        });
      });

      if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
          filterInputs.forEach(input => {
            input.value = '';
          });
          applyFilters();
        });
      }

      applyFilters();
    })();
  </script>
</body>

</html>