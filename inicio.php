<?php
session_start();
include('conexion.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Función para limpiar datos de entrada
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Procesar el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $estatus = clean_input($_POST['estatus']);
    $ano_ciclo_escolar = clean_input($_POST['ano_ciclo_escolar']);
    $ciclo = clean_input($_POST['ciclo']);
    $periodo = clean_input($_POST['periodo']);
    $nombre = clean_input($_POST['nombre']);
    $primer_apellido = clean_input($_POST['primer_apellido']);
    $segundo_apellido = clean_input($_POST['segundo_apellido']);
    $genero = clean_input($_POST['genero']);
    $curp = clean_input($_POST['curp']);
    $fecha_nacimiento = clean_input($_POST['fecha_nacimiento']);
    $pais_nacimiento = clean_input($_POST['pais_nacimiento']);
    $entidad_federativa = clean_input($_POST['entidad_federativa']);
    $pais_procedencia = clean_input($_POST['pais_procedencia']);
    $idioma = clean_input($_POST['idioma']);
    $necesidad_educativa_especial = clean_input($_POST['necesidad_educativa_especial']);
    $antecedente_academico = clean_input($_POST['antecedente_academico']);
    $cct = clean_input($_POST['cct']);
    $matricula_institucional = clean_input($_POST['matricula_institucional']);
    $nivel_educativo = clean_input($_POST['nivel_educativo']);
    $clave_institucion = clean_input($_POST['clave_institucion']);
    $clave_carrera = clean_input($_POST['clave_carrera']);
    $turno = clean_input($_POST['turno']);
    $numero_acuerdo_rvoe = clean_input($_POST['numero_acuerdo_rvoe']);
    $fecha_acuerdo_rvoe = clean_input($_POST['fecha_acuerdo_rvoe']);
    $modalidad_educativa = clean_input($_POST['modalidad_educativa']);

    $sql = "INSERT INTO students (estatus, ano_ciclo_escolar, ciclo, periodo, nombre, primer_apellido, segundo_apellido, genero, curp, fecha_nacimiento, pais_nacimiento, entidad_federativa, pais_procedencia, idioma, necesidad_educativa_especial, antecedente_academico, cct, matricula_institucional, nivel_educativo, clave_institucion, clave_carrera, turno, numero_acuerdo_rvoe, fecha_acuerdo_rvoe, modalidad_educativa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssssssssssssssssssssss", 
        $estatus, 
        $ano_ciclo_escolar,
        $ciclo,
        $periodo, 
        $nombre, 
        $primer_apellido, 
        $segundo_apellido, 
        $genero, 
        $curp, 
        $fecha_nacimiento, 
        $pais_nacimiento, 
        $entidad_federativa, 
        $pais_procedencia, 
        $idioma, 
        $necesidad_educativa_especial, 
        $antecedente_academico, 
        $cct, 
        $matricula_institucional, 
        $nivel_educativo, 
        $clave_institucion, 
        $clave_carrera, 
        $turno, 
        $numero_acuerdo_rvoe, 
        $fecha_acuerdo_rvoe, 
        $modalidad_educativa
    );  
    
    if ($stmt->execute()) {
        $message = "Estudiante registrado exitosamente.";
    } else {
        $error = "Error al registrar estudiante: " . $conexion->error;
    }
    $stmt->close();
}

