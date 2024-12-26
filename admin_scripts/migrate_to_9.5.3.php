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

        $dbDriver->query('BEGIN');

        // First populate geometry column
        $dbDriver->query('UPDATE ' . $targetSchema . '.feature SET geometry = geom WHERE geometry IS NULL');
        
        // Now convert all geometry so there are correctly computed with antimeridian
        $results = $dbDriver->query('SELECT id, ST_AsGeoJSON(geometry) as geometry FROM ' . $targetSchema . '.feature');
        while ($result = pg_fetch_assoc($results)) {
            $dbDriver->pQuery('UPDATE ' . $targetSchema . '.feature SET geom=ST_SetSRID(ST_GeomFromGeoJSON($2), 4326) WHERE id=$1', array(
                $result['id'],
                json_encode($antimeridian->fixGeoJSON(json_decode($result['geometry'], true)), JSON_UNESCAPED_SLASHES)
            ));
        }

        $dbDriver->query('COMMIT');

    } catch(Exception $e){
        $dbDriver->query('ROLLBACK');
        RestoLogUtil::httpError(500, $e->getMessage());
    }
    echo "Looks good\n";
    
    