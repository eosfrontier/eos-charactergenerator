<?php
// globals
// config variable.
$APP = array();

#$APP["loginpage"] = "/component/users/?view=login";
include_once __DIR__ . "/../_includes/includes.php";

$isModal = isset($_GET['modal']) && $_GET['modal'] === '1';

if (!in_array("32", $jgroups, true) && !in_array("30", $jgroups, true)) {
  if ($isModal) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<div class="detail-alert detail-alert-error">You do not have permission to view participant details. Your session may have expired &mdash; please refresh the page and sign in again.</div>';
    exit;
  }
  header('Status: 303 Moved Temporarily', false, 303);
  header('Location: ../');
  exit;
}

$UPLINK->set_charset("utf8mb4");

$participant_id = (int) ($_GET["participant_id"] ?? 0);

$sql = "SELECT r.id, r.event_id, e.title as event_title, r.first_name as oc_fn, r.register_date, r.email as email, tussenvoegsel.field_value as oc_tv, r.phone as phone,
  r.address, r.address2, r.city, r.state, r.zip, r.country, r.last_name as oc_ln, faction.character_name as ic_name, faction.faction as faction,
  soort_inschrijving.field_value as type, noodgevallen_naam.field_value as noodgevallen_naam,
  noodgevallen_telefoonnummer.field_value as noodgevallen_telefoonnummer, med_info.field_value as med_info, allergies.field_value as allergies, other.field_value as other
  from joomla.jml_eb_registrants r
  left join joomla.jml_eb_events e on (e.id = r.event_id)
  left join joomla.jml_eb_field_values charname on (charname.registrant_id = r.id and charname.field_id = 21)
  left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
  left join joomla.ecc_characters faction ON (faction.characterID = substring_index(charname.field_value,' - ',-1))
  left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
  left join joomla.jml_eb_field_values noodgevallen_naam on (noodgevallen_naam.registrant_id = r.id and noodgevallen_naam.field_id = 18)
  left join joomla.jml_eb_field_values noodgevallen_telefoonnummer on (noodgevallen_telefoonnummer.registrant_id = r.id and noodgevallen_telefoonnummer.field_id = 19)
  left join joomla.jml_eb_field_values med_info on (med_info.registrant_id = r.id and med_info.field_id = 17)
  left join joomla.jml_eb_field_values allergies on (allergies.registrant_id = r.id and allergies.field_id = 56)
  left join joomla.jml_eb_field_values other on (other.registrant_id = r.id and other.field_id = 57)
  where r.id = $participant_id
  limit 1";
$res = $UPLINK->query($sql);
$row = $res ? mysqli_fetch_assoc($res) : null;

$event_title = $row['event_title'] ?? '';
if ($event_title === '' && !empty($EVENTID)) {
  $sql2 = "SELECT title FROM jml_eb_events where id = " . (int) $EVENTID . ";";
  $res2 = $UPLINK->query($sql2);
  $row2 = $res2 ? mysqli_fetch_assoc($res2) : null;
  $event_title = $row2['title'] ?? '';
}

$parseDietAndAllergies = static function (?string $rawValue): array {
  $diets = [];
  $allergies = [];
  $raw = trim((string)($rawValue ?? ''));
  if ($raw === '') {
    return [$diets, $allergies];
  }

  $decoded = json_decode($raw, true);
  if (is_array($decoded)) {
    $items = $decoded;
  } else {
    $cleaned = str_replace(
      ["Allium(ui,prei,knoflook,bieslook,etc)", "\\/", "\"", "\\u00eb", "\\u00cb"],
      ["Allium(ui;prei;knoflook;bieslook;etc)", "/", "", "ë", "Ë"],
      $raw
    );
    $cleaned = trim($cleaned, "[] \t\n\r\0\x0B,");
    $items = $cleaned !== '' ? explode(',', $cleaned) : [];
  }

  foreach ($items as $entry) {
    $entry = trim((string) $entry);
    if ($entry === '') {
      continue;
    }
    if (preg_match('/^dieet:\s*(.*)$/i', $entry, $m)) {
      $val = trim($m[1]);
      if ($val !== '') {
        $diets[] = $val;
      }
    } elseif (preg_match('/^allergie:\s*(.*)$/i', $entry, $m)) {
      $val = trim($m[1]);
      if ($val !== '') {
        $allergies[] = $val;
      }
    } elseif (stripos($entry, 'dieet:') !== false) {
      $val = trim((string) preg_replace('/^.*dieet:\s*/i', '', $entry));
      if ($val !== '') {
        $diets[] = $val;
      }
    } elseif (stripos($entry, 'allergie') !== false) {
      $val = trim((string) preg_replace('/^.*allergie:\s*/i', '', $entry));
      if ($val !== '') {
        $allergies[] = $val;
      }
    }
  }

  return [$diets, $allergies];
};

