<?php
mysqli_report(MYSQLI_REPORT_OFF);

$envPath = '.env';
$env = parse_ini_file($envPath);

$db_ready = true;
$mysqli = null;

$required_env_keys = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'DB_PORT'];

if ($env === false) {
    $db_ready = false;
    die('error: could not read .env');
}

foreach ($required_env_keys as $key) {
    if (!isset($env[$key]) || $env[$key] === '') {
        $db_ready = false;
        die('error: missing database config');
    }
}

$mysqli = new mysqli(
    $env['DB_HOST'],
    $env['DB_USER'],
    $env['DB_PASS'],
    $env['DB_NAME'],
    (int)$env['DB_PORT']
);

if ($mysqli->connect_error) {
    die('error: could not connect to database');
}
