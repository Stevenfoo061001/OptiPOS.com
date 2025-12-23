<?php
$pdo = new PDO(
    "pgsql:host=localhost;port=5432;dbname=postgres",
    "postgres",
    "061001", //Change your postgreSQL password
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);
