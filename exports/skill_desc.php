<?php
require_once "../_includes/config.php";
require_once "../_includes/functions.global.php";
require_once './current-players.php';
?>
<style>
  <?php 
include '../_includes/css/style.css';
?>
/* html {
      margin: 0;
      padding: 0;
      height: 100%;
      width: 100%;
      font-size: 10px;
      background: #FFF;
    }
    h1 {
    display: block;
    margin-block-start: 0.67em;
    margin-block-end: 0.67em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
    }
    h2 {
        display: block;
        margin-block-start: 0.67em;
        margin-block-end: 0.67em;
        margin-inline-start: 0px;
        margin-inline-end: 0px;
        font-weight: bold;
    }

    @media only screen and (max-width: 400px) {
      h1 {
        font-size: 5rem;
      }
      h2 {
        font-size: 4rem;
      }
      div.body {
        font-size: 3rem;
      }
      button {
        font-size: 4vw;
      }
    }
    @media only screen and (min-width: 401px) {
      h1 {
        font-size: 3rem;
      }
      h2 {
        font-size: 2.5rem;
      }
      div.body {
        font-size: 2rem;
      }
      button {
        font-size: 2vw;
      }
    }

    div.body {
      font-family: arial;
      /* font-size: 2vw; */
      height: 297mm;
      width: 100%;
      margin-left: auto;
      margin-right: auto;
      margin-top: 0;
      margin-bottom: 0;
      background: #FFF;
    }

    button {
      /* cursor: pointer;
      padding: 8px; */
      font-size: 5vw;
    }
    img {
      Padding: 50px,0px //padding positions
    }
  </style>

  <?php
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
echo "<div class='body'><body>" . nl2br($VALUES['description']) . "</body></div>";
