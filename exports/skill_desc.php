<?php
require_once "../_includes/config.php";
require_once "../_includes/functions.global.php";
require_once './current-players.php';


?>
<style>
    html {
      margin: 0;
      padding: 0;
      height: 100%;
      width: 100%;
      font-size: 10px;
      background: #FFF;
    }

    body {
      font-family: arial;
      font-size: 12px;
      height: 297mm;
      width: 210mm;
      margin-left: auto;
      margin-right: auto;
      margin-top: 0;
      margin-bottom: 0;
      background: #FFF;
      background-image: url('../img/32033.png');
      background-position: top right;
      background-position: 95% 60px;
      background-repeat: no-repeat;
    }

    button {
      cursor: pointer;
      padding: 8px;
      font-size: 32px;
    }
  </style>

  <?php
  echo '<button onclick="history.go(-1);">Back </button>';
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

echo "<h1>Name:" . $VALUES['label'] . "</h1>";
echo "<h2>" . $VALUES['parent'] ." level:" . $VALUES['level'] . "</h2>";
echo "<body>" . nl2br($VALUES['description']) . "</body>";
