<?php
include_once($_SERVER["DOCUMENT_ROOT"] . '/eoschargen/db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
require './buildtable.php';
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <title>Sleepytime</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Orbitron:400,500,700,900" rel="stylesheet">
</head>

<body>
    <div>
        <?php
        $buildings = GetBuildings($sleepers);
        ?>
        <table>
            <?php
            foreach ($buildings as $building){
                $building_sleepers = BreakdownByBuilding($sleepers, $building);
                $b_count = count($building_sleepers);
                // echo "<td>";
                echo "<H2>" . str_replace('tweede gebouw', 'Zonnedauw', $building) . " - ($b_count)</H2>";
                $rooms = GetRooms($sleepers,$building);
                foreach ($rooms as $room){
                    $room_sleepers = BreakdownByRoom($sleepers,$building,$room);
                    $count = count($room_sleepers);
                    echo "<H3>Room $room - ($count) </H3>";
                arrayToTable($room_sleepers);
                }
                echo "<br>";
                // echo "</td>";
            }
            ?>
        </table>    
    </div>
</body>

</html>