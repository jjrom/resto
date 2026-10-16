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

    $dbDriver->query("CREATE OR REPLACE FUNCTION update_collection_visibility() RETURNS TRIGGER AS $$
BEGIN
   UPDATE " . $dbDriver->commonSchema . ".catalog SET visibility = NEW.visibility WHERE catalog.id LIKE '%' || NEW.id;
   RETURN NEW;
END;
$$ LANGUAGE plpgsql;");
    $dbDriver->query("CREATE OR REPLACE TRIGGER update_collection_visibility_in_catalogs AFTER INSERT OR UPDATE ON " . $dbDriver->commonSchema . ".collection
   FOR EACH ROW EXECUTE FUNCTION update_collection_visibility();");

    $dbDriver->query("UPDATE " . $dbDriver->commonSchema . ".catalog SET visibility = collection.visibility 
FROM " . $dbDriver->commonSchema . ".collection
WHERE catalog.rtype = 'collection' AND catalog.id LIKE '%' || collection.id;");

    $dbDriver->query("ALTER TABLE " . $dbDriver->commonSchema . ".feature DROP COLUMN IF EXISTS visibility");
    
    $dbDriver->query("UPDATE " . $dbDriver->commonSchema . ".feature SET metadata = (metadata::jsonb - 'visibility')");

    $dbDriver->query('COMMIT');
} catch (Exception $e) {
    $dbDriver->query('ROLLBACK');
    RestoLogUtil::httpError(500, $e->getMessage());
}
echo "Looks good\n";
