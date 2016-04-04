#!/usr/bin/env php
<?php

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
            echo 'Database connection error';
            exit;
        }
    }

    return $dbh;
}

/**
 * Upgrade resto v2.1 json keywords to resto v2.2 json keywords
 * @param array $oldJSON
 */
function upgradeJSON($oldJSON) {
    $newJSON = array();
    foreach ($oldJSON as $key => $value) {
        $newValue = array();
        // Force identifier to be a string 
        $newValue['id'] = "" . $key;
        foreach ($value as $key2 => $value2) {
            $newValue[$key2] = $value2;
        }
        array_push($newJSON, $newValue);
    }
    return $newJSON;
}


$help = "\nGenerate SQL commands to update keywords column to follow resto v2.2 interface\n";
$help .= "\n USAGE : updateKeywords -u <db user> -p <db password> \n";
$help .= "   OPTIONS:\n";
$help .= "          -u <db user> : Database user name with update rights on resto database\n";
$help .= "          -p <db password> : Database user password\n";
$options = getopt("u:p:h");
foreach ($options as $option => $value) {
    if ($option === "u") {
        $username = $value;
    }
    if ($option === "p") {
        $password = $value;
    }
    if ($option === "h") {
        echo $help;
        exit;
    }
}

if (!isset($username) || !isset($password)) {
    echo $help;
    exit;
}

$dbh = getHandler(array(
    'dbname' => 'resto',
    'host' => 'localhost',
    'port' => 5432,
    'user' => $username,
    'password' => $password
));

$results = pg_query($dbh, 'SELECT identifier, keywords FROM resto.features ORDER BY identifier');
while ($result = pg_fetch_assoc($results)) {
    $newJSON = upgradeJSON(json_decode($result['keywords'], true));
    echo 'UPDATE resto.features SET keywords=\'' . pg_escape_string(json_encode($newJSON)) . '\' WHERE identifier=\'' . $result['identifier'] . '\';' ."\n";
}
