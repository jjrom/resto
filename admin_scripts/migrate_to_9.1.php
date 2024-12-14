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

        $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.collection ADD COLUMN IF NOT EXISTS title TEXT');
        $dbDriver->query('ALTER TABLE ' . $dbDriver->targetSchema . '.collection ADD COLUMN IF NOT EXISTS description TEXT');
        $dbDriver->query('WITH tmp as (SELECT collection, longname, description FROM ' . $dbDriver->targetSchema . '.osdescription WHERE lang=\'en\') UPDATE ' . $dbDriver->targetSchema . '.collection SET title=tmp.longname, description=tmp.description FROM tmp WHERE tmp.collection=id');
        $dbDriver->query('DROP TABLE ' . $dbDriver->targetSchema . '.osdescription');

        $results = $dbDriver->query('SELECT id, level, owner, links FROM ' . $dbDriver->targetSchema . '.catalog WHERE json_array_length(links) > 0');
        while ($result = pg_fetch_assoc($results)) {
            $links = json_decode($result['links'], true);
            $level = (integer)$result['level'] + 1;
            $keepLinks = [];
            for ($i = 0, $ii = count($links); $i < $ii; $i++) {
                if ( str_starts_with($links[$i]['href'], $config['baseUrl'] . '/collections/' ) ) {
                    $collectionId = explode('/', substr($links[$i]['href'], strlen($config['baseUrl'] . '/collections/')))[0];
                    $queries[] = 'INSERT INTO ' . $dbDriver->targetSchema . '.catalog (id,level,counters,owner,created,visibility,rtype) VALUES (\'' . ($result['id'] . '/' . $collectionId) . '\',' . $level . ',\'{"total":0, "collections":[]}\',' . $result['owner'] . ',now(),100,\'collection\')';
                }
                else {
                    $keepLinks[] = $links[$i];
                }
            }
            $keepLinksStr = str_replace('{}', '[]', json_encode($keepLinks, JSON_UNESCAPED_SLASHES));
            $queries[] = 'UPDATE ' . $dbDriver->targetSchema . '.catalog SET links=\'' . $keepLinksStr . '\' WHERE id=\'' . $result['id'] . '\'';
        }

        for ($i = 0, $ii = count($queries); $i < $ii; $i++) {
            $dbDriver->query($queries[$i]);
        }
        $dbDriver->query('COMMIT');

    } catch(Exception $e){
        $dbDriver->query('ROLLBACK');
        RestoLogUtil::httpError(500, $e->getMessage());
    }
    echo "Looks good\n";
