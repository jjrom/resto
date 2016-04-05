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

$hostname = 'localhost/resto';
$protocol = 'http';
$help = "\nCorrect invalid keywords hash generated in resto v2.1 i.e. hashes for:";
$help .= "\n        - region:franche-comte";
$help .= "\n        - region:western-transdanubia";
$help .= "\n        - state:montana";
$help .= "\n        - state:nebraska";
$help .= "\n        - state:zasavska\n";    
$help .= "\n USAGE: correctInvalidKeywords -H <hostname> -a <admin:password> -u <db user> -p <db password> \n";
$help .= " OPTIONS:\n";
$help .= "       -H <resto endpoint> : endpoint (default localhost/resto)\n";
$help .= "       -P <protocol> : protocol (default http)\n";
$help .= "       -a <resto admin user:user password> : resto admin username:password\n";
$help .= "       -u <db user> : Database user name with update rights on resto database\n";
$help .= "       -p <db password> : Database user password\n\n";
$options = getopt("P:H:a:u:p:h");
foreach ($options as $option => $value) {
    if ($option === "P") {
        $protocol = $value;
    }
    if ($option === "H") {
        $hostname = $value;
    }
    if ($option === "a") {
        $admin = $value;
    }
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

if (!isset($username) || !isset($password) || !isset($admin)) {
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

$results = pg_query($dbh, 'SELECT distinct(identifier) FROM resto.features WHERE (hashes @> ARRAY[\'region:franche-comte\'] OR hashes @> ARRAY[\'region:western-transdanubia\'] OR hashes @> ARRAY[\'state:montana\'] OR hashes @> ARRAY[\'state:nebraska\'] OR hashes @> ARRAY[\'state:zasavska\']) ORDER BY identifier');
while ($result = pg_fetch_assoc($results)) {
    echo 'curl -k -X PUT  "'. $protocol .'://' . $admin . '@' . $hostname . '/api/tag/' . $result['identifier'] . '/refresh"'."\n";
}
pg_close($dbh);

