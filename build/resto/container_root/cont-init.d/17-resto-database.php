#!/command/with-contenv php

<?php

    require_once("/app/resto/core/RestoConstants.php");
    require_once("/app/resto/core/RestoDatabaseDriver.php");
    require_once("/app/resto/core/utils/RestoLogUtil.php");

    /*
     * Read configuration from file...
     */
    $configFile = '/etc/resto/config.php';
    if ( !file_exists($configFile)) {
        exit(1);
    }

    $config = include($configFile);
    
    $dbDriver = new RestoDatabaseDriver($config['database'] ?? null);

    $replace = array('__DATABASE_COMMON_SCHEMA__', '__DATABASE_TARGET_SCHEMA__');
    $with = array($dbDriver->commonSchema, $dbDriver->targetSchema);
  
    // Handle core model
    $sqlFiles = glob('/resto-database-model/*.sql');
    for ($i = 0, $ii = count($sqlFiles); $i < $ii; $i++) {
        $dbDriver->query(str_replace($replace, $with, file_get_contents($sqlFiles[$i])));
    }

    // Create trigger for geometry_part
    if ( isset($config['database']['useGeometryPart']) && $config['database']['useGeometryPart'] ) {
        $dbDriver->query(str_replace($replace, $with, 'CREATE TRIGGER update_geometry_part AFTER INSERT ON __DATABASE_TARGET_SCHEMA__.feature FOR EACH ROW EXECUTE PROCEDURE __DATABASE_TARGET_SCHEMA__.trigger_store_geometry_part();'));
    }

    // Handle migrations scripts
    $sqlFiles = glob('/resto-database-model/migrations/*.sql');
    for ($i = 0, $ii = count($sqlFiles); $i < $ii; $i++) {
        $dbDriver->query(str_replace($replace, $with, file_get_contents($sqlFiles[$i])));
    }   

    // Handle post install - basically INSERT are here
    $sqlFiles = glob('/resto-database-model/postInstall/*.sql');
    for ($i = 0, $ii = count($sqlFiles); $i < $ii; $i++) {
        $dbDriver->query(str_replace($replace, $with, file_get_contents($sqlFiles[$i])));
    }   
    
    function map($value) {
        $myFile = pathinfo($value);
        return array($myFile['basename'] => $value);
    }

    // Handle addons - located under /app/resto/addons/*/sql/*.sql
    // [IMPORTANT] Process SQL in the right order i.e. based on filename without dirname (i.e. O1_*.sql, then 02_*.sql, etc.)
    $sqlFiles = array_map('map', glob('/app/resto/addons/*/sql/*.sql'));
    sort($sqlFiles);
    for ($i = 0, $ii = count($sqlFiles); $i < $ii; $i++) {
        foreach ($sqlFiles[$i] as $key => $value) {
            $dbDriver->query(str_replace($replace, $with, file_get_contents($value)));
        }
    }