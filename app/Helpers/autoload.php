<?php

$files = glob(__DIR__ . "/*Helpers.php");
foreach ($files as $file) {
    $filename = (string) $file;

    if (strpos($filename, 'Helpers.php') !== false) {
        require_once $filename;
    }
}
