<?php
// config/config.php
$scriptName = $_SERVER['SCRIPT_NAME']; 
$baseDir = str_replace('/index.php', '', $scriptName);

define('BASE_URL', $baseDir);
