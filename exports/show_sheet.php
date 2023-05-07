<?php
        include_once $APP["root"] . "/_includes/functions.sheet.php";
        include_once $APP["root"] . "/_includes/functions.skills.php";

        $sql = "SELECT characterID, faction, born_faction, accountID, aantal_events, character_name
        FROM `ecc_characters` 
        WHERE accountID = $jid AND sheet_status = 'active'
        LIMIT 1";
        $res = $UPLINK->query($sql);

        $sql2 = "SELECT title FROM jml_eb_events where id = $EVENTID;";
        $res2 = $UPLINK->query($sql2);
        $row2 = mysqli_fetch_array($res2);

        if ($res && mysqli_num_rows($res) == 1) {

            $row = mysqli_fetch_assoc($res);

            $jid = $row['accountID'];

            if (  isset($row['born_faction']) && ($row['born_faction'] != '') ) {
              $faction = $row['born_faction'];
              $displayFaction = $row['faction'] . "(Orig: " . ucFirst($row['born_faction']) .")";
            }else{
              $faction = $row['faction'];
              $displayFaction = $faction;
            }
            $skillArr       = getCharacterSkills($row['characterID']);
            $expUsed        = calcUsedExp(EMS_echo($skillArr), $faction);
            $expTotal       = calcTotalExp($row['aantal_events']);
            $augmentations  = filterSkillAugs(getImplants($row['characterID']));
            //MySQL Query to Check for Bonus research token skill
            $sql3 = "SELECT charID FROM ecc_char_skills WHERE (skill_id = 31305 AND charID = " . $row['characterID'] . ");";
            $res3 = $UPLINK->query($sql3);
            $row3 = mysqli_fetch_array($res3);

            echo "<div style='padding: 15px 45px; 0 15px;'>";
            echo "<font size='6'><strong>" . ucfirst($row['character_name']) . "</strong></font></br>";
            echo "<font size='5'><strong>" . "Experience points spent: $expUsed / $expTotal "
            . "<span style=\"color: #777; float: right;\">" . ucfirst($displayFaction) . "</span>"
            . "</strong></font></br>";

            echo "<hr/>";

            // SKILLS
            echo "<div style=\"width: 65%; float: left;\">";
            echo "<style> body { font-size: 16px } </style>";
            echo "<font size='4'><strong>Your skills</strong></font></br>";

            // first, create a minimized skill sheet

            $parentSkills = [];

            $kSQL = "SELECT primaryskill_id, name FROM `ecc_skills_groups`";
            $kRES = $UPLINK->query($kSQL);
            while ($kROW = mysqli_fetch_assoc($kRES)) {
                $parentSkills[$kROW['primaryskill_id']] = $kROW['name'];
            }
            // and Third: It's time to print those skills!

            echo "<table style=\"border: 0; width: 90%;\">";
            echo "<tr style=\"background-color: #CCC;\">"
            . "<th colspan=\"3\">Skill</th>"
            . "<th style=\"width: 65px; text-align: center;\">Level</th>"
            .  "</tr>";

            $printableSkills = [];

            foreach ($skillArr as $SKILL => $VALUES) {
                $printableSkills[$VALUES['parent']] = $VALUES;
            }

            foreach ($skillArr as $SKILL => $VALUES) {

                if (isset($VALUES['label']) && $VALUES['label'] !== '') {
                    echo "<tr>"
                    . "<td style=\"color: #888; font-size: 8px;\">" . $parentSkills[$VALUES['parent']] . "</td>"
                    . "<td colspan=\"2\"><a href='./skill_desc.php?id=" . $VALUES['id'] . "'>" . $VALUES['label'] . "</a>" . ($VALUES['level'] > 5 ? "*" : "") . "</td>"
                    . "<td style=\"text-align: center; padding: 2px 5px; width: 65px;\">" . $VALUES['level'] . "</td>"
                    . "</tr>";
                }
            }

            echo "</table>";
            echo "<p style=\"font-size: 13px;\"><i>* specialty skills</i></p>";



            echo "</div>";

            echo "<div style=\"width: 30%; float: left;\">";

            if ($augmentations != "") {
              echo "<font size='4'><strong>Augmentations</strong></font></br>";
              echo "<table style=\"border: 0; width: 90%;\">";
              echo "<tr style=\"background-color: #CCC;\">"
              . "<th>Type</th>"
              . "<th>Skill</th>"
              . "<th>Level</th>"
              .  "</tr>";
                foreach ($augmentations as $aug) {
                    echo "<tr>"
                    . "<td>" . ($aug['type'] == 'cybernetic' ? 'Bionic' : 'Symbiont') . "</td>" 
                    . "<td>". $aug['name'] . "</td>"
                    ."<td>" . $aug['level'] . "</td>"
                    . "</tr>";
                }
              echo "</table>";
            }

            echo "</div>";

            // AUGMENTATIONS
        }

        echo "</div>";
   ?>