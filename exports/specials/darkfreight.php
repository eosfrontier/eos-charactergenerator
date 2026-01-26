<?php
include_once __DIR__ . "/../_includes/includes.php";
$UPLINK->set_charset("utf8mb4");

// 1. Permissions & Redirects
if (!in_array("66", $jgroups, true)) {
  header('Location: ../', true, 303);
  exit;
}

$sql = "SELECT faction FROM ecc_characters WHERE accountID = $jid AND sheet_status = 'active'";
$res = $UPLINK->query($sql);
$char = mysqli_fetch_array($res);
$faction = $char['faction'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .pdf-container {
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="pdf-container">
    <iframe 
        src="https://mozilla.github.io/pdf.js/web/viewer.html?file=https://www.eosfrontier.space/eoschargen/exports/specials/briefings/<?= $faction ?>.pdf" 
        width="100%" 
        height="800px">
    </iframe>
</div>

</body>
</html>