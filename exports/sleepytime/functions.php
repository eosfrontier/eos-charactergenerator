<?php

function arrayToTable($array)
{
    echo '<table border="2">';
    echo "<thead><tr>";

    foreach (array_keys($array[0]) as $header) {
        echo "<th>$header</th>";
    }
    echo "</tr></thead>";
    echo "<tbody>";
    foreach ($array as $items) {
        echo "<tr>";
        foreach ($items as $key => $value) {
            echo "<td>$value</td>";
        }
    }
    echo "</tbody></table>";
}
;
function buildSleeperRow($result)
{
    $array_results = array();
    while ($row = mysqli_fetch_array($result)) {
        $building = $row['building'];
        if ($building == "Bastion") {
            $room = $row['bastion_room'];
        } elseif ($building == "tweede gebouw") {
            $room = $row['tweede_room'];
        }
        array_push($array_results, array("Name" => $row['name'], "Building" => $building, "Room" => $room, "Food Location" => $row['food_loc']));
    }
    return $array_results;
}

function BreakdownByBuilding($input_array, $building)
{
    $array = array_filter($input_array, function ($item) use ($building) {
        if (stripos($item['Building'], $building) !== false) {
            return true;
        }
        return false;
    });
    $result = array();
    foreach ($array as $item) {
        array_push($result, array('Name' => $item['Name'], 'Room' => $item['Room']));
    }
    return $result;
}

function GetBuildings($sleepers)
{
    $new_arr = array();
    foreach ($sleepers as $key => $val) {
        array_push($new_arr, $val['Building']);
    }
    return array_unique($new_arr);
}

function GetRooms($input_array, $building)
{
    $array = array_filter($input_array, function ($item) use ($building) {
        if (stripos($item['Building'], $building) !== false) {
            return true;
        }
        return false;
    });
    foreach ($array as $key => $val) {
        $new_arr[] = $val['Room'];
    }
    return array_unique($new_arr);
}

function BreakdownByRoom($input_array, $building, $room)
{
    $array_building = array_filter($input_array, function ($item) use ($building) {
        if (stripos($item['Building'], $building) !== false) {
            return true;
        }
        return false;
    });
    $array = array_filter($array_building, function ($item) use ($room) {
        if (($item['Room']) == $room) {
            return true;
        }
        return false;
    });
    $result = array();
    foreach ($array as $item) {
        array_push($result, array('Name' => $item['Name'], 'Room' => $item['Room']));
    }
    return $result;
}

function JoshArrayDump($array)
{
    print '<pre>';
    print_r($array);
    print '</pre>';
}