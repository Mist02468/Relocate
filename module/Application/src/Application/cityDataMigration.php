<?php

$columns = array(
            'cityName'            => 0,
            'stateName'           => 1,
            'population'          => 2,
            'landAreaSquareMiles' => 3,
            'coordinatesNorth'    => 4,
            'coordinatesWest'     => 5,
            'walkScore'           => 6,
            'transitScore'        => 7,
            'avgTemp'             => 8
           );

$secrets  = parse_ini_file('config/autoload/localSecrets.ini');
$dbHandle = new PDO('mysql:host=' . $secrets['Host'] . ';port=' . $secrets['Port'] . ';dbname=' . $secrets['Database'], $secrets['Username'], $secrets['Password']);
$csvFileHandle = fopen("dataAboutCities.csv", "r");

if ($csvFileHandle !== False) {
    while (($csvRow = fgetcsv($csvFileHandle)) !== False) {

        $stmt = $dbHandle->prepare('SELECT id FROM state WHERE name = :name');
        $stmt->bindParam(':name', $csvRow[$columns['stateName']], PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll();

        if (count($results) === 0) {
            //this state has not been added to the database yet, do so and note the id
            $stmt = $dbHandle->prepare('INSERT INTO state (name) VALUES (:name)');
            $stmt->bindParam(':name', $csvRow[$columns['stateName']], PDO::PARAM_STR);
            $stmt->execute();
            $stateID = (int) $dbHandle->lastInsertId();
        } elseif (count($results) === 1) {
            //this state is in the database, note the id
            $stateID = (int) $results[0]['id'];
        } else {
            //this state is in the database more than once, this should not occur
            throw new Exception('Found more than one row in the states table with the name ' . $csvRow[$columns['stateName']]);
        }

        $stmt = $dbHandle->prepare('INSERT INTO city (name, state_id, walkScore, transitScore, avgTemp, population, landAreaSquareMiles, coordinatesNorth, coordinatesWest) VALUES (:name, :state_id, :walkScore, :transitScore, :avgTemp, :population, :landAreaSquareMiles, :coordinatesNorth, :coordinatesWest)');
        $stmt->bindParam(':name', $csvRow[$columns['cityName']], PDO::PARAM_STR);
        $stmt->bindParam(':state_id', $stateID, PDO::PARAM_INT);
        $stmt->bindParam(':walkScore', $csvRow[$columns['walkScore']], PDO::PARAM_INT);
        $stmt->bindParam(':transitScore', $csvRow[$columns['transitScore']], PDO::PARAM_INT);
        $stmt->bindParam(':avgTemp', $csvRow[$columns['avgTemp']]);
        $stmt->bindParam(':population', $csvRow[$columns['population']], PDO::PARAM_INT);
        $stmt->bindParam(':landAreaSquareMiles', $csvRow[$columns['landAreaSquareMiles']]);
        $stmt->bindParam(':coordinatesNorth', $csvRow[$columns['coordinatesNorth']]);
        $stmt->bindParam(':coordinatesWest', $csvRow[$columns['coordinatesWest']]);
        $stmt->execute();
    }
    fclose($csvFileHandle);
} else {
    print("Was not able to open dataAboutCities.csv");
}
