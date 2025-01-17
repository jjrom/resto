#!/command/with-contenv php

<?php

    require_once("/app/resto/core/RestoConstants.php");
    require_once("/app/resto/core/RestoDatabaseDriver.php");
    require_once("/app/resto/core/utils/RestoLogUtil.php");
    require_once("/app/resto/core/utils/Antimeridian.php");
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

    $antimeridian = new AntiMeridian();

    $targetSchema = $dbDriver->targetSchema;

    try {
        $dbDriver->query('ALTER TABLE ' . $targetSchema . '.catalog_feature ADD COLUMN IF NOT EXISTS created TIMESTAMP DEFAULT now()');
    } catch(Exception $e){
        RestoLogUtil::httpError(500, $e->getMessage());
    }
    echo "Looks good\n";
    
    