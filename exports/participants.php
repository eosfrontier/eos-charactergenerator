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

if (isset($_GET['selected_event'])) {
  $selected_event = $_GET['selected_event'];
}
else {
  $selected_event = $EVENTID;
}


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
   <link rel="stylesheet" href="css/participants.css">
</head>

<body>
  <?php
  $tableSort = !empty($_GET['sort']) ? $_GET['sort'] : 'register_date desc';
  $sql_all_events = "SELECT e.id, e.title FROM jml_eb_events e
  LEFT JOIN joomla.jml_eb_event_categories cats ON (cats.event_id = e.id)
  WHERE cats.category_id = 1 AND e.id <= $EVENTID
  ORDER By event_date;";
  $res_all_events = $UPLINK->query($sql_all_events);?>
    <?php
  $sql2 = "SELECT title FROM jml_eb_events where id = $selected_event;";
  $res2 = $UPLINK->query($sql2);
  $row2 = mysqli_fetch_array($res2);
  $sql = "SELECT r.id as id, r.first_name as oc_fn, r.register_date, r.email as email, tussenvoegsel.field_value as oc_tv,
    r.last_name as oc_ln, faction.character_name as ic_name, soort_inschrijving.field_value as type, faction.faction as faction, foto.field_value as foto
    from joomla.jml_eb_registrants r
    left join joomla.jml_eb_field_values charname on (charname.registrant_id = r.id and charname.field_id = 21 )
    left join joomla.ecc_characters faction ON (faction.characterID = substring_index(charname.field_value,' - ',-1))
    left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
    left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
    left join joomla.jml_eb_field_values foto on (foto.registrant_id = r.id and foto.field_id = 105)
    where soort_inschrijving.field_value = 'Speler' AND r.event_id = $selected_event and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
    (r.published in (0,1) AND r.payment_method = 'os_offline'))
    UNION
    select r.id as id, r.first_name as oc_fn, r.register_date, r.email as email, tussenvoegsel.field_value as oc_tv,
    r.last_name as oc_ln, NULL as ic_name, soort_inschrijving.field_value as type, NULL as faction, foto.field_value as foto
    from joomla.jml_eb_registrants r
    left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
    left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
    left join joomla.jml_eb_field_values foto on (foto.registrant_id = r.id and foto.field_id = 105)

    WHERE soort_inschrijving.field_value != 'Speler' AND r.event_id = $selected_event and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
    (r.published in (0,1) AND r.payment_method = 'os_offline')) ORDER BY $tableSort";
  $res = $UPLINK->query($sql);
  $row_count = mysqli_num_rows($res);

  $sql3 = "SELECT COUNT(r.id) as count, soort_inschrijving.field_value as type
  from joomla.jml_eb_registrants r       
  left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
  left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
  where  soort_inschrijving.field_value IS NOT NULL AND r.event_id = $selected_event  and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
  (r.published in (0,1) AND r.payment_method = 'os_offline'))
  GROUP BY soort_inschrijving.field_value";
  $res3 = $UPLINK->query($sql3);
  $sql5 = "SELECT faction.faction as faction, COUNT(*) as count
from joomla.jml_eb_registrants r
join joomla.jml_eb_field_values charname on (charname.registrant_id = r.id and charname.field_id = 21 )
join joomla.ecc_characters faction ON (faction.characterID = substring_index(charname.field_value,' - ',-1))
left join joomla.jml_eb_field_values tussenvoegsel on (tussenvoegsel.registrant_id = r.id and tussenvoegsel.field_id = 16)
left join joomla.jml_eb_field_values soort_inschrijving on (soort_inschrijving.registrant_id = r.id and soort_inschrijving.field_id = 14)
where soort_inschrijving.field_value = 'Speler' AND r.event_id = $selected_event and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
(r.published in (0,1) AND r.payment_method = 'os_offline')) GROUP by faction";
  $res5 = $UPLINK->query($sql5);

  $event_title = $row2['title'];
  echo "<div id='printButton'>";
  echo "<button class=\"button\" id=\"printPageButton\" style=\"width: 100px;\" onClick=\"window.print();\">Print</button> <font color='red'>IMPORTANT: Before clicking print, change sorting to OC Naam (oplopend)!</font>
  </div>";
  echo '<font size="5">Participants for ';
  ?>
  <select style="font-size:20px;" name="eventid" id="eventid" onchange="location.href = '/eoschargen/exports/participants.php?&<?php if (isset($_GET["email"])) {
    echo 'email=' . $_GET["email"] . '&'; } ?><?php if (isset($_GET["sort"])) {
    echo 'sort=' . $_GET["sort"] . '&'; } ?>&selected_event=' + this.value; ">
