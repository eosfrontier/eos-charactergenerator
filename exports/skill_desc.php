<?php
require_once "../_includes/config.php";
require_once "../_includes/functions.global.php";
require_once './current-players.php';
?>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      <?php
      include '../_includes/css/style.css';
      ?>
          button {
          font-size: 4vw;
            }
          @media  screen and (min-width: 601px) {
          button {
          font-size: 1.5vw;
            }
          }
        img {
          Padding: 50px,0px //padding positions
        }
      </style>
    </head>

  <?php
  echo "<div class='body'><body>" ;
  echo '<button onclick="history.go(-1);">Back </button> &nbsp; &nbsp; &nbsp; <img src="../img/outpost-icc-pm.png"  width="200" >';
  $skillid = $_GET["id"];

$stmt = db::$conn->prepare(
    "SELECT s.label, s.level, s.description, g.name as parent from ecc_skills_allskills s 
    LEFT JOIN ecc_skills_groups g on (g.primaryskill_id = s.parent)                                                                                                                  
  WHERE skill_id = $skillid;"
  );
  $res  = $stmt->execute();
  $res  = $stmt->fetchAll( PDO::FETCH_ASSOC );
foreach ($res as $SKILL => $VALUES) {
    $printableSkills[$VALUES['parent']] = $VALUES;
}
echo "<h1 font-size='6vw'>Name: " . $VALUES['label'] . "</h1>";
echo "<h2>" . $VALUES['parent'] ." level:" . $VALUES['level'] . "</h2>";
echo nl2br($VALUES['description']) . "</body></div>";
echo "</html>";