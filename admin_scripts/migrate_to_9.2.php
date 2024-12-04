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
            $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.' . $target . ' ADD COLUMN visibleby BIGINT[]');
            $dbDriver->query('UPDATE ' . $dbDriver->targetSchema . '.' . $target . ' SET visibleby=ARRAY[visibility::BIGINT]');
            $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.' . $target . ' DROP COLUMN visibility');
            $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.' . $target . ' ADD COLUMN visibility BIGINT[]');
            $dbDriver->query('UPDATE ' . $dbDriver->targetSchema . '.' . $target . ' SET visibility=visibleby');
            $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.' . $target . ' DROP COLUMN visibleby');
            $dbDriver->query('CREATE INDEX IF NOT EXISTS idx_visibility_' . $target . ' ON ' . $dbDriver->targetSchema . '.' . $target . ' USING GIN (visibility)');    
        }
        
        $dbDriver->query('COMMIT');

    } catch(Exception $e){
        $dbDriver->query('ROLLBACK');
        RestoLogUtil::httpError(500, $e->getMessage());
    }

