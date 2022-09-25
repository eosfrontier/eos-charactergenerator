<?php

      if (isset($_POST['updateEventsPlayed']) && $_POST['updateEventsPlayed']) {
        $xINPUT = EMS_echo($_POST['updateEventsPlayed']['value']);
        $xINPUT = (int)$xINPUT;

        if ($xINPUT > 50 || $xINPUT < 0) {
          $xINPUT = 0;
        }

        $sql = "UPDATE `ecc_characters`
            SET `aantal_events` = '" . mysqli_real_escape_string($UPLINK, (int)$xINPUT) . "'
            WHERE `characterID` = '" . mysqli_real_escape_string($UPLINK, (int)$_GET['viewChar']) . "'
            AND `accountID` = '" . mysqli_real_escape_string($UPLINK, $jid) . "'";
        $res = $UPLINK->query($sql);

        header("location: " . $APP['header'] . "/index.php?viewChar=" . $_GET['viewChar'] . "&u=1");
        exit();

        echo $sql;
      }


      if (isset($_GET['u']) && $_GET['u'] == 1) {
        $printresult .= "<p class=\"dialog\"><i class=\"fas fa-check green\"></i>&nbsp;Updated succesfully.</p>";
      }

      // check if characters is valid
      if (is_array($sheetArr['characters'])) {
        if (count($sheetArr['characters']) > 0) {

          if (isset($sheetArr["characters"][$_GET['viewChar']]['accountID']) && EMS_echo($sheetArr["characters"][$_GET['viewChar']]['accountID']) == $jid) {

            // put the character into an easier to access variable for laziness.
            $character = $sheetArr["characters"][$_GET['viewChar']];

            if (EMS_echo($character['character_name']) != "") {
              $printresult .= "<h1>{$character['character_name']} - {$character['faction']}</h1>";
            } else {
              $printresult .= "<h1>[character name] - {$character['faction']}</h1>";
            }

            if (isset($_GET['editInfo']) && $_GET['editInfo'] == true) {
              $printresult .= "<a href='./handler/fileupload/index.php?viewChar={$character['characterID']}'>"
                . "<img class=\"passphoto popout\" alt=\"Upload File \" onerror=\"this.src='./img/default.png';\""
                . "  src=\"{$APP['header']}/img/passphoto/{$character['characterID']}.jpg?" . time() . "\"/></a>"
                . "<style>.grid .main .content .row {width: auto;}</style>"
                . "<div class=\"row\">"
                . "<a href=\"{$APP['header']}/index.php?viewChar={$character['characterID']}\"><button><i class=\"fas fa-arrow-left\"></i>&nbsp;Back to character options</button></a>"
                . "</div>"
                . "<hr/>";

              // edit char set? Validate.
              if (isset($_POST['editchar']) && $_POST['editchar'] != "") {

                updateCharacterInfo($_POST['editchar'], $character['characterID']);
                header("location: {$APP['header']}/index.php?viewChar={$character['characterID']}&editInfo=true&u=1");
                exit();
              }

              $printresult .= "<div class=\"row flexcolumn\">";

              $printresult .=
                "<p>&nbsp;</p>"
                . "<p>This is where you edit your character's basic information.</p>"
                . "<p>&nbsp;</p>"
                . "<p>Don't worry about knowing or entering all the details right now, you can revisit this page any time.</p>"
                . "<p>&nbsp;</p>"
                . "<form action=\"{$APP['header']}/index.php?viewChar=" . $character['characterID'] . "&editInfo=true\" method=\"post\">";

              $printresult .=
                "<div class=\"formitem\">"
                . "<h3><i class=\"fas fa-user\"></i>&nbsp;Character Name</h3>"
                . "<input autocomplete=\"off\" type=\"text\" placeholder=\"Character Name\" maxlength=\"99\" name=\"editchar[character_name]\" value=\"" . EMS_echo($character['character_name']) . "\"></input>"
                . "</div>";

              $printresult .=
                "<div class=\"formitem\">"
                . "<h3><i class=\"fas fa-users\"></i>&nbsp;Faction</h3>"
                . "<p class=\"text-muted\">" . ucfirst(EMS_echo($character['faction'])) . "</p>"
                . "</div>"

                . "<div class=\"formitem\">"
                . "<h3><i class=\"fas fa-key\"></i>&nbsp;ICC Number:</h3>"
                . "<p class=\"text-muted\">" . EMS_echo($character['ICC_number']) . "</p>"
                . "</div>"

                . "<div class=\"formitem\">"
                . "<h3><i class=\"far fa-calendar-alt\"></i>&nbsp;Birth date</h3>"
                . "<input autocomplete=\"off\" type=\"text\" placeholder=\"..( current IC year: 240NT )\" maxlength=\"24\" name=\"editchar[ic_birthday]\" value=\"" . EMS_echo($character['ic_birthday']) . "\"></input>"
                . "</div>";

              $printresult .=
                "<div class=\"formitem\">"
                . "<h3><i class=\"fas fa-globe\"></i>&nbsp;Birth planet</h3>"
                . "<input autocomplete=\"off\" type=\"text\" placeholder=\"...\" maxlength=\"99\" name=\"editchar[birthplanet]\" value=\"" . EMS_echo($character['birthplanet']) . "\"></input>"
                . "</div>"

                . "<div class=\"formitem\">"
                . "<h3><i class=\"fas fa-globe\"></i>&nbsp;Current/home planet</h3>"
                . "<input autocomplete=\"off\" type=\"text\" placeholder=\"...\" maxlength=\"99\" name=\"editchar[homeplanet]\" value=\"" . EMS_echo($character['homeplanet']) . "\"></input>"
                . "</div>";

              $printresult .=
                "<div class=\"formitem\">"
                . "<p>&nbsp;</p>"
                . "<input type=\"submit\" class=\"button green\" value=\"Save changes\"></input>"
                . "</div>"
                . "</div>"
                . "</form>";
            } else {

              $printresult .= "<div class=\"row\">"
                . "<a href=\"{$APP['header']}/index.php\"><button><i class=\"fas fa-arrow-left\"></i>&nbsp;Back</button></a>"
                . "</div>"
                . "<hr/>";

              // default: character menu.
              $printresult .= "<div class=\"row\">";

              $printresult .= "<div class=\"box33\">"
                . "<a href=\"{$APP['header']}/index.php?viewChar={$character['characterID']}&editInfo=true\">"
                . "<button type=\"button\" class=\"blue bar\" name=\"button\"><i class=\"far fa-id-card\"></i>&nbsp;Edit basic info</button>"
                . "</a>"
                . "</div>";

              $printresult .= "<div class=\"box33\">"
                . "<a href=\"{$APP['header']}/stats/skillsV2.php?viewChar={$character['characterID']}\">"
                . "<button type=\"button\" class=\"blue bar\" name=\"button\"><i class=\"fas fa-book\"></i>&nbsp;Character Skills</button>"
                . "</a>"
                . "</div>";

              $printresult .= "<div class=\"box33\">"
                . "<a class=\"\" href=\"{$APP['header']}/stats/implantsV2.php?viewChar={$_GET['viewChar']}\">"
                . "<button type=\"button\" class=\"button bar blue\" name=\"button\"><i class=\"fas fa-microchip\"></i>&nbsp;Implants/Symbionts</button>"
                . "</a>"
                . "</div>";

              // end first row, start second row
              $printresult .= "</div><div class=\"row\">";


              $printresult .= "<div class=\"box33\">"
                . "<a onclick=\"SH_editPlayedForm({$_GET['viewChar']})\">"
                . "<button type=\"button\" class=\"button blue no-bg bar\" name=\"button\"><i class=\"fas fa-sort-numeric-up\"></i>&nbsp;Events Played</button>"
                . "</a>"
                . "</div>";

              $printresult .= "<div class=\"box33\">"
                . "<a href=\"https://www.eosfrontier.space/bgcheck\" target=\"_blank\">"
                . "<button type=\"button\" class=\"blue no-bg bar\" name=\"button\"><i class=\"fas fa-list\"></i>&nbsp;Background-check details</button>"
                . "</a>"
                . "</div>";


              $printresult .= "<div class=\"box33\">";

              // if($character['status'] != "deceased") {
              //
              //   if($character['sheet_status']['code'] == 0) {
              //
              //     $printresult .= "<a class=\"\" href=\"".$APP['header']."/index.php?delChar=".$character['characterID']."\">"
              //         ."<button type=\"button\" class=\"tomato bar\" name=\"button\"><i class=\"fas fa-user-times\"></i>&nbsp;Mark for delete</button>"
              //       ."</a>";
              //
              //   } else if ($character['sheet_status']['code'] == 90) {
              //
              //     $printresult .= "<button type=\"button\" class=\"disabled bar\" name=\"button\"><i class=\"fas fa-times\"></i>&nbsp;Marked for delete</button>";
              //
              //   } else {
              //
              //     $printresult .= "<button type=\"button\" class=\"disabled bar\" name=\"button\"><i class=\"fas fa-user-times\"></i>&nbsp(Delete disabled)</button>";
              //
              //   }
              //
              // }

              $printresult .= "</div>"; //sluit box33


              $printresult .= "</div>";
              // end second row

            }
          } else {
            // error : account ID  doesn't match the logged in account ID !!
            $printresult .= "ERROR: NO MATCH.";
          }
        } else {
          header("location: " . $APP['header'] . "/index.php");
          exit();
        }
      } else {
        header("location: " . $APP['header'] . "/index.php");
        exit();
      }