$renderDetailContent = static function (?array $row, string $eventTitle, array $jgroups) use ($parseDietAndAllergies): void {
  if (!$row) {
    echo '<div class="detail-alert detail-alert-error">Participant not found.</div>';
    return;
  }

  $ocFullName = trim((string) preg_replace(
    '/\s+/',
    ' ',
    ($row['oc_fn'] ?? '') . ' ' . ($row['oc_tv'] ?? '') . ' ' . ($row['oc_ln'] ?? '')
  ));
  $country = (string) ($row['country'] ?? '');
  $phone = formatPhoneNumber($row['phone'] ?? '', $country);
  $phoneHref = preg_replace('/[^0-9+]/', '', $phone);
  $emergencyPhone = formatPhoneNumber($row['noodgevallen_telefoonnummer'] ?? '', $country);
  $emergencyPhoneHref = preg_replace('/[^0-9+]/', '', $emergencyPhone);
  $type = trim((string) ($row['type'] ?? ''));
  $isBoard = in_array("32", $jgroups, true);

  [$dietItems, $allergyItems] = $parseDietAndAllergies($row['allergies'] ?? null);
  $otherText = trim((string) ($row['other'] ?? ''));
  $medInfo = trim((string) ($row['med_info'] ?? ''));
  $emergencyName = trim((string) ($row['noodgevallen_naam'] ?? ''));
  ?>
  <div class="detail-sections"
       data-participant-name="<?= htmlspecialchars($ocFullName) ?>"
       data-event-title="<?= htmlspecialchars($eventTitle) ?>">

    <section class="detail-section">
      <h3 class="detail-section-title">OC Informatie</h3>
      <div class="table-card detail-card">
        <table class="detail-table">
          <tbody>
            <tr>
              <th>OC Name</th>
              <td><?= $ocFullName !== '' ? htmlspecialchars($ocFullName) : '<span class="detail-empty">&mdash;</span>' ?></td>
            </tr>
            <tr>
              <th>Soort inschrijf</th>
              <td><?= $type !== '' ? htmlspecialchars($type) : '<span class="detail-empty">&mdash;</span>' ?></td>
            </tr>
            <tr>
              <th>Telefoonnummer</th>
              <td>
                <?php if ($phone !== ''): ?>
                  <a class="detail-phone-link" href="tel:<?= htmlspecialchars($phoneHref !== '' ? $phoneHref : $phone) ?>"><?= htmlspecialchars($phone) ?></a>
                <?php else: ?>
                  <span class="detail-empty">&mdash;</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th>Adres</th>
              <td>
                <?php if (!$isBoard): ?>
                  <span class="detail-restricted">
                    You must be bestuur to retrieve this info.<br>
                    If you need this info, please ask a member of the board to access this record for you.
                  </span>
                <?php else:
                  $addrLines = [];
                  $addr1 = trim((string) ($row['address'] ?? ''));
                  $addr2 = trim((string) ($row['address2'] ?? ''));
                  $zipCity = trim(trim((string) ($row['zip'] ?? '')) . ' ' . trim((string) ($row['city'] ?? '')));
                  $countryLine = trim($country);
                  if ($addr1 !== '') $addrLines[] = htmlspecialchars($addr1);
                  if ($addr2 !== '') $addrLines[] = htmlspecialchars($addr2);
                  if ($zipCity !== '') $addrLines[] = htmlspecialchars($zipCity);
                  if ($countryLine !== '') $addrLines[] = htmlspecialchars($countryLine);
                ?>
                  <?= !empty($addrLines) ? implode('<br>', $addrLines) : '<span class="detail-empty">&mdash;</span>' ?>
                <?php endif; ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <?php if ($type === 'Speler'): ?>
      <section class="detail-section">
        <h3 class="detail-section-title">IC Informatie</h3>
        <div class="table-card detail-card">
          <table class="detail-table">
            <tbody>
              <tr>
                <th>IC Name</th>
                <td><?= trim((string)($row['ic_name'] ?? '')) !== '' ? htmlspecialchars(trim((string)$row['ic_name'])) : '<span class="detail-empty">&mdash;</span>' ?></td>
              </tr>
              <tr>
                <th>Factie</th>
                <td><?= trim((string)($row['faction'] ?? '')) !== '' ? htmlspecialchars(ucfirst(trim((string)$row['faction']))) : '<span class="detail-empty">&mdash;</span>' ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <section class="detail-section">
      <h3 class="detail-section-title">Nood Informatie</h3>
      <div class="table-card detail-card">
        <table class="detail-table">
          <tbody>
            <tr>
              <th>Te contacteren bij noodgevallen (naam)</th>
              <td><?= $emergencyName !== '' ? htmlspecialchars($emergencyName) : '<span class="detail-empty">&mdash;</span>' ?></td>
            </tr>
            <tr>
              <th>Noodgevallen telefoonnummer</th>
              <td>
                <?php if ($emergencyPhone !== ''): ?>
                  <a class="detail-phone-link" href="tel:<?= htmlspecialchars($emergencyPhoneHref !== '' ? $emergencyPhoneHref : $emergencyPhone) ?>"><?= htmlspecialchars($emergencyPhone) ?></a>
                <?php else: ?>
                  <span class="detail-empty">&mdash;</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th>Medische aandoeningen indien relevant</th>
              <td><?= $medInfo !== '' ? nl2br(htmlspecialchars($medInfo)) : '<span class="detail-empty">&mdash;</span>' ?></td>
            </tr>
            <tr>
              <th>Dieet</th>
              <td>
                <?php if (!empty($dietItems)): ?>
                  <?= htmlspecialchars(implode(', ', $dietItems)) ?>
                <?php else: ?>
                  <span class="detail-empty">&mdash;</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th>Allergies</th>
              <td>
                <?php if (!empty($allergyItems) || $otherText !== ''): ?>
                  <?php if (!empty($allergyItems)): ?>
                    <?= implode('<br>', array_map('htmlspecialchars', $allergyItems)) ?>
                    <?php if ($otherText !== ''): ?><br><?php endif; ?>
                  <?php endif; ?>
                  <?php if ($otherText !== ''): ?>
                    <?= nl2br(htmlspecialchars($otherText)) ?>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="detail-empty">&mdash;</span>
                <?php endif; ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
  <?php
};

