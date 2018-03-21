<?php
// show errorpage function
function showErrorPage($message = '451') {
  echo "<html style=\"background:#222; color: #EEE;\"><h1 style=\"color: #EEE;\">".$message."</h1></html>";
  exit();
}


// builds the left menu
function generateMenu($param = 'Home') {

  global $APP;

  $printresult = "";

  // $class = (strtolower($param) == 'home') ? 'active' : '';
  //   $printresult .= "<a href=\"".$APP['header']."/\" class=\"menuitem $class\"><i class=\"fas fa-home\"></i><span>&nbsp;Home</span></a>";

  $class = (strtolower($param) == 'characters') ? 'active' : '';
    $printresult .= "<a href=\"".$APP['header']."/index.php\" class=\"menuitem $class\"><i class=\"fas fa-user\"></i><span>&nbsp;Character(s)</span></a>";

  // $class = (strtolower($param) == 'myaccount') ? 'active' : '';
    // $printresult .= "<a href=\"".$APP['header']."/myaccount.php\" class=\"menuitem disabled $class\"><i class=\"fas fa-cog\"></i><span>&nbsp;My account</span></a>";

  $class = (strtolower($param) == 'about') ? 'active' : '';
    $printresult .= "<a href=\"".$APP['header']."/about.php\" class=\"menuitem $class\"><i class=\"fas fa-info-circle\"></i><span>&nbsp;About</span></a>";

  // $class = 'disabled';
    $printresult .= "<a href=\"https://www.eosfrontier.space\" class=\"menuitem\"><i class=\"fas fa-arrow-left\"></i><span>&nbsp;Back to site</span></a>";

  return $printresult;

}

// large update function to make updating a character's info easier.
function updateCharacterInfo($params = array(), $charID = 0) {
  global $jid, $UPLINK;

  // is charID set?
  if(isset($charID) && (int)$charID !== 0) {

    // check if charID belongs to the active account.
    $sql = "SELECT characterID, accountID FROM `ecc_characters` WHERE characterID = '".mysqli_real_escape_string($UPLINK,(int)$charID)."' AND accountID = '".mysqli_real_escape_string($UPLINK,(int)$jid)."' LIMIT 1";
    $res = $UPLINK->query($sql);

    if($res && mysqli_num_rows($res) == 1) {

      check4dead($charID);

      if(is_array($params) && count($params) > 0) {

        if(isset($params['character_name'])) {
          $input = EMS_echo($params['character_name']);
          $input = sanitize_spaces($input);
          huizingfilter($input);

          $sql = "UPDATE `ecc_characters`
            SET `character_name` = '".mysqli_real_escape_string($UPLINK,$input)."'
            WHERE characterID = '".mysqli_real_escape_string($UPLINK,(int)$charID)."'
            AND accountID = '".mysqli_real_escape_string($UPLINK,(int)$jid)."'
            LIMIT 1";
          $update = $UPLINK->query($sql) or trigger_error(mysqli_error($UPLINK));
        }
        if(isset($params['ic_birthday'])) {
          $input = EMS_echo($params['ic_birthday']);
          $input = sanitize_spaces($input);
          huizingfilter($input);

          $sql = "UPDATE `ecc_characters`
            SET `ic_birthday` = '".mysqli_real_escape_string($UPLINK,$input)."'
            WHERE characterID = '".mysqli_real_escape_string($UPLINK,(int)$charID)."'
            AND accountID = '".mysqli_real_escape_string($UPLINK,(int)$jid)."'
            LIMIT 1";
          $update = $UPLINK->query($sql) or trigger_error(mysqli_error($UPLINK));
        }
        if(isset($params['birthplanet'])) {
          $input = EMS_echo($params['birthplanet']);
          $input = sanitize_spaces($input);
          huizingfilter($input);

          $sql = "UPDATE `ecc_characters`
            SET `birthplanet` = '".mysqli_real_escape_string($UPLINK,$input)."'
            WHERE characterID = '".mysqli_real_escape_string($UPLINK,(int)$charID)."'
            AND accountID = '".mysqli_real_escape_string($UPLINK,(int)$jid)."'
            LIMIT 1";
          $update = $UPLINK->query($sql) or trigger_error(mysqli_error($UPLINK));
        }
        if(isset($params['homeplanet'])) {
          $input = EMS_echo($params['homeplanet']);
          $input = sanitize_spaces($input);
          huizingfilter($input);

          $sql = "UPDATE `ecc_characters`
            SET `homeplanet` = '".mysqli_real_escape_string($UPLINK,$input)."'
            WHERE characterID = '".mysqli_real_escape_string($UPLINK,(int)$charID)."'
            AND accountID = '".mysqli_real_escape_string($UPLINK,(int)$jid)."'
            LIMIT 1";
          $update = $UPLINK->query($sql) or trigger_error(mysqli_error($UPLINK));
        }

      } else {
        showErrorPage("Error code 0734");
        return false;
      }
    } else {
      showErrorPage("Error code 0731");
      return false;
    }


  } else {
    return false;
  }

}


