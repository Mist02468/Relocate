<?php

$columns = array(
            'cityName'            => 0,
            'stateName'           => 1, 
            'population'          => 2,
            'landAreaSquareMiles' => 3,
            'coordinatesNorth'    => 4,
            'coordinatesWest'     => 5
           );

$dbHandle      = new PDO('mysql:host=localhost;port=3305;dbname=relocate', 'root', 'bitnami');
$csvFileHandle = fopen("dataAboutCities.csv", "r");   
        
if ($csvFileHandle !== False) {
    while (($csvRow = fgetcsv($csvFileHandle)) !== False) {
        
        $stmt = $dbHandle->prepare('SELECT id FROM states where name = :name');
        $stmt->bindParam(':name', $csvRow[$columns['stateName']]);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        if (count($results) === 0) {
            //this state has not been added to the database yet, do so and note the id
            $stmt = $dbHandle->prepare('INSERT INTO states (name) VALUES (:name)');
            $stmt->bindParam(':name', $csvRow[$columns['stateName']]);
            $stmt->execute();
            $stateID = (int) $dbHandle->lastInsertId();
        } elseif (count($results) === 1) {
            //this state is in the database, note the id
            $stateID = (int) $results[0]['id'];   
        } else {
            //this state is in the database more than once, this should not occur
            throw new Exception('Found more than one row in the states table with the name ' . $csvRow[$columns['stateName']]);
        }

        $stmt = $dbHandle->prepare('SELECT id FROM cities where name = :name');
        $stmt->bindParam(':name', $csvRow[$columns['cityName']]);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        if (count($results) === 0) {
            //this city has not been added to the database yet, do so
            $stmt = $dbHandle->prepare('INSERT INTO cities (name, state_id, population, landAreaSquareMiles, coordinatesNorth, coordinatesWest) VALUES (:name, :state_id, :population, :landAreaSquareMiles, :coordinatesNorth, :coordinatesWest)');
            $stmt->bindParam(':name', $csvRow[$columns['cityName']]);
            $stmt->bindParam(':state_id', $stateID);
            $stmt->bindParam(':population', $csvRow[$columns['population']]);
            $stmt->bindParam(':landAreaSquareMiles', $csvRow[$columns['landAreaSquareMiles']]);
            $stmt->bindParam(':coordinatesNorth', $csvRow[$columns['coordinatesNorth']]);
            $stmt->bindParam(':coordinatesWest', $csvRow[$columns['coordinatesWest']]);
            $stmt->execute();
        } elseif (count($results) > 1) {
            //this city is in the database more than once, this should not occur
            throw new Exception('Found more than one row in the cities table with the name ' . $csvRow[$columns['cityName']]);
        }
    }
    fclose($csvFileHandle);
} else {
    print("Was not able to open dataAboutCities.csv");
}