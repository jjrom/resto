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

/**
 * Search recusively for files in a base directory matching a glob pattern.
 * The `GLOB_NOCHECK` flag has no effect.
 *
 * @param  string $base Directory to search
 * @param  string $pattern Glob pattern to match files
 * @param  int $flags Glob flags from https://www.php.net/manual/function.glob.php
 * @return string[] Array of files matching the pattern
 */
function glob_recursive($base, $pattern, $flags = 0) {
	$flags = $flags & ~GLOB_NOCHECK;
	
	if (substr($base, -1) !== DIRECTORY_SEPARATOR) {
		$base .= DIRECTORY_SEPARATOR;
	}

	$files = glob($base.$pattern, $flags);
	if (!is_array($files)) {
		$files = [];
	}

	$dirs = glob($base.'*', GLOB_ONLYDIR|GLOB_NOSORT|GLOB_MARK);
	if (!is_array($dirs)) {
		return $files;
	}
	
	foreach ($dirs as $dir) {
		$dirFiles = glob_recursive($dir, $pattern, $flags);
		$files = array_merge($files, $dirFiles);
	}

	return $files;
}

spl_autoload_register(function ($class) {
    // Get all sub directories
    $directories = glob_recursive(__DIR__ . '/resto/', '*' , GLOB_ONLYDIR);
    
    // Find the class in each directory and then stop
    foreach ($directories as $directory) {
        $filename = $directory . '/' . $class . '.php';
        if (is_readable($filename)) {
            return require_once $filename;
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
