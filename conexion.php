<?php
$host = "localhost";
$user = "root"; // Changed from "roots" to "root"
$pass = "";
$db = "registro";

$conexion = mysqli_connect($host, $user, $pass, $db);

if (!$conexion) { // Changed from $con to $conexion
    die("Conexión fallida: " . mysqli_connect_error());
}
?>

