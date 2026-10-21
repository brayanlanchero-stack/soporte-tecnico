
<?php

require_once "auth.php";

// Solo el administrador puede acceder a esta página.
exigirRol(["Administrador"]);

require_once "conexion.php";

function escapar($valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

// Consultar los usuarios sin mostrar sus contraseñas.
$sql = "SELECT id, nombre, correo, rol, activo, fecha_creacion
        FROM usuarios
        ORDER BY id DESC";

$resultado = $conexion->query($sql);

if ($resultado === false) {
    error_log("Error al consultar usuarios: " . $conexion->error);
    exit("No fue posible consultar los usuarios.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestión de usuarios</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 25px 15px;
        }

        .contenedor {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #1d4ed8;
        }

        .tabla-contenedor {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #eaf0ff;
        }

        .enlace {
            display: inline-block;
            margin: 20px 15px 0 0;
            color: #1d4ed8;
        }
    </style>
</head>

<body>
<main class="contenedor">

    <h1>Gestión de usuarios</h1>

    <p>
        Bienvenido,
        <strong><?= escapar($_SESSION["nombre"] ?? "Administrador") ?></strong>.
    </p>

    <p>Usuarios registrados: <strong><?= $resultado->num_rows ?></strong></p>

    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado de cuenta</th>
<th>Fecha de creación</th>
<th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($resultado->num_rows > 0): ?>

                    <?php while ($usuario = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= escapar($usuario["id"]) ?></td>
                            <td><?= escapar($usuario["nombre"]) ?></td>
                            <td><?= escapar($usuario["correo"]) ?></td>
                            <td><?= escapar($usuario["rol"]) ?></td>
                            <td>
    <?= (int) $usuario["activo"] === 1
        ? "Activo"
        : "Desactivado" ?>
</td>

<td><?= escapar($usuario["fecha_creacion"]) ?></td>

<td>
    <a href="editar_usuario.php?id=<?= (int) $usuario["id"] ?>">
        Editar
    </a>
</td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                    <td colspan="7">No hay usuarios registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a class="enlace" href="index.php">Volver al inicio</a>

</main>
</body>
</html>

<?php
$resultado->free();
$conexion->close();
?>
