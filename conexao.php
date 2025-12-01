<?php
$servername = "localhost";
$username = "root";
$password = ""; // geralmente vazio no XAMPP
$dbname = "projeto"; // substitua pelo nome do seu banco

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
