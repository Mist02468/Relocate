<?php

$stateAbbreviations = array(
  'Alabama'              => 'AL',
  'Alaska'               =>	'AK',
  'Arizona'              => 'AZ',
  'Arkansas'             => 'AR',
  'California'           => 'CA',
  'Colorado'             => 'CO',
  'Connecticut'          => 'CT',
  'Delaware'             => 'DE',
  'District of Columbia' => 'DC',
  'Florida'              => 'FL',
  'Georgia'              => 'GA',
  'Hawai\'i'             => 'HI',
  'Idaho'                => 'ID',
  'Illinois'             => 'IL',
  'Indiana'              => 'IN',
  'Iowa'                 => 'IA',
  'Kansas'               => 'KS',
  'Kentucky'             => 'KY',
  'Louisiana'            => 'LA',
  'Maine'                => 'ME',
  'Maryland'             => 'MD',
  'Massachusetts'        => 'MA',
  'Michigan'             => 'MI',
  'Minnesota'            => 'MN',
  'Mississippi'          => 'MS',
  'Missouri'             => 'MO',
  'Montana'              => 'MT',
  'Nebraska'             => 'NE',
  'Nevada'               => 'NV',
  'New Hampshire'        => 'NH',
  'New Jersey'           => 'NJ',
  'New Mexico'           => 'NM',
  'New York'             => 'NY',
  'North Carolina'       => 'NC',
  'North Dakota'         => 'ND',
  'Ohio'                 => 'OH',
  'Oklahoma'             => 'OK',
  'Oregon'               => 'OR',
  'Pennsylvania'         => 'PA',
  'Rhode Island'         => 'RI',
  'South Carolina'       => 'SC',
  'South Dakota'         => 'SD',
  'Tennessee'            => 'TN',
  'Texas'                => 'TX',
  'Utah'                 => 'UT',
  'Vermont'              => 'VT',
  'Virginia'             => 'VA',
  'Washington'           => 'WA',
  'West Virginia'        => 'WV',
  'Wisconsin'            => 'WI',
  'Wyoming'              => 'WY'
);

$secrets  = parse_ini_file('config/autoload/localSecrets.ini');
$dbHandle = new PDO('mysql:host=' . $secrets['Host'] . ';port=' . $secrets['Port'] . ';dbname=' . $secrets['Database'], $secrets['Username'], $secrets['Password']);

$query = <<<'SQL'
SELECT c.id AS cityId, c.name AS cityName, s.name AS stateName, c.companiesLastUpdated
FROM city c
INNER JOIN state s ON s.id = c.state_id
SQL;
$stmt = $dbHandle->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll();

foreach ($results as $result) {
    gatherDataForACity($secrets['IndeedPublisherId'], $result['cityId'], $result['cityName'], $stateAbbreviations[$result['stateName']], $result['companiesLastUpdated']);
    setLastUpdatedForACity($result['cityId']);
}

function gatherDataForACity($publisherId, $cityId, $cityName, $stateAbbreviation, $lastUpdated) {
    $location = $cityName . '+' . $stateAbbreviation;
    $fromAge  = '';
    if (is_null($lastUpdated) === false) {
        $interval = date_diff(date_create($lastUpdated), date_create());
        $fromAge = $interval->format('%a');
    }

    $request      = 'http://api.indeed.com/ads/apisearch?publisher=' . $publisherId . '&q=software+developer&l=' . urlencode($location) . '&sort=&radius=&st=&jt=fulltime&start=&limit=25&fromage=' . $fromAge . '&filter=&latlong=1&co=us&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2';
    $response     = makeCurlRequest($request);
    $totalResults = intval($response->totalresults->__toString());

    $numProcessed  = 0;
    $numProcessed += processPageOfResults($response->results->result);

    while ($numProcessed < $totalResults) {
        $request       = 'http://api.indeed.com/ads/apisearch?publisher=' . $publisherId . '&q=software+developer&l=' . urlencode($location) . '&sort=&radius=&st=&jt=fulltime&start=' . $numProcessed . '&limit=25&fromage=' . $fromAge . '&filter=&latlong=1&co=us&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2';
        $response      = makeCurlRequest($request);
        $numProcessed += processPageOfResults($response->results->result);
    }
}

function processPageOfResults($results) {
    $numProcessed = 0;
    foreach ($results as $result) {
        $companyName = $result->company->__toString();
        $companyId   = getOrCreateCompany($companyName);

        $companyLatitude  = floatval($result->latitude->__toString());
        $companyLongitude = floatval($result->longitude->__toString());
        createCompanyLocationIfNecessary($companyId, $companyLatitude, $companyLongitude);
        $numProcessed++;
    }
    return $numProcessed;
}

function getOrCreateCompany($name) {
    global $dbHandle;

    $stmt = $dbHandle->prepare('SELECT id FROM company WHERE name = :name');
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->execute();

    $results = $stmt->fetchAll();
    if (count($results) === 0) {
        $stmt = $dbHandle->prepare('INSERT INTO company (name) VALUES (:name)');
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        $companyId = (int) $dbHandle->lastInsertId();
    } elseif (count($results) === 1) {
        //this company is in the database, note the id
        $companyId = (int) $results[0]['id'];
    } else {
        //this company is in the database more than once, this should not occur
        throw new Exception('Found more than one row in the company table with the name ' . $name);
    }

    return $companyId;
}

function createCompanyLocationIfNecessary($id, $latitude, $longitude) {
    global $dbHandle;

    $stmt = $dbHandle->prepare('SELECT COUNT(*) AS isMatchingCompanyLocation FROM companyLocation WHERE company_id = :id AND coordinatesNorth = :coordinatesNorth AND coordinatesWest  = :coordinatesWest');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':coordinatesNorth', $latitude);
    $stmt->bindParam(':coordinatesWest',  $longitude);
    $stmt->execute();

    $results = $stmt->fetchAll();
    if (intval($results[0]['isMatchingCompanyLocation']) === 0) {
        $stmt = $dbHandle->prepare('INSERT INTO companyLocation (company_id, coordinatesNorth, coordinatesWest) VALUES (:company_id, :coordinatesNorth, :coordinatesWest)');
        $stmt->bindParam(':company_id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':coordinatesNorth', $latitude);
        $stmt->bindParam(':coordinatesWest',  $longitude);
        $stmt->execute();
    }
}

//handles the curl functions needed to make an API request
function makeCurlRequest($request) {
    //initialize curl with the url of the request
    $handle   = curl_init($request);
    //get the response as a string rather than outputting it
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, True);
    //process the response as xml, parse it
    $response = simplexml_load_string(curl_exec($handle));
    //check for errors
    $errors   = curl_error($handle);
    //close the curl handle
    curl_close($handle);

    //if there are no errors, return the processed response
    if (empty($errors)) {
        return $response;
    } else {
        throw new Exception($errors); //let me know there's still debugging for me to do
    }
}

function setLastUpdatedForACity($id) {
    global $dbHandle;

    $stmt = $dbHandle->prepare('UPDATE city SET companiesLastUpdated = NOW() WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}