// Procesar la búsqueda
if ($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['search']) || isset($_GET['search_year']) || isset($_GET['search_cycle']) || isset($_GET['search_curp']))) {
    if (isset($_GET['search'])) {
        $search = clean_input($_GET['search']);
        $sql = "SELECT * FROM students WHERE nombre LIKE ? OR curp LIKE ?";
        $stmt = $conexion->prepare($sql);
        $search_param = "%$search%";
        $stmt->bind_param("ss", $search_param, $search_param);
    } elseif (isset($_GET['search_year']) || isset($_GET['search_cycle'])) {
        $search_year = isset($_GET['search_year']) ? clean_input($_GET['search_year']) : null;
        $search_cycle = isset($_GET['search_cycle']) ? clean_input($_GET['search_cycle']) : null;
        
        $sql = "SELECT * FROM students WHERE 1=1";
        $types = "";
        $params = array();
        
        if ($search_year) {
            $sql .= " AND ano_ciclo_escolar = ?";
            $types .= "i";
            $params[] = $search_year;
        }
        if ($search_cycle) {
            $sql .= " AND ciclo = ?";
            $types .= "i";
            $params[] = $search_cycle;
        }
        
        $stmt = $conexion->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    } elseif (isset($_GET['search_curp'])) {
        $search_curp = clean_input($_GET['search_curp']);
        $sql = "SELECT * FROM students WHERE curp = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $search_curp);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Si no hay búsqueda, mostrar todos los registros
    $sql = "SELECT * FROM students";
    $result = $conexion->query($sql);
}

// Procesar la descarga de datos
if (isset($_GET['download'])) {
    $ano = isset($_GET['ano']) ? clean_input($_GET['ano']) : null;
    $ciclo = isset($_GET['ciclo']) ? clean_input($_GET['ciclo']) : null;

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="estudiantes.csv"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for UTF-8

    fputcsv($output, array('ID', 'Estatus', 'Año Ciclo Escolar', 'Ciclo', 'Periodo', 'Nombre', 'Primer Apellido', 'Segundo Apellido', 'CURP', 'Género', 'Fecha de Nacimiento', 'País de Nacimiento', 'Entidad Federativa', 'País de Procedencia', 'Idioma', 'Necesidad Educativa Especial', 'Antecedente Académico', 'CCT', 'Matrícula Institucional', 'Nivel Educativo', 'Clave Institución', 'Clave Carrera', 'Turno', 'Número Acuerdo RVOE', 'Fecha Acuerdo RVOE', 'Modalidad Educativa'));
    
    if ($ano && $ciclo) {
        $sql = "SELECT * FROM students WHERE ano_ciclo_escolar = ? AND ciclo = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $ano, $ciclo);
        $stmt->execute();
        $query = $stmt->get_result();
    } else {
        $query = $conexion->query("SELECT * FROM students");
    }
    
    while ($row = $query->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Procesar la actualización de estudiantes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = clean_input($_POST['id']);
    $estatus = clean_input($_POST['estatus']);
    $ano_ciclo_escolar = clean_input($_POST['ano_ciclo_escolar']);
    $ciclo = clean_input($_POST['ciclo']);
    $periodo = clean_input($_POST['periodo']);
    $nombre = clean_input($_POST['nombre']);
    $primer_apellido = clean_input($_POST['primer_apellido']);
    $segundo_apellido = clean_input($_POST['segundo_apellido']);
    $genero = clean_input($_POST['genero']);
    $curp = clean_input($_POST['curp']);
    $fecha_nacimiento = clean_input($_POST['fecha_nacimiento']);
    $pais_nacimiento = clean_input($_POST['pais_nacimiento']);
    $entidad_federativa = clean_input($_POST['entidad_federativa']);
    $pais_procedencia = clean_input($_POST['pais_procedencia']);
    $idioma = clean_input($_POST['idioma']);
    $turno = clean_input($_POST['turno']);
    $necesidad_educativa_especial = clean_input($_POST['necesidad_educativa_especial']);
    $antecedente_academico = clean_input($_POST['antecedente_academico']);

    $sql = "UPDATE students SET estatus=?, ano_ciclo_escolar=?, ciclo=?, periodo=?, nombre=?, primer_apellido=?, segundo_apellido=?, genero=?, curp=?, fecha_nacimiento=?, pais_nacimiento=?, entidad_federativa=?, pais_procedencia=?, idioma=?, turno=?, necesidad_educativa_especial=?, antecedente_academico=? WHERE id=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssissssssssssssssi", $estatus, $ano_ciclo_escolar, $ciclo, $periodo, $nombre, $primer_apellido, $segundo_apellido, $genero, $curp, $fecha_nacimiento, $pais_nacimiento, $entidad_federativa, $pais_procedencia, $idioma, $turno, $necesidad_educativa_especial, $antecedente_academico, $id);
    
    if ($stmt->execute()) {
        $message = "Estudiante actualizado exitosamente.";
    } else {
        $error = "Error al actualizar estudiante: " . $conexion->error;
    }
    $stmt->close();
}

// Obtener datos para edición
if (isset($_GET['edit'])) {
    $edit_id = clean_input($_GET['edit']);
    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    $edit_data = $edit_result->fetch_assoc();
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Registro de Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Sistema de Registro de Estudiantes</h1>
        
        <?php
        if (isset($message)) {
            echo "<div class='alert alert-success'>$message</div>";
        }
        if (isset($error)) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
        ?>
        
        <!-- Formulario de registro -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="estatus" class="form-label">Estatus:</label>
                    <select name="estatus" class="form-select" required>
                        <option value="Inscripción">Inscripción</option>
                        <option value="Reinscripción">Reinscripción</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="ano_ciclo_escolar" class="form-label">Año del ciclo escolar:</label>
                    <input type="number" name="ano_ciclo_escolar" class="form-control" required min="2000" max="2100">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="ciclo" class="form-label">ciclo:</label>
                    <select name="ciclo" class="form-select" required>
                        <option value="1">1</option>
                        <option value="2">2</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="periodo" class="form-label">Periodo:</label>
                    <select name="periodo" class="form-select" required>
                        <option value="Semestral">Semestral</option>
                        <option value="Cuatrimestral">Cuatrimestral</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" name="nombre" class="form-control" required maxlength="70">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="primer_apellido" class="form-label">Primer Apellido:</label>
                    <input type="text" name="primer_apellido" class="form-control" required maxlength="70">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="segundo_apellido" class="form-label">Segundo Apellido:</label>
                    <input type="text" name="segundo_apellido" class="form-control" maxlength="70">
                </div>
            </div>
    <div class="row">
    <div class="col-md-4 mb-3">
        <label for="genero" class="form-label">Género:</label>
        <select name="genero" class="form-select" required>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="curp" class="form-label">CURP:</label>
        <input type="text" name="curp" class="form-control" required maxlength="18" pattern="^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z][0-9]$">
    </div>
    <div class="col-md-4 mb-3">
        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
        <input type="date" name="fecha_nacimiento" class="form-control" required>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="pais_nacimiento" class="form-label">País de Nacimiento:</label>
        <input type="text" name="pais_nacimiento" class="form-control" required maxlength="50">
    </div>
    <div class="col-md-6 mb-3">
        <label for="entidad_federativa" class="form-label">Entidad Federativa:</label>
        <input type="text" name="entidad_federativa" class="form-control" required maxlength="50">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="pais_procedencia" class="form-label">País de Procedencia:</label>
        <input type="text" name="pais_procedencia" class="form-control" maxlength="50">
    </div>
    <div class="col-md-6 mb-3">
        <label for="idioma" class="form-label">Idioma:</label>
        <input type="text" name="idioma" class="form-control" maxlength="50">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="necesidad_educativa_especial" class="form-label">Necesidad Educativa Especial:</label>
        <select name="necesidad_educativa_especial" class="form-select">
            <option value="Sí">Sí</option>
            <option value="No">No</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="antecedente_academico" class="form-label">Antecedente Académico:</label>
        <select name="antecedente_academico" class="form-select" required>
            <option value="Sí">Sí</option>
            <option value="No">No</option>
        </select>
    </div>
</div>


<div class="row">
    <div class="col-md-6 mb-3">
        <label for="cct" class="form-label">CCT:</label>
        <input type="text" name="cct" class="form-control" maxlength="10">
    </div>
    <div class="col-md-6 mb-3">
        <label for="matricula_institucional" class="form-label">Matrícula Institucional:</label>
        <input type="text" name="matricula_institucional" class="form-control" required maxlength="20">
    </div>
</div>


<div class="row">
    <div class="col-md-4 mb-3">
        <label for="nivel_educativo" class="form-label">Nivel Educativo:</label>
        <select name="nivel_educativo" class="form-select" required>
            <option value="licenciatura">Licenciatura</option>
            <option value="maestria">Maestría</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="clave_institucion" class="form-label">Clave Institución:</label>
        <input type="text" name="clave_institucion" class="form-control" required maxlength="10">
    </div>
    <div class="col-md-4 mb-3">
        <label for="clave_carrera" class="form-label">Clave Carrera:</label>
        <input type="text" name="clave_carrera" class="form-control" required maxlength="10">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="turno" class="form-label">Turno:</label>
        <select name="turno" class="form-select">
            <option value="Matutino">Matutino</option>
            <option value="Vespertino">Vespertino</option>
            <option value="Mixto">Mixto</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="numero_acuerdo_rvoe" class="form-label">Número Acuerdo RVOE:</label>
        <input type="text" name="numero_acuerdo_rvoe" class="form-control" required maxlength="70">
    </div>
    <div class="col-md-4 mb-3">
        <label for="fecha_acuerdo_rvoe" class="form-label">Fecha Acuerdo RVOE:</label>
        <input type="date" name="fecha_acuerdo_rvoe" class="form-control" required>
    </div>
</div>


<div class="row">
    <div class="col-md-12 mb-3">
        <label for="modalidad_educativa" class="form-label">Modalidad Educativa:</label>
        <select name="modalidad_educativa" class="form-select" required>
            <option value="Escolar">Escolar</option>
            <option value="No escolarizada">No escolarizada</option>
            <option value="Mixta">Mixta</option>
        </select>
    </div>
</div>
            <!-- Add the rest of the form fields here -->
            
            <button type="submit" name="register" class="btn btn-primary">Registrar Estudiante</button>
            </form>

        <!-- Formulario de búsqueda por año y ciclo -->
        <form method="get" class="mb-3 mt-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="number" name="search_year" class="form-control" placeholder="Año del ciclo escolar">
                </div>
                <div class="col-md-4">
                    <select name="search_cycle" class="form-select">
                        <option value="">Ciclo</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Buscar por Año y Ciclo</button>
                </div>
            </div>
        </form>

        <!-- Formulario de búsqueda por CURP -->
        <form method="get" class="mb-3 mt-3">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" name="search_curp" class="form-control" placeholder="Buscar por CURP">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Buscar por CURP</button>
                </div>
            </div>
        </form>

        <!-- Botón de descarga -->
        <form method="get" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="number" name="ano" class="form-control" placeholder="Año del ciclo escolar">
                </div>
                <div class="col-md-4">
                    <select name="ciclo" class="form-select">
                        <option value="">Ciclo</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="download" class="btn btn-success">Descargar datos (CSV)</button>
                </div>
            </div>
        </form>

        <!-- Formulario de edición -->
        <?php if (isset($edit_data)): ?>
        <h2>Editar Estudiante</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
            <!-- Include all editable fields here -->
      <!-- Formulario de edición -->
<?php if (isset($edit_data)): ?>
<h2>Editar Estudiante</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="estatus" class="form-label">Estatus:</label>
            <select name="estatus" class="form-select" required>
                <option value="Inscripción" <?php echo $edit_data['estatus'] == 'Inscripción' ? 'selected' : ''; ?>>Inscripción</option>
                <option value="Reinscripción" <?php echo $edit_data['estatus'] == 'Reinscripción' ? 'selected' : ''; ?>>Reinscripción</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="ano_ciclo_escolar" class="form-label">Año del ciclo escolar:</label>
            <input type="number" name="ano_ciclo_escolar" class="form-control" required min="2000" max="2100" value="<?php echo $edit_data['ano_ciclo_escolar']; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label for="ciclo" class="form-label">Ciclo:</label>
            <select name="ciclo" class="form-select" required>
                <option value="1" <?php echo $edit_data['ciclo'] == 1 ? 'selected' : ''; ?>>1</option>
                <option value="2" <?php echo $edit_data['ciclo'] == 2 ? 'selected' : ''; ?>>2</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="periodo" class="form-label">Periodo:</label>
            <select name="periodo" class="form-select" required>
                <option value="Semestral" <?php echo $edit_data['periodo'] == 'Semestral' ? 'selected' : ''; ?>>Semestral</option>
                <option value="Cuatrimestral" <?php echo $edit_data['periodo'] == 'Cuatrimestral' ? 'selected' : ''; ?>>Cuatrimestral</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="nombre" class="form-label">Nombre:</label>
            <input type="text" name="nombre" class="form-control" required maxlength="70" value="<?php echo $edit_data['nombre']; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label for="primer_apellido" class="form-label">Primer Apellido:</label>
            <input type="text" name="primer_apellido" class="form-control" required maxlength="70" value="<?php echo $edit_data['primer_apellido']; ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="segundo_apellido" class="form-label">Segundo Apellido:</label>
            <input type="text" name="segundo_apellido" class="form-control" maxlength="70" value="<?php echo $edit_data['segundo_apellido']; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label for="genero" class="form-label">Género:</label>
            <select name="genero" class="form-select" required>
                <option value="Masculino" <?php echo $edit_data['genero'] == 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                <option value="Femenino" <?php echo $edit_data['genero'] == 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="curp" class="form-label">CURP:</label>
            <input type="text" name="curp" class="form-control" required maxlength="18" pattern="^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z][0-9]$" value="<?php echo $edit_data['curp']; ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
            <input type="date" name="fecha_nacimiento" class="form-control" required value="<?php echo $edit_data['fecha_nacimiento']; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label for="pais_nacimiento" class="form-label">País de Nacimiento:</label>
            <input type="text" name="pais_nacimiento" class="form-control" required maxlength="50" value="<?php echo $edit_data['pais_nacimiento']; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label for="entidad_federativa" class="form-label">Entidad Federativa:</label>
            <input type="text" name="entidad_federativa" class="form-control" required maxlength="50" value="<?php echo $edit_data['entidad_federativa']; ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="pais_procedencia" class="form-label">País de Procedencia:</label>
            <input type="text" name="pais_procedencia" class="form-control" maxlength="50" value="<?php echo $edit_data['pais_procedencia']; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label for="idioma" class="form-label">Idioma:</label>
            <input type="text" name="idioma" class="form-control" maxlength="50" value="<?php echo $edit_data['idioma']; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label for="turno" class="form-label">Turno:</label>
            <select name="turno" class="form-select">
                <option value="Matutino" <?php echo $edit_data['turno'] == 'Matutino' ? 'selected' : ''; ?>>Matutino</option>
                <option value="Vespertino" <?php echo $edit_data['turno'] == 'Vespertino' ? 'selected' : ''; ?>>Vespertino</option>
                <option value="Mixto" <?php echo $edit_data['turno'] == 'Mixto' ? 'selected' : ''; ?>>Mixto</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="necesidad_educativa_especial" class="form-label">Necesidad Educativa Especial:</label>
            <select name="necesidad_educativa_especial" class="form-select">
                <option value="Sí" <?php echo $edit_data['necesidad_educativa_especial'] == 'Sí' ? 'selected' : ''; ?>>Sí</option>
                <option value="No" <?php echo $edit_data['necesidad_educativa_especial'] == 'No' ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="antecedente_academico" class="form-label">Antecedente Académico:</label>
            <select name="antecedente_academico" class="form-select" required>
                <option value="Sí" <?php echo $edit_data['antecedente_academico'] == 'Sí' ? 'selected' : ''; ?>>Sí</option>
                <option value="No" <?php echo $edit_data['antecedente_academico'] == 'No' ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
    </div>

    
    <!-- Non-editable fields -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="cct" class="form-label">CCT:</label>
            <input type="text" class="form-control" value="<?php echo $edit_data['cct']; ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
            <label for="matricula_institucional" class="form-label">Matrícula Institucional:</label>
            <input type="text" class="form-control" value="<?php echo $edit_data['matricula_institucional']; ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
            <label for="clave_institucion" class="form-label">Clave Institución:</label>
            <input type="text" class="form-control" value="<?php echo $edit_data['clave_institucion']; ?>" readonly>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="clave_carrera" class="form-label">Clave Carrera:</label>
            <input type="text" class="form-control" value="<?php echo $edit_data['clave_carrera']; ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
            <label for="numero_acuerdo_rvoe" class="form-label">Número Acuerdo RVOE:</label>
            <input type="text" class="form-control" value="<?php echo $edit_data['numero_acuerdo_rvoe']; ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
            <label for="fecha_acuerdo_rvoe" class="form-label">Fecha Acuerdo RVOE:</label>
            <input type="text" class="form-control" value="<?php echo $edit_data['fecha_acuerdo_rvoe']; ?>" readonly>
        </div>
    </div>
    <button type="submit" name="update" class="btn btn-primary">Actualizar Estudiante</button>
</form>
<?php endif; ?>

            <!-- Add more rows for other editable fields -->
            <button type="submit" name="update" class="btn btn-primary">Actualizar Estudiante</button>
        </form>
        <?php endif; ?>

        <!-- Tabla de resultados -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>CURP</th>
                    <th>Año</th>
                    <th>Ciclo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row["id"]."</td>";
                        echo "<td>".$row["nombre"]." ".$row["primer_apellido"]." ".$row["segundo_apellido"]."</td>";
                        echo "<td>".$row["curp"]."</td>";
                        echo "<td>".$row["ano_ciclo_escolar"]."</td>";
                        echo "<td>".$row["ciclo"]."</td>";
                        echo "<td><a href='?edit=".$row["id"]."' class='btn btn-sm btn-warning'>Editar</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No se encontraron resultados</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <footer class="mt-5 text-center">
        <a href="cerrar.php" class="btn btn-danger">Cerrar sesión</a>
    </footer>
</body>
</html>