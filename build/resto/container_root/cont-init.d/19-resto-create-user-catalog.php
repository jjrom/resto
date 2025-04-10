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
    $restoUserFunctions = new UsersFunctions($dbDriver);

    $allUsers = array();
    try {

        $dbDriver->query_params('INSERT INTO ' . $dbDriver->targetSchema . '.catalog (id, title, description, level, counters, owner, visibility, created) VALUES ($1,$2,$3,$4,$5,$6,$7,now_utc()) ON CONFLICT (id) DO NOTHING', array(
            'users',
            'Users catalog',
            'Catalog for each users',
            1,
            str_replace('[]', '{}', json_encode(array(
                'total' => 0,
                'collections' => array()
            ), JSON_UNESCAPED_SLASHES)),
            RestoConstants::ADMIN_USER_ID,
            '{' . RestoConstants::GROUP_DEFAULT_ID . '}'
        ));   
        
        $allUsers = $dbDriver->fetch($dbDriver->query('SELECT u.id AS userid, u.username AS username, g.id AS groupid FROM ' . $dbDriver->commonSchema . '.user u,' . $dbDriver->commonSchema . '.group g WHERE g.owner = u.id AND g.name = u.username || \'_private\''));
        for ($i = 0; $i < count($allUsers); $i++) {
            $restoUserFunctions->createUserCatalog(array(
                'id' => $allUsers[$i]['userid'],
                'username' => $allUsers[$i]['username'],
            ), $allUsers[$i]['groupid']);
        }
    } catch (Exception $e) {
        exit(1);
    }
