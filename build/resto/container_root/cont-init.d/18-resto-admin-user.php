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

    $hash = password_hash(getenv('ADMIN_USER_PASSWORD') ?? 'admin', PASSWORD_BCRYPT);
    $dbDriver->pQuery('INSERT INTO ' . $dbDriver->commonSchema . '.user (id,email,groups,firstname,password,activated,registrationdate) VALUES ($1,$2,$3,$2,$4,1,now_utc()) ON CONFLICT (id) DO UPDATE SET password=$4', array(
        getenv('ADMIN_USER_ID') ?? 100,
        getenv('ADMIN_USER_NAME') ?? 'admin',
        '{0,100}',
        $hash
    ));
