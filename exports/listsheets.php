<?php

// BEGIN PLAYER COUNT QUERY
$sql = "SELECT SUM(totalchars) as totalchars, faction, building FROM (
        SELECT count(SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1)) as totalchars, c1.faction, v3.field_value as building from jml_eb_registrants r
              join jml_eb_field_values v1 on (v1.registrant_id = r.id and v1.field_id = 21) /*Character Name*/
              join jml_eb_field_values v2 on (v2.registrant_id = r.id and v2.field_id = 14) /*Soort Inschrijving*/
              join jml_eb_field_values v3 ON (v3.registrant_id = r.id AND v3.field_id = 36) /* Building */
              join ecc_characters c1 on c1.characterID = SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1)
              where v2.field_value = 'Speler' AND r.event_id = $EVENTID  AND  v3.field_value NOT LIKE 'medische%' AND `faction` LIKE '$_FACTION' AND v3.field_value = '$_BUILDING'
            and ((r.published = 1 AND (r.payment_method = 'os_ideal' or r.payment_method='os_paypal')) OR (r.published in (0,1) AND r.payment_method = 'os_offline'))
          UNION ALL
          SELECT count(SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1)) as totalchars, c1.faction, LEFT(v6.field_value,LOCATE(',',v6.field_value) - 1)  as building from jml_eb_registrants r
          join jml_eb_field_values v1 on (v1.registrant_id = r.id and v1.field_id = 21)/*Character Name*/
        join jml_eb_field_values v2 on (v2.registrant_id = r.id and v2.field_id = 14)/*Soort Inschrijving*/
        join jml_eb_field_values v3 ON (v3.registrant_id = r.id AND v3.field_id = 36)/* Building */
        join ecc_characters c1 on c1.characterID = SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1)
        left join joomla.jml_eb_field_values v6 on (v6.registrant_id = r.id and v6.field_id = 71)
          where v2.field_value = 'Speler' AND r.event_id = $EVENTID  AND  v3.field_value LIKE 'medische%' AND `faction` LIKE '$_FACTION'  AND LEFT(v6.field_value,LOCATE(',',v6.field_value) - 1) = '$_BUILDING'
         and ((r.published = 1 AND (r.payment_method = 'os_ideal' or r.payment_method='os_paypal')) OR (r.published in (0,1) AND r.payment_method = 'os_offline'))
          ) t1;";

        $res = $UPLINK->query($sql);
        $resCOUNT = mysqli_fetch_assoc($res)['totalchars'];