<?php while ($row_all_events = mysqli_fetch_array($res_all_events)) {
      if ($row_all_events['id'] == $selected_event)  {
        $event_select = 'selected';
      }
      else {
      $event_select = '';
      }
      if ($row_all_events['id'] == $EVENTID){
        $title = $row_all_events['title'] . " (Upcoming Event)";
      }
      else {
        $title = $row_all_events['title'];
      }
      echo '<option value="' . $row_all_events['id'] . '"' . $event_select . '>' . $title . "</option>";
      }?>
</select>
<?php
 echo '- '  . "($row_count participants)</font><br>";
  ?>
  <div class="grid">
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
        }?>
      <?php if (in_array("32", $jgroups, true)) {
      $sql_pending = 'SELECT (SUM(payment_amount) - SUM(discount_amount)) as amount FROM jml_eb_registrants WHERE payment_method="os_offline" AND published=0 AND event_id = ' . $selected_event;
      $res_pending = $UPLINK->query($sql_pending);
      $pending = mysqli_fetch_array($res_pending);
      $sql_pending_old = 'SELECT (SUM(payment_amount) - SUM(discount_amount)) as amount FROM jml_eb_registrants WHERE payment_method="os_offline" AND published=0 AND event_id <> ' . $selected_event;
      $res_pending_old = $UPLINK->query($sql_pending_old);
      $pending_old = mysqli_fetch_array($res_pending_old);
      $sql_sponsor = "SELECT COUNT(r.id) as count
              from jml_eb_registrants r
              join jml_eb_field_values v3 ON (v3.registrant_id = r.id AND v3.field_id = 103)
              WHERE v3.field_value = 'Yes' AND r.event_id = $EVENTID AND 
              ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR 
				  r.payment_method = 'os_bancontact')) OR (r.published in (0,1) AND r.payment_method = 'os_offline'))";
      $res_sponsor = $UPLINK->query($sql_sponsor);
      $sponsor = mysqli_fetch_array($res_sponsor);
      $total_sponsor_tickets_purchased = "SELECT SUM(total_amount) from jml_eb_registrants r
          WHERE  r.event_id = 24 AND ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' 
			 OR r.payment_method = 'os_bancontact'))
          OR (r.published in (0,1) AND r.payment_method = 'os_offline'))";
      $fifteen_sponsor_tickets_used= "SELECT COUNT(r.id) * 15.00 as count from jml_eb_registrants r
          join jml_eb_field_values v3 ON (v3.registrant_id = r.id AND v3.field_id = 103)
          WHERE v3.field_value = 'Yes' AND r.event_id = 23 AND ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR
          r.payment_method = 'os_bancontact')) OR (r.published in (0,1) AND r.payment_method = 'os_offline'))";
      $twenty_sponsor_tickets_used="SELECT COUNT(r.id) * 20.00 as count from jml_eb_registrants r
          join jml_eb_field_values v3 ON (v3.registrant_id = r.id AND v3.field_id = 103)
          WHERE v3.field_value = 'Yes' AND r.event_id != 23 AND ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR
          r.payment_method = 'os_bancontact')) OR (r.published in (0,1) AND r.payment_method = 'os_offline'))";
      $sql_sponsor_tickets_remain = "SELECT (($total_sponsor_tickets_purchased) - ($fifteen_sponsor_tickets_used) - ($twenty_sponsor_tickets_used))/20 as tickets_remaining";
      $res_sponsor_tickets_remain = $UPLINK->query($sql_sponsor_tickets_remain);
      $remaining_tickets = mysqli_fetch_array($res_sponsor_tickets_remain);
      $sql_donations = "SELECT sum(v3.field_value) AS total_donations from jml_eb_registrants r
              join jml_eb_field_values v3 ON (v3.registrant_id = r.id AND v3.field_id = 102)
				      WHERE  r.event_id = 26 AND ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR 
				      r.payment_method = 'os_bancontact')) OR (r.published in (0,1) AND r.payment_method = 'os_offline'));";
      $donations = mysqli_fetch_array($UPLINK->query($sql_donations));
      echo '<tr>';
      echo '<td>Pending Payments ('. $event_title . ')</td> <td>€' . round($pending['amount'],2) . '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>Number of Sponsor Tickets used this event:</td> <td>' . $sponsor['count'] . '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>Number of Sponsor Tickets remaining:</td> <td>' . intval($remaining_tickets['tickets_remaining']) . '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>Donations made this event:</td> <td>€' . round($donations['total_donations'],2) . '</td>';
      echo '</tr>';
      if ( (round($pending_old['amount'],2) > 0) && $selected_event == $EVENTID){
        echo '<tr>';
        echo '<td>Pending Payments (previous events)</td> <td>€' . round($pending_old['amount'],2) . '</td>';
        echo '</tr>';
      }
      }?>
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
      }?>
    </table>
    <table>
      <tr>
        <th width="10%">Faction</th>
        <th width="10%">Active Characters (since <?php echo player_cap_count_from();?>)</th>
      </tr>
      <?php 
      $faction_caps = get_active_factions();
      while ($faction_cap_row = mysqli_fetch_array($faction_caps)) {
      echo "<tr>";
      echo '<td><a href="./player_cap/player_cap.php?faction=' . $faction_cap_row['faction'] . '">' . ucwords($faction_cap_row['faction']) . "</a></td>";
        echo '<td>' . $faction_cap_row['count'] . "</td>";
        echo '</tr>';
      }?>
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
    where soort_inschrijving.field_value LIKE '$email' AND r.event_id = $selected_event and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR
    (r.published in (0,1) AND r.payment_method = 'os_offline'))";
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
    <select id="sort_table" style="padding: 5px; border-radius: 2px; margin-bottom: 1rem;" onchange="location.href = '/eoschargen/exports/participants.php?<?php echo 'selected_event=' . $selected_event; ?>&<?php if (isset($_GET["email"])) {
      echo 'email=' . $_GET["email"] . '&'; } ?>sort=' + this.value; ">
      <option value="oc_fn asc" <?php if ($tableSort === 'oc_fn asc') echo 'selected' ?>> OC Naam (Oplopend)</option>
      <option value="oc_fn desc" <?php if ($tableSort === 'oc_fn desc') echo 'selected' ?>>OC Naam (Aflopend)</option>
      <option value="ic_name asc" <?php if ($tableSort === 'ic_name asc') echo 'selected' ?>>IC Naam (Oplopend)</option>
      <option value="ic_name desc" <?php if ($tableSort === 'ic_name desc') echo 'selected' ?>>IC Naam (Aflopend)</option>
      <option value="type asc" <?php if ($tableSort === 'type asc') echo 'selected' ?>>Soort Inschrijf (Oplopend)</option>
      <option value="type desc" <?php if ($tableSort === 'type desc') echo 'selected' ?>>Soort Inschrijf (Aflopend)</option>
      <option value="register_date asc" <?php if ($tableSort === 'register_date asc') echo 'selected' ?>>Inschrijf Datum(Oplopend)</option>
      <option value="register_date desc" <?php if ($tableSort === 'register_date desc') echo 'selected' ?>>InschrijfDatum (Aflopend)</option>
      <option value="type desc, faction asc, oc_fn asc" <?php if ($tableSort === 'type desc, faction asc, oc_fn asc') echo 'selected' ?>>Factie(Oplopend)</option>
      <option value="type desc, faction desc, oc_fn asc" <?php if ($tableSort === 'type desc, faction desc, oc_fn asc') echo 'selected' ?>>Factie (Aflopend)</option>

    </select>
  </div>
  <?php
  echo '<table class="main">';
  echo '<th width="20%">OC Name</th>';
  echo '<th width="20%">IC Name</th>';
  echo '<th width="15%">Soort Inschrijf</th>';
  echo '<th width="15%">Factie</th>';
  echo '<th class="inschrijf_datum" width="10%">Inschrijf Datum</th>';
  echo '<th width="25%">Handtekening</th>';
  echo '<th width="10%">Foto Opt-Out</th>';

  while ($row = mysqli_fetch_array($res)) {
    echo "<tr>" . "<td>";
    echo "<a class='playername' href='participant_detail.php?participant_id=" . $row['id'] . "'>" . $row['oc_fn'] . " " . $row['oc_tv'] . " " . $row['oc_ln'];
    echo "</td>";
    echo '<td>' . $row['ic_name'] . "</td>";
    echo '<td>' . $row['type'] . "</td>";
    echo '<td>' . ucwords($row['faction']) . "</td>";
    echo '<td class="inschrijf_datum">' . $row['register_date'] . "</td>";
    echo '<td height="40px">&nbsp;</td>';
    if (strpos($row['foto'],'afmelden') != false) {
    echo '<td height="40px">Yes</td>';
    }
    else {
    echo '<td height="40px">&nbsp;</td>';
    }
    echo "</tr>";
  }
  echo "</table>";
  ?>
  </div>
</body>

</html>