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
if (!file_exists($configFile)) {
    exit(1);
}
$config = include($configFile);
$dbDriver = new RestoDatabaseDriver($config['database'] ?? null);
$queries = [];
try {
    $dbDriver->query('BEGIN');

    /* User information is now false by default */
    $dbDriver->query('ALTER TABLE ' . $dbDriver->commonSchema . '.user ALTER COLUMN settings SET DEFAULT \'{"createdCatalogIsPublic":true,"createdCollectionIsPublic":true,"createdItemIsPublic":true,"notifyOnAddFeature":true,"notifyOnNewFollower":true,"notifyOnLikeFeature":true,"notifyOnAddComment":true,"showBio":false,"showIdentity":false,"showTopics":false,"showEmail":false,"profileNeedReview":true}\'');
    
    $dbDriver->query('UPDATE ' . $dbDriver->commonSchema . '.user SET settings = settings::jsonb || jsonb \'{"showBio":false,"showIdentity":false,"showTopics":false,"showEmail":false}\'');

    $dbDriver->query('COMMIT');
} catch (Exception $e) {
    $dbDriver->query('ROLLBACK');
    RestoLogUtil::httpError(500, $e->getMessage());
}
echo "Looks good\n";
