<?php
session_start();
include('conexion.php');

if (isset($_POST['usuario']) && isset($_POST['clave'])) {
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $usuario = validate($_POST['usuario']); 
    $clave = validate($_POST['clave']);

    if (empty($usuario)) {
        header("Location: index.php?error=El usuario es requerido");
        exit();
    } elseif (empty($clave)) {
        header("Location: index.php?error=La clave es requerida");
        exit();
    } else {
        $sql = "SELECT * FROM users WHERE usuario=? AND clave=?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $usuario, $clave);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            if ($row['usuario'] === $usuario && $row['clave'] === $clave) {
                $_SESSION['usuario'] = $row['usuario'];
                $_SESSION['nombre_completo'] = $row['nombre_completo'];
                $_SESSION['id'] = $row['id'];
                header("Location: inicio.php");
                exit();
            } else {
                header("Location: index.php?error=Usuario o contraseña incorrectos");
                exit();
            }
        } else {
            header("Location: index.php?error=Usuario o contraseña incorrectos");
            exit();
        }
    }
} else {
    header("Location: login.php");
    exit();
}
?>
