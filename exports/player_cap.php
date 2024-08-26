<?php
// globals
// config variable.
$APP = array();

$APP["loginpage"] = "/component/users/?view=login";

include_once('../db.php');
include_once("../_includes/functions.global.php");
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
  <h1> Active <?php echo ucwords($_GET['faction']);?> Players since <?php echo player_cap_count_from();?></h1>
  <table>
      <tr>
        <th width="10%">Character Name</th>
      </tr>
      <?php 
      $active_players = get_active_players($_GET['faction']);
      while ($row = mysqli_fetch_array($active_players)) {
      echo "<tr>";
      echo '<td>' . ucwords($row['character_name']) . "</td>";
        echo '</tr>';
      }?>
    </table>
  </div>
</body>

</html>
