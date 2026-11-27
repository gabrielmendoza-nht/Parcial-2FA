<?php
$sql_host = "localhost";
$sql_name = "company_info";
$sql_user = "user";
$sql_pass = "Clave123";

try {
    $pdo = new PDO("mysql:host=$sql_host;dbname=$sql_name;charset=utf8mb4", $sql_user, $sql_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}