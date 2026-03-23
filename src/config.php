<?php
$host = 'db'; 
$user = 'root';
$pass = 'root_password';
$db   = 'azienda_agricola';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Errore di connessione: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>