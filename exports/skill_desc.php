<?php
require_once "../_includes/config.php";
require_once "../_includes/functions.global.php";
require_once './current-players.php';
echo '<button onclick="history.go(-1);">Back </button>';
$skillid = $_GET["id"];

$stmt = db::$conn->prepare(
    "SELECT * from ecc_skills_allskills s 
    LEFT JOIN ecc_skills_groups g on (g.primaryskill_id = s.skill_id)                                                                                                                  
  WHERE skill_id = $skillid;"
  );
  $res  = $stmt->execute();
  $res  = $stmt->fetchAll( PDO::FETCH_ASSOC );
foreach ($res as $SKILL => $VALUES) {
    $printableSkills[$VALUES['parent']] = $VALUES;
}

echo "<h1>Name:" . $VALUES['label'] . "</h1>";
echo "<h2>Level:" . $VALUES['level'] . "</h2>";
echo "<body>" . $VALUES['description'] . "</body><br><br>";
// print_r($res);