<?php

$phps = array();

function loadPHP($folder)
{
    global $phps;

    $files = glob($folder . "/*");
    
    foreach ($files as $file) {
        if (is_dir($file)) {
            loadPHP($file);
        } else {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $phps[] = $file;
            }
        }
    }
}

loadPHP("/app/resto");
loadPHP("/app/vendor");

foreach ($phps as $php) {
    opcache_compile_file($php);
}
