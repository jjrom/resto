#!/usr/bin/env php
<?php

/*==========================================
 *
 * FOR cron task set these variable 
 * Otherwise use command line options
 *
 */
$dbname = 'resto';
$username = 'postgres';
$password = 'postgres';
$hostname = null;
$port = 5432;
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
        $upsert = 'UPDATE resto.facets SET counter = ' .  $facetElement['counter'] .  '  WHERE uid = \'' . pg_escape_string($facetElement['uid']) . '\' AND collection = \'' . pg_escape_string($facetElement['collection']) . '\'';
        
        $query = 'WITH upsert AS (' . $upsert . ' RETURNING *) ' . $insert . ' WHERE NOT EXISTS (SELECT * FROM upsert)';
        array_push($queries, $query);

    }

    return $queries;
    
}

/*==========================================
 * Start of script
 *==========================================*/
$help = "\nUpdate resto.facets table\n";
$help .= "\n USAGE : updateFacets -u <user> -p <password> [-d <dbname> -H <hostname> -P <port>] \n";
$help .= "   OPTIONS:\n";
$help .= "          -u <db user> : Database user name with update rights on resto database\n";
$help .= "          -p <db password> : Database user password\n";
$help .= "          -d <db name> : Database name (default resto)\n";
$help .= "          -H <hostname> : make a TCP/IP connection (default is no host i.e. socket connection)\n";
$help .= "          -P <port> : port for TCP/IP connection (default is 5432)\n";
$options = getopt("u:p:d:H:P:h");
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
    if ($port === "P") {
        $port = $value;
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
$counterArray = array();
while ($continue) {

    $lastPublished = isset($lastPublished) ? '\'' . $lastPublished . '\'' : 'now()';
    $features = pg_query($dbh, 'SELECT id, collection, keywords, published FROM resto.features WHERE published < ' . $lastPublished . ' ORDER BY published DESC LIMIT ' . $nbOfFeaturesPerUpdate);
    
    $nbOfFeatures = 0;
    $published = null;

    while ($feature = pg_fetch_assoc($features)) {

        // Get feature facets
        $facets = getFacetsFromKeywords(json_decode($feature['keywords'], true));
        
        // Update counters
        $counterArray = updateCounter($counterArray, $facets, $feature['collection']);

        // Set published
        $published = $feature['published'];

        $nbOfFeatures++;

    }

    // Some news
    echo '[PROCESSED] ' . $nbOfFeatures . ' features from ' . $lastPublished . ' to ' . $published . "\n";

    // Set lastPublished to exit the loop
    $lastPublished = $published;
    
    // Exit from the loop
    if ($nbOfFeatures === 0) {
        break;
    }

}

$queries = getSQLQueries($counterArray);

print_r($queries);

// Bye bye
pg_close($dbh);
