#!/command/with-contenv php

<?php

    require_once("/app/resto/core/RestoConstants.php");
    require_once("/app/resto/core/RestoDatabaseDriver.php");
    require_once("/app/resto/core/utils/RestoLogUtil.php");
    require_once("/app/resto/core/dbfunctions/UsersFunctions.php");

    /*
     * Read configuration from file...
     */
    $configFile = '/etc/resto/config.php';
    if ( !file_exists($configFile)) {
        exit(1);
    }
    $config = include($configFile);
    $dbDriver = new RestoDatabaseDriver($config['database'] ?? null);
    $queries = [];
    try {

        $dbDriver->query('BEGIN');

        /* User name is now UNIQUE */
        $dbDriver->query('UPDATE ' . $dbDriver->targetSchema . '.user SET name = lower(name)');
        $dbDriver->query('WITH tmp AS (SELECT name as n FROM ' . $dbDriver->targetSchema . '.user GROUP BY name HAVING count(name) > 1) UPDATE ' . $dbDriver->targetSchema . '.user SET name = tmp.n || (floor(random() * 1000 + 1)::int)::TEXT FROM tmp WHERE name = tmp.n');
        $dbDriver->query('UPDATE ' . $dbDriver->targetSchema . '.user SET name = \'anonymous\' || (floor(random() * 1000 + 1)::int)::TEXT WHERE name IS NULL');
        $dbDriver->query('ALTER TABLE ' . $dbDriver->commonSchema . '.user ADD UNIQUE (name)');
        
        // Create private group per user
        $dbDriver->query('DROP INDEX IF EXISTS ' . $dbDriver->commonSchema . '.idx_uname_group');
        $dbDriver->query('WITH tmp AS (SELECT id, name FROM ' . $dbDriver->commonSchema . '.user) INSERT INTO ' . $dbDriver->commonSchema . '.group (name, description, owner, private) SELECT name || \'_private\', \'Private group for user \' || name, id, 1 FROM tmp WHERE name <> \'admin\'');
        
        
        $dbDriver->query('COMMIT');

    } catch(Exception $e){
        $dbDriver->query('ROLLBACK');
        RestoLogUtil::httpError(500, $e->getMessage());
    }
    echo "Looks good\n";
    
    