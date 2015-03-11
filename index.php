<?php

/*
 * Autoload controllers and modules
 */
function autoload($className) {
    foreach (array(
        'include/resto/',
        'include/resto/Drivers/',
        'include/resto/Drivers/PostgreSQL/',
        'include/resto/Collections/',
        'include/resto/Models/',
        'include/resto/Dictionaries/',
        'include/resto/Modules/',
        'include/resto/Routes/', 
        'include/resto/Utils/', 
        'lib/iTag/',
        'lib/JWT/') as $current_dir) {
        $path = $current_dir . sprintf('%s.php', $className);
        if (file_exists($path)) {
            include $path;
            return;
        }
    }
}
spl_autoload_register('autoload');

/*
 * Launch RESTo
 */
new Resto(realpath(dirname(__FILE__)) . '/include/config.php');