<?php
// db.php - conexão PDO com tratamento de erros
$host = 'localhost';
$db   = 'oficina_db';
$user = 'root';
$pass = ''; // ajuste conforme sua senha

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>