if ($isModal) {
  header('Content-Type: text/html; charset=utf-8');
  header('Cache-Control: private, no-store');
  if (!$row) {
    http_response_code(404);
  }
  $renderDetailContent($row, $event_title, $jgroups);
  exit;
}

$pageOcName = $row
  ? trim((string) preg_replace('/\s+/', ' ', ($row['oc_fn'] ?? '') . ' ' . ($row['oc_tv'] ?? '') . ' ' . ($row['oc_ln'] ?? '')))
  : 'Participant Details';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageOcName) ?><?= $event_title !== '' ? ' - ' . htmlspecialchars($event_title) : '' ?></title>
  <link rel="stylesheet" href="../_includes/css/participants.css">
</head>

<body>
  <div class="page-container detail-page-container">
    <header class="page-header">
      <div class="page-title-group">
        <span class="page-title-label">Participant details for <?= htmlspecialchars($event_title) ?></span>
      </div>
      <div class="detail-page-actions">
        <button type="button" class="backButton" id="backButton" onclick="window.history.go(-1); return false;">Back</button>
        <button type="button" class="button print-btn" id="printPageButton" onclick="window.print();">Print</button>
      </div>
    </header>

    <?php $renderDetailContent($row, $event_title, $jgroups); ?>
  </div>
</body>

</html>
