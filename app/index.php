<?php
/*
 * Copyright 2018 Jérôme Gasperi
 *
 * Licensed under the Apache License, version 2.0 (the "License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

spl_autoload_register ( function ($class) {
    
    $dirs = array(
        "resto/core/",
        "resto/core/addons/",
        "resto/core/api/",
        "resto/core/dictionaries/",
        "resto/core/dbfunctions/",
        "resto/core/models/",
        "resto/core/utils/",
        "resto/core/xml/",
        // This is where you put external addons, models, etc.
        "resto/addons/"
    );
    
    foreach ($dirs as $dir) {
        $src = $dir . $class . '.php';
        if (file_exists($src)) {
            return include $src;
        }
    }

});

/*
 * Read configuration from file...
 */
$configFile = '/etc/resto/config.php';
if (file_exists($configFile)) {
    return new Resto(include($configFile));
}

/*
 * ...or use default if not exist
*/
error_log('[WARNING] Config file ' . $configFile . ' not found - using default configuration');
return new Resto();