// get character sheets
function getCharacterSheets() {
  global $jid, $UPLINK;

  $return = array();
  $return['characters'] = array();


  if(isset($UPLINK) && isset($jid) && $jid != "") {

    $sql = "SELECT * FROM ecc_characters WHERE accountID = '".(int)$jid."'";
    $res = $UPLINK->query($sql);

    if($res) {
      if(mysqli_num_rows($res) > 0) {

        while($row = mysqli_fetch_assoc($res)){

          $return['characters'][$row['characterID']] = array();

          foreach($row AS $KEY => $VALUE) {

            // $return['characters'][$row['characterID']][$KEY] = silvesterFilter(EMS_echo($VALUE));
            $return['characters'][$row['characterID']][$KEY] = EMS_echo($VALUE);

          }//foreach

          if(count($return['characters']) > 0) {

            $return['characters'][$row['characterID']]['sheets'] = array();

            foreach($row AS $KEY => $VALUE) {

              $xSQL = "SELECT * FROM ecc_char_sheet WHERE characterID = '".(int)$return['characters'][$row['characterID']]['characterID']."' ORDER BY versionNumber DESC";
              $xRES = $UPLINK->query($xSQL);

              if(mysqli_num_rows($xRES) > 0) {
                while($xROW = mysqli_fetch_assoc($xRES)){

                  foreach($xROW AS $xKEY => $xVALUE) {
                    $return['characters'][$row['characterID']]['sheets'][$xROW['charSheetID']][$xKEY] = EMS_echo($xVALUE);
                  }
                }

                $xRES->free();
              }
            }//foreach
          }

        }

        $res->free();

        $return['status'] = "ok";

      } else {

        $return['status'] = "noChar";
      }
    } else {

      $return['status'] = "noChar";
    }

  } else {

    $return['status'] = "noDB";
  }

  return $return;
}

// Removes Emojis from user input. Yup. This is a thing. (specificly: unicode. 😀🤬✌🏻✌🏻💎 )
// Named after Silvester, who hilariously discovered you could make full Emoji based characters.
function silvesterFilter($input = null) {
  // $output = preg_replace("/[^[:alnum:][:space:]]/u", '', $input);
  $output = $input; //temporarily disabled
  return $output;
}

// abort an operation if a character sheet is NOT edittable.
function checkSheetStatus($sheetID) {
  global $UPLINK;

  $sql = "SELECT status FROM `ecc_char_sheet` WHERE charSheetID = '".mysqli_real_escape_string($UPLINK,(int)$sheetID)."' LIMIT 1";
  $res = $UPLINK->query($sql);

  if(mysqli_num_rows($res) == 1) {
    $row = mysqli_fetch_assoc($res);
    if($row['status'] == 'ontwerp'){
      return true;
    } else {
      echo "<p>Character sheet cannot be editted, as it is not in design mode.</p>";
      exit();
    }
  } else {
    echo "<p>Character sheet cannot be editted, as it is not in design mode.</p>";
    exit();
  }

}



function generateCode($codelength = 10, $type = 'hex') {

  switch($type) {
    case 'hex': default:
      $allowed = '0123456789ABCDEF';
      break;
    case 'number':
      $allowed = '0123456789';
      break;
  }

  // legacy: faction STARTING numbers. Here in comments for later use.
  /* Aquila: 7 Pendzal: 9 Ekanesh: 8 Dugo: 3 Sona: 5 */
  $output = '';

  for ($i = 0; $i < $codelength; $i++) {
    $output .= substr($allowed, mt_rand(0, strlen($allowed)-1), 1);
  }
  return $output;
}

function check4dead($charID) {
  global $UPLINK;

  $sql = "SELECT status FROM `ecc_characters` WHERE characterID = '".mysqli_real_escape_string($UPLINK,(int)$charID)."' AND status = 'deceased'";
  $res = $UPLINK->query($sql);

  if(mysqli_num_rows($res) > 0) {
    echo "<p class=\"dialog\">We're very sorry for your loss, but it's time to let go. This character is dead.<br/><br/><i class=\"far fa-lightbulb\"></i>&nbsp;Try to find some closure by designing a new character.</p>";
    exit();
  }
}

// Wash away all unnecessary spaces, by brutishly looping for all them.
function sanitize_spaces($input = null) {

  $spaces = substr_count($input, " ");
  if($spaces > 0) {
    for($i = 0; $i < $spaces; $i++) {
      $input = str_replace("  "," ",$input);
    }
  }

  return $input;
}

// spam / escape filter, named after a friend of mine who taught me the importance of filtering user input.
function huizingfilter($input = null) {

  $triggers    = array('http','tps:/','tp:/',"src=","src =",'<','>','><','.js','@S','@s','GOTO ','DBCC ');
  $error       = false;

  foreach ($triggers as $trigger) { // loops through the huizing-huizingtriggertriggertrigger
    if (stripos($input,$trigger) !== false) {
      $error = true;
    }
  }

  if($error == true) {
    echo "<h2>Operations interrupted: Some of your input was flagged as malicious.</h2>";
    exit();
  } else {
    return "clear";
  }
}

/* EMS_echo : just a function to echo; However, if the variable you're trying to echo is undefined, it will still return "". */
function EMS_echo(&$var, $else = '') {
  return isset($var) && $var ? $var : $else;
}
