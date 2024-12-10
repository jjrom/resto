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
        
        $targets = ['collection', 'catalog', 'feature'];
        foreach ($targets as $target) {
            $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.' . $target . ' ADD COLUMN IF NOT EXISTS visibleby BIGINT[]');
            $dbDriver->query('UPDATE ' . $dbDriver->targetSchema . '.' . $target . ' SET visibleby=ARRAY[visibility::BIGINT]');
            $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.' . $target . ' DROP COLUMN IF EXISTS visibility');
            $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.' . $target . ' ADD COLUMN IF NOT EXISTS visibility BIGINT[]');
            $dbDriver->query('UPDATE ' . $dbDriver->targetSchema . '.' . $target . ' SET visibility=visibleby');
            $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.' . $target . ' DROP COLUMN IF EXISTS visibleby');
            $dbDriver->query('CREATE INDEX IF NOT EXISTS idx_visibility_' . $target . ' ON ' . $dbDriver->targetSchema . '.' . $target . ' USING GIN (visibility)');    
        }

        // Set Title to feature
        $dbDriver->query('ALTER TABLE resto.catalog_feature ADD COLUMN IF NOT EXISTS title TEXT');
        $dbDriver->query('WITH tmp AS (SELECT id, title FROM ' . $dbDriver->targetSchema . '.feature) UPDATE ' . $dbDriver->targetSchema . '.catalog_feature SET title = tmp.title FROM tmp WHERE featureid = tmp.id');
        
        // Group table update
        $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.group ADD UNIQUE (name)');
        $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.group ADD COLUMN IF NOT EXISTS private INTEGER DEFAULT 0');
        
        /* User name is now UNIQUE
        $dbDriver->query('WITH tmp AS (SELECT name as n FROM ' . $dbDriver->targetSchema . '.user GROUP BY name HAVING count(name) > 1) UPDATE ' . $dbDriver->targetSchema . '.user SET name = tmp.n || (floor(random() * 1000 + 1)::int)::TEXT FROM tmp WHERE name = tmp.n');
        $dbDriver->query('UPDATE ' . $dbDriver->targetSchema . '.user SET name = \'anonymous\' || (floor(random() * 1000 + 1)::int)::TEXT WHERE name IS NULL');
        $dbDriver->query('ALTER TABLE resto.user ADD COLUMN name UNIQUE');
        */
        $dbDriver->query('COMMIT');

    } catch(Exception $e){
        $dbDriver->query('ROLLBACK');
        RestoLogUtil::httpError(500, $e->getMessage());
    }
    echo "Looks good\n";
    
    

    

    