// END PLAYER COUNT QUERY

        echo "<select id=\"factionswitch\"
                  style=\"padding: 5px; border-radius: 2px; margin-bottom: 1rem;\"
                  onchange=\"location.href = '{$APP['header']}/exports/printsheet.php?offset=0&building=$_BUILDING&faction=' + this.value; \">
                <option value=\"\">Select faction</option>
                <option value=\"Aquila\">Aquila</option>
                <option value=\"Dugo\">Dugo</option>
                <option value=\"Ekanesh\">Ekanesh</option>
                <option value=\"Pendzal\">Pendzal</option>
                <option value=\"Sona\">Sona</option>
                <option value=\"\">ALL FACTIONS</option>
              </select>";
        echo "<select id=\"buildingswitch\"
              style=\"padding: 5px; border-radius: 2px; margin-bottom: 1rem;\"
              onchange=\"location.href = '{$APP['header']}/exports/printsheet.php?offset=0&faction=$_FACTION&building=' + this.value; \">
            <option value=\"\">Select building</option>
            <option value=\"Bastion\">Bastion</option>
            <option value=\"tweede gebouw\">FOB</option>
          </select>";
        $sql2 = "SELECT title FROM jml_eb_events where id = $EVENTID;";
        $res2 = $UPLINK->query($sql2);
        $row2 = mysqli_fetch_array($res2);

        echo "<div style=\"padding: 15px;\"><h1>" . $row2['title'] . "<br>Number of ";
        if ($_FACTION != '%') {echo $_FACTION . " ";}
        echo "Characters in $_BUILDING: $resCOUNT</h1><br/><br/>";

        $printresult = "";

        if (isset($_GET['offset']) && (int)$_GET['offset'] > 0) {
            $offset = (int)$_GET['offset'];
        }

        $limitFirst = $offset * $perPage;


        $pageNumber = 1;
        echo "Page ";
        for ($x = 0; $x < $resCOUNT; $x = ($x + $perPage)) {

            if (($pageNumber - 1) == $offset) {
                echo "<span style=\"padding:8px 4px; color: red;\"><button type='disabled'><strong><font size=4>$pageNumber</font></strong></button></span>";
            } else {
                echo "<a style=\"padding:8px 4px;\" href=\"" . $APP['header'] . "/exports/printsheet.php?offset=" . ($pageNumber - 1) . "&faction=$_FACTION&building=$_BUILDING\"><button>$pageNumber</button></a>";
            }

            $pageNumber++;
        }

        echo "<br/><br/>";


        $sql = "SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1) as characterID, c1.character_name, c1.faction, c1.sheet_status, c1.print_status from jml_eb_registrants r
        join jml_eb_field_values v1 on (v1.registrant_id = r.id and v1.field_id = 21)
	      join jml_eb_field_values v2 on (v2.registrant_id = r.id and v2.field_id = 14)
        join ecc_characters c1 on c1.characterID = SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1)
        join jml_eb_field_values v3 ON (v3.registrant_id = r.id AND v3.field_id = 36) /* Building */
        where v2.field_value = 'Speler' AND r.event_id = $EVENTID  and characterID <> 257 AND v3.field_value = '$_BUILDING' AND `faction` LIKE '$_FACTION' and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR (r.published in (0,1) AND r.payment_method = 'os_offline'))
        UNION
        SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1) as characterID, c1.character_name, c1.faction, c1.sheet_status, c1.print_status from jml_eb_registrants r
        join jml_eb_field_values v1 on (v1.registrant_id = r.id and v1.field_id = 21)
	      join jml_eb_field_values v2 on (v2.registrant_id = r.id and v2.field_id = 14)
        join ecc_characters c1 on c1.characterID = SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1)
        join jml_eb_field_values v3 ON (v3.registrant_id = r.id AND v3.field_id = 36) /* Building */
        left join joomla.jml_eb_field_values v6 on (v6.registrant_id = r.id and v6.field_id = 71) /*ROOM*/
        where v2.field_value = 'Speler' AND r.event_id = $EVENTID  and characterID <> 257 AND v3.field_value LIKE 'medische%' AND LEFT(v6.field_value,LOCATE(',',v6.field_value) - 1) = '$_BUILDING' AND `faction` LIKE '$_FACTION' and ((r.published = 1 AND (r.payment_method = 'os_ideal' OR r.payment_method = 'os_paypal' OR r.payment_method = 'os_bancontact')) OR (r.published in (0,1) AND r.payment_method = 'os_offline'))
          ORDER BY character_name
          LIMIT " . (int)$limitFirst . " , " . (int)$perPage . " ";
        $res = $UPLINK->query($sql);

        if ($res) {

            echo "<table style=\"border: 0; width: 100%;\">"
            . "<th>ID</th><th>Name</th><th>Faction</th><th>Print Status</th><th>Open Sheet</th>";
            while ($row = mysqli_fetch_assoc($res)) {
                // $xCHAR = $row['characterID'];
                $xTOPRINT = false;

                if ($row['print_status'] == $EVENTID) {

                    $xSTATUS = "<span style=\"color: green;\">Done</span>";
                    $xTOPRINT = false;
                } else {
                    $xSTATUS = "<span style=\"color: tomato;\">Unprinted</span>";
                    $xTOPRINT = true;
                }

                if ($row['character_name'] == "" || $row['character_name'] == null) {
                    $row['character_name'] = '<span style="color: tomato;">no name</span>';
                }



                echo "<tr>"
                . "<td>#" . $row['characterID'] . "</td>"
                . "<td>" . $row['character_name'] . "</td><td>" . $faction . "</td><td>" . $xSTATUS . "</td>"
                // ."<td><a href=\"".$APP['header']."/exports/printsheet.php?characterID=".$row['characterID']."\" target=\"_new\"><button>Sheets</button></a></td>"
                . "<td><button onclick=\"window.open('{$APP["header"]}/exports/printsheet.php?characterID={$row['characterID']}','sheets','width=1280,height=1000');\">View Sheet</button></td></tr>";
                unset($xSTATUS);
            }

            echo "</table>";
            if (isset($_GET['offset']) && (int)$_GET['offset'] > 0) {
                $offset = (int)$_GET['offset'];
            }

            $limitFirst = $offset * $perPage;


            $pageNumber = 1;
            echo "Page ";
            for ($x = 0; $x < $resCOUNT; $x = ($x + $perPage)) {

                if (($pageNumber - 1) == $offset) {
                    echo "<span style=\"padding:8px 4px; color: red;\"><button type='disabled'><strong><font size=4>$pageNumber</font></strong></button></span>";
                } else {
                    echo "<a style=\"padding:8px 4px;\" href=\"" . $APP['header'] . "/exports/printsheet.php?offset=" . ($pageNumber - 1) . "&faction=$_FACTION&building=$_BUILDING\"><button>$pageNumber</button></a>";
                }

                $pageNumber++;
            }

            echo "<br/><br/>";
        }
?>