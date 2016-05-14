<?php
require("/Users/jrom/.composer/vendor/autoload.php");
$swagger = \Swagger\scan('../include');
header('Content-Type: application/json');
echo $swagger;