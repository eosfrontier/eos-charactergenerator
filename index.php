<?php
// globals
require_once '_includes/includes.php';
include_once('./header.php');

//if there is no active session, start one
if (!isset($_SESSION)) {
  session_start();
}

if (isset($_POST['newchar']) && $_POST['newchar'] != "") {
  require './operations/POST/newChar.php';
}
?>

<div class="wsleft cell"></div>

<div class="main cell">
  <div class="content">
    <br />
    <?php
	if (!isset($APP["allowed_groups"]) || (isset($APP["allowed_groups"]) && !empty(array_intersect($jgroups, $APP["allowed_groups"])))) {
	  $sheetArr = getCharacterSheets();
	} else {
	  echo 'Access denied. Please contact a member of the organization to be granted CharGen access by filling out the <a href="/access-request-form">access request form</a>.';
	  exit();
	}

    $printresult = "";
    //Trigger the new character screen
    if (isset($_GET['newChar'])) {
      require './operations/GET/newChar.php';
    }
    //Activate a character
    else if (isset($_GET['activate']) && $_GET['activate'] != "") {
      require './operations/GET/activate.php';
    }
    //View a character
    else if (isset($_GET['viewChar']) && $_GET['viewChar'] != "") {
      require './operations/GET/viewChar.php';
    }
    //List your characters
    else {
      require './operations/GET/listChars.php';
    }

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
include_once('./footer.php');
