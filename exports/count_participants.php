<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/eoschargen/_includes/config.php";
function html_table($data = array())
{
    $rows = array();
    foreach ($data as $row) {
        $cells = array();
        foreach ($row as $cell) {
            $cells[] = "<td>{$cell}</td>";
        }
        $rows[] = "<tr>" . implode('', $cells) . "</tr>";
    }
    return "<table class='hci-table'>" . implode('', $rows) . "</table>";
}
$stmt=db::$conn->prepare("SELECT COUNT(r.id) AS aantaal_deelneemers, v2.field_value AS soort_inschrijving from jml_eb_registrants r
        join jml_eb_field_values v2 on (v2.registrant_id = r.id and v2.field_id = 14) /*Soort Inschrijving*/
        where r.event_id = 21  and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR (r.published in (0,1) AND r.payment_method = 'os_offline'))
        GROUP BY v2.field_value");
$res = $stmt->execute();
$aParticipants = $stmt->fetchAll();

echo html_table($aParticipants);
        ?>