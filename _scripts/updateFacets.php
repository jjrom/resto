#!/usr/bin/env php
<?php

/*==========================================
 *
 * FOR cron task $execute should be set to true
 *
 */
$dbname = 'resto';
$username = 'postgres';
$password = 'postgres';
$hostname = null;
$from = 'NOW()';
$timeframe = '1 DAY';
$execute = false;
$port = 5432;
$orderColumn = 'updated';
$nbOfFeaturesPerUpdate = 1000;
/*=========================================*/


/*==========================================
 *  DO NOT TOUCH ANYTHING BELOW THIS LINE
 *==========================================*/

/**
 * Return PostgreSQL database handler
 *
 * @param array $options
 * @throws Exception
 */
function getHandler($options = array()) {

    $dbh = null;

    if (isset($options) && isset($options['dbname'])) {
        try {
            $dbInfo = array(
                'dbname=' . $options['dbname'],
                'user=' . $options['user'],
                'password=' . $options['password']
            );
            /*
             * If host is specified, then TCP/IP connection is used
             * Otherwise socket connection is used
             */
            if (isset($options['host'])) {
                $dbInfo[] = 'host=' . $options['host'];
                $dbInfo[] = 'port=' . (isset($options['port']) ? $options['port'] : '5432');
            }
            $dbh = pg_connect(join(' ', $dbInfo));
            if (!$dbh) {
                throw new Exception();
            }
        } catch (Exception $e) {
            return null;
        }
    }

    return $dbh;
}

/**
 * Return facet category 
 * 
 * @param string $type
 */
function getFacetCategory($type) {
    if (!isset($type)) {
        return null;
    }

  
    $facetCategories = array(
        array(
            'collection'
        ),
        array(
            'productType'
        ),
        array(
            'processingLevel'
        ),
        array(
            'platform',
            'instrument',
            'sensorMode'
        ),
        array(
            'continent',
            'country',
            'region',
            'state'
        ),
        array(
            'year',
            'month',
            'day'
        )
    );

    for ($i = count($facetCategories); $i--;) {
        for ($j = count($facetCategories[$i]); $j--;) {
            if ($facetCategories[$i][$j] === $type) {
                return $facetCategories[$i];
            }
        }
    }
    
    /*
     * Otherwise return $type as a new facet category
     */
    return $type;
    
}

/**
 * Upate input $counterArray with input facets
 *
 * @param array $counterArray
 * @param array $facets
 * @param string $collectionName
 */
function updateCounter($counterArray, $facets, $collectionName) {

    foreach (array_values($facets) as $facetElement) {

        $hash = isset($facetElement['hash']) ? $facetElement['hash'] : '';
        $parentHash = isset($facetElement['parentHash']) ? $facetElement['parentHash'] : '';
        $uniqueKey = join('|', array($hash, $parentHash, $collectionName));

        if (isset($counterArray[$uniqueKey])) {
            $counterArray[$uniqueKey]['counter'] = $counterArray[$uniqueKey]['counter'] + 1;
        }
        else {
            $counterArray[$uniqueKey] = array(
                'uid' => isset($facetElement['hash']) ? $facetElement['hash'] : null,
                'value' => isset($facetElement['name']) ? $facetElement['name'] : null,
                'type' => isset($facetElement['type']) ? $facetElement['type'] : null,
                'pid' => isset($facetElement['parentHash']) ? $facetElement['parentHash'] : null,
                'collection' => isset($collectionName) ? $collectionName : null,
                'counter' => 1
            );
        }
        
    }

    return $counterArray;

}

/**
 * Get facets from keywords
 *
 * @param array $keywords
 */
function getFacetsFromKeywords($keywords) {

    /*
     * One facet per keyword
     */
    $facets = array();
    foreach ($keywords as $hash => $keyword) {
        if (getFacetCategory($keyword['type'])) {
            $facets[] = array(
                'name' => $keyword['name'],
                'type' => $keyword['type'],
                'hash' => $keyword['id'],
                'parentHash' => isset($keyword['parentHash']) ? $keyword['parentHash'] : null
            );
        }
    }

    return $facets;

}

/**
 * Convert countArray to a list of SQL Upsert
 */
function getSQLQueries($counterArray) {

    $queries = array();

    foreach (array_values($counterArray) as $facetElement) {

        $arr = array(
            '\'' . pg_escape_string($facetElement['uid']) . '\'',
            '\'' . pg_escape_string($facetElement['value']) . '\'',
            '\'' . pg_escape_string($facetElement['type']) . '\'',
            isset($facetElement['pid']) ? '\'' . pg_escape_string($facetElement['pid']) . '\'' : 'NULL',
            isset($facetElement['collection']) ? '\'' . pg_escape_string($facetElement['collection']) . '\'' : 'NULL',
            $facetElement['counter']
        );

        $insert = 'INSERT INTO resto.facets (uid, value, type, pid, collection, counter) SELECT ' . join(',', $arr);
        $upsert = 'UPDATE resto.facets SET counter = counter + ' .  $facetElement['counter'] .  '  WHERE uid = \'' . pg_escape_string($facetElement['uid']) . '\' AND collection = \'' . pg_escape_string($facetElement['collection']) . '\'';
        
        $query = 'WITH upsert AS (' . $upsert . ' RETURNING *) ' . $insert . ' WHERE NOT EXISTS (SELECT * FROM upsert)';
        array_push($queries, $query);

    }

    return $queries;
    
}

/*==========================================
 * Start of script
 *==========================================*/
