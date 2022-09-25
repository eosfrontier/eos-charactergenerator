<?php
// globals
include_once($_SERVER["DOCUMENT_ROOT"] . "/eoschargen/_includes/config.php");
include_once($APP["root"] . "/_includes/functions.global.php");
include_once($APP["root"] . "/header.php");

//if there is no active session, start one
if (!isset($_SESSION)) {
  session_start();
}

if (isset($_POST['newchar']) && $_POST['newchar'] != "") {

  $_POST['newchar'] = strTolower($_POST['newchar']);

  if (
    $_POST['newchar'] == "aquila"
    || $_POST['newchar'] == "dugo"
    || $_POST['newchar'] == "ekanesh"
    || $_POST['newchar'] == "pendzal"
    || $_POST['newchar'] == "sona"
  ) {

    $psyCharacter = ($_POST['newchar'] == 'ekanesh' ? 'true' : 'false');

    $ICCID = generateICCID($_POST['newchar']);

    $sql = "INSERT INTO `ecc_characters` (`accountID`, `faction`, `status`, `psychic`, `ICC_number`)
        VALUES (
          '" . (int)$jid . "',
          '" . mysqli_real_escape_string($UPLINK, $_POST['newchar']) . "',
          'in design',
          '" . mysqli_real_escape_string($UPLINK, $psyCharacter) . "',
          '" . mysqli_real_escape_string($UPLINK, $ICCID) . "'
        );";
    $res = $UPLINK->query($sql) or trigger_error(mysqli_error($UPLINK));

    // after creating a character, check if this is the only character bound to this account.
    $sql2 = "SELECT `characterID` FROM `ecc_characters` WHERE `accountID` = $jid";
    $res2 = $UPLINK->query($sql2) or trigger_error(mysqli_error($UPLINK));
    // redirect to SET_ACTIVE if 1 character exists.
    if (mysqli_num_rows($res2) == 1) {
      $character = mysqli_fetch_assoc($res2);
      header("location: {$APP['header']}/index.php?activate={$character['characterID']}&firstCharacter=true");
      exit();
    }

    header("location: {$APP['header']}/index.php");
    exit();
  }
}
?>
<div class="wsleft cell"></div>

<div class="main cell">
  <div class="content">

    <br />
    <?php

    $printresult = "";

    if (isset($_GET['newChar'])) {require './operations/newchar.php';}
    else if (isset($_GET['activate']) && $_GET['activate'] != "") {require './operations/activate.php';}
    else if (isset($_GET['viewChar']) && $_GET['viewChar'] != "") {require './operations/viewChar.php';}
    else {require './operations/listChars.php';}

    echo $printresult;
    unset($printresult);

    ?>
    <div class="row">
      <div id="customForm"></div>
    </div>

  </div>

</div>

<div class="wsright cell"></div>

<?php
include_once($APP["root"] . "/footer.php");
