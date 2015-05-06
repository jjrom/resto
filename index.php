<?php
/*
 * Copyright 2014 Jérôme Gasperi
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

/*
 * Autoload controllers and modules
 */
function autoload($className) {
    foreach (array(
        'include/resto/',
        'include/resto/Drivers/',
        'include/resto/Collections/',
        'include/resto/Models/',
        'include/resto/Dictionaries/',
        'include/resto/Modules/',
        'include/resto/Routes/', 
        'include/resto/Utils/', 
        'include/resto/XML/',
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