$help = "\nCompute UPSERT for resto.facets table\n";
$help .= "\n Facets computation is done backward i.e. most recent features are processed first\n\n";
$help .= "\n USAGE : updateFacets -u <user> -p <password> [OPTIONS] \n\n";
$help .= "   MANDATORY:\n";
$help .= "          -u <db user> : Database user name with update rights on resto database\n";
$help .= "          -p <db password> : Database user password\n\n";
$help .= "   OPTIONS:\n";
$help .= "          -d <db name> : Database name (default " . $dbname . ")\n";
$help .= "          -H <hostname> : make a TCP/IP connection (default is no host i.e. socket connection)\n";
$help .= "          -P <port> : port for TCP/IP connection (default is " . $port . ")\n";
$help .= "          -o <orderColumn> : name of column used for ordering features, usually \"updated\" or \"published\" (default is " . $orderColumn . ") [WARNING]This column must be indexed[WARNING]\n";
$help .= "          -n <nbOfFeaturesPerUpdate> : split whole process in block of \"nbOfFeaturesPerUpdate\" features (default is " . $nbOfFeaturesPerUpdate . ")\n";
$help .= "          -f <from> : most recent \"" . $orderColumn . "\" date to start processing (default is " . $from . ")\n";
$help .= "          -t <timeframe> : timeframe to stop processing based on from date (default is " . $timeframe . ")\n";
$help .= "          -X : execute UPSERT on database (default is to stream UPSERT queries without execution)\n";
$options = getopt("u:p:d:H:P:o:n:f:t:Xh");
foreach ($options as $option => $value) {
    if ($option === "u") {
        $username = $value;
    }
    if ($option === "p") {
        $password = $value;
    }
    if ($option === "d") {
        $dbname = $value;
    }
    if ($option === "H") {
        $hostname = $value;
    }
    if ($option === "P") {
        $port = $value;
    }
    if ($option === "o") {
        $orderColumn = $value;
    }
    if ($option === "n") {
        $nbOfFeaturesPerUpdate = $value;
    }
    if ($option === "f") {
        $from = $value;
    }
    if ($option === "t") {
        $timeframe = $value;
    }
    if ($option === "X") {
        $execute = true;
    }
    if ($option === "h") {
        echo $help;
        exit;
    }
}

// username/password is mandatory
if (!isset($username) || !isset($password)) {
    echo $help;
    exit;
}

// Default database connection is socket based
$options = array(
    'dbname' => isset($dbname) ? $dbname : 'resto',
    'user' => $username,
    'password' => $password
);

// TCP/IP database connection (if defined)
if (isset($hostname)) {
    $options['host'] = $hostname;
    $options['port'] = isset($port) ? (int) $port : 5432;
}
$dbh = getHandler($options);

// Very bad trip
if (!isset($dbh)) {
    echo "[ERROR] Cannot connect to database - aborting\n";
    exit;
}

// Roll over each features
$continue = true;
$totalFeatures = 0;
$totalQueries = 0;

while ($continue) {

    $lastPublished = isset($lastPublished) ? $lastPublished : $from;
    $features = pg_query($dbh, 'SELECT id, collection, keywords, ' . pg_escape_string($orderColumn) . ' FROM resto.features WHERE ' . pg_escape_string($orderColumn) . ' < ' . (is_numeric(substr($lastPublished, 0, 1)) ? '\'' . $lastPublished . '\'' : $lastPublished ) . ' AND ' . pg_escape_string($orderColumn) . ' > ' . (is_numeric(substr($from, 0, 1)) ? 'TO_TIMESTAMP(\'' . $from . '\', \'YYYY-MM-DD HH24:MI:SS\')' : $from ) . ' - INTERVAL \'' . $timeframe. '\' ORDER BY ' . pg_escape_string($orderColumn) . ' DESC LIMIT ' . pg_escape_string($nbOfFeaturesPerUpdate));
    $nbOfFeatures = 0;
    $published = null;
    $counterArray = array();

    while ($feature = pg_fetch_assoc($features)) {

        // Get feature facets
        $facets = getFacetsFromKeywords(json_decode($feature['keywords'], true));
        
        // Update counters
        $counterArray = updateCounter($counterArray, $facets, $feature['collection']);

        // Set published
        $published = $feature[$orderColumn];

        $nbOfFeatures++;

    }

    // Exit from the loop
    if ($nbOfFeatures === 0) {
        break;
    }

    // Compute queries
    $queries = getSQLQueries($counterArray);

    // Execute queries...
    if ( $execute ) {

        for ($i = 0, $ii = count($queries); $i < $ii; $i++) {
            pg_query($dbh, $queries[$i]);
        }

        echo '[FROM] ' . $lastPublished . ' to ' . $published . "\n";
        echo '  ' . count($queries) . ' facets queries processed for ' . $nbOfFeatures . " features\n";

    }
    // ...or stream UPSERT to console
    else {
        for ($i = 0, $ii = count($queries); $i < $ii; $i++) {
            echo $queries[$i] . ";\n";
        }
    }

    // Total counters
    $totalFeatures = $totalFeatures + $nbOfFeatures;
    $totalQueries = $totalQueries + count($queries);
     
    // Set lastPublished to exit the loop
    $lastPublished = $published;

}

if ( $execute ) {
    echo "\n[END] " . $totalQueries . ' facets queries processed for ' . $totalFeatures . " features\n\n";
}

// Bye bye
pg_close($dbh);
