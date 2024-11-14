<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <title>Inicio de sesion</title>
</head>
<body>
    
<form action="Iniciosesion.php" method="POST">
<h1>Inicio de sesion</h1>
<hr>
<?php
    if (isset($_GET['error'])) 
    {
    ?>
    <p class="error">
        <?php
        echo $_GET['error'];
        ?>
    </p>
    <?php
    }
?>
    <i class="ti ti-user"></i>
    <label > Usuario </label>
    <input type="text" name="usuario" placeholder="Nombre de usuario">
<br>
    <i class="ti ti-lock"></i>
    <label > contraseña </label>
    <input type="text" name="clave" placeholder="clave">

    <button type="submit">Iniciar sesion</button>


</form>
</body>
</html>