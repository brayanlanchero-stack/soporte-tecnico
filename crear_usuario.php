
<?php

require_once "auth.php";
exigirRol(["Administrador"]);

require_once "conexion.php";

function escapar($valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

$mensaje = "";
$tipo_mensaje = "";

$nombre = "";
$correo = "";
$rol = "Solicitante";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";
    $rol = $_POST["rol"] ?? "Solicitante";

    $rolesPermitidos = ["Solicitante", "Tecnico"];

    if (
        $nombre === "" ||
        mb_strlen($nombre) > 100 ||
        $correo === "" ||
        mb_strlen($correo) > 150 ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        $contrasena === "" ||
        !in_array($rol, $rolesPermitidos, true)
    ) {
        $mensaje = "Revisa los campos. El nombre, correo y contraseña son obligatorios; el correo debe ser válido.";
        $tipo_mensaje = "error";

    } elseif (strlen($contrasena) < 8) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres.";
        $tipo_mensaje = "error";

    } else {
        $sql = "SELECT id FROM usuarios WHERE correo = ? LIMIT 1";
        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            error_log("Error al preparar validación de correo: " . $conexion->error);
            $mensaje = "No fue posible procesar la solicitud.";
            $tipo_mensaje = "error";
        } else {
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $existente = $stmt->get_result();

            if ($existente->num_rows > 0) {
                $mensaje = "Ya existe una cuenta con ese correo.";
                $tipo_mensaje = "error";
            } else {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);

                $sqlInsert = "INSERT INTO usuarios
                              (nombre, correo, contrasena, rol)
                              VALUES (?, ?, ?, ?)";

                $stmtInsert = $conexion->prepare($sqlInsert);

                if (!$stmtInsert) {
                    error_log("Error al preparar creación de usuario: " . $conexion->error);
                    $mensaje = "No fue posible crear la cuenta.";
                    $tipo_mensaje = "error";
                } else {
                    $stmtInsert->bind_param(
                        "ssss",
                        $nombre,
                        $correo,
                        $hash,
                        $rol
                    );

                    if ($stmtInsert->execute()) {
                        $mensaje = "Usuario creado correctamente.";
                        $tipo_mensaje = "exito";

                        $nombre = "";
                        $correo = "";
                        $rol = "Solicitante";
                    } else {
                        error_log("Error al crear usuario: " . $stmtInsert->error);

                        if ($stmtInsert->errno === 1062) {
                            $mensaje = "Ya existe una cuenta con ese correo.";
                        } else {
                            $mensaje = "No fue posible crear la cuenta.";
                        }

                        $tipo_mensaje = "error";
                    }

                    $stmtInsert->close();
                }
            }

            $stmt->close();
        }
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear usuario</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 25px 15px;
        }

        .contenedor {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #1d4ed8;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 15px 0 7px;
        }

        input, select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        button, .boton {
            display: inline-block;
            margin-top: 20px;
            padding: 11px 16px;
            border: none;
            border-radius: 6px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .mensaje {
            padding: 12px;
            border-radius: 6px;
            margin: 15px 0;
        }

        .exito {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body>
<main class="contenedor">

    <h1>Crear usuario</h1>

    <p>Registra una cuenta nueva para el sistema de soporte.</p>

    <?php if ($mensaje !== ""): ?>
        <p class="mensaje <?= escapar($tipo_mensaje) ?>">
            <?= escapar($mensaje) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="crear_usuario.php">

        <label for="nombre">Nombre completo</label>
        <input
            type="text"
            name="nombre"
            id="nombre"
            maxlength="100"
            value="<?= escapar($nombre) ?>"
            required
        >

        <label for="correo">Correo electrónico</label>
        <input
            type="email"
            name="correo"
            id="correo"
            maxlength="150"
            value="<?= escapar($correo) ?>"
            required
        >

        <label for="contrasena">Contraseña</label>
        <input
            type="password"
            name="contrasena"
            id="contrasena"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <label for="rol">Rol del usuario</label>
        <select name="rol" id="rol" required>
            <option
                value="Solicitante"
                <?= $rol === "Solicitante" ? "selected" : "" ?>
            >
                Solicitante
            </option>

            <option
                value="Tecnico"
                <?= $rol === "Tecnico" ? "selected" : "" ?>
            >
                Técnico
            </option>
        </select>

        <button type="submit">Crear cuenta</button>
    </form>

    <p>
        <a class="boton" href="gestionar_usuarios.php">
            Volver a usuarios
        </a>
    </p>

</main>
</body>
</html>
