
<?php

require_once "auth.php";
exigirRol(["Administrador"]);

require_once "conexion.php";

function escapar($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

$mensaje = "";
$tipo_mensaje = "";

$usuario_id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario_id = filter_input(
        INPUT_POST,
        "id",
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $accion = $_POST["accion"] ?? "";

    if (!$usuario_id) {
        $mensaje = "El identificador del usuario no es válido.";
        $tipo_mensaje = "error";
    } elseif ($usuario_id === (int) ($_SESSION["usuario_id"] ?? 0)) {
        $mensaje = "No puedes modificar tu propia cuenta desde este módulo.";
        $tipo_mensaje = "error";
    } elseif ($accion === "cambiar_rol") {
        $nuevo_rol = $_POST["rol"] ?? "";

        if (!in_array($nuevo_rol, ["Solicitante", "Tecnico"], true)) {
            $mensaje = "El rol seleccionado no es válido.";
            $tipo_mensaje = "error";
        } else {
            $sql = "UPDATE usuarios
                    SET rol = ?
                    WHERE id = ?
                      AND rol <> 'Administrador'";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("si", $nuevo_rol, $usuario_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $mensaje = "El rol del usuario se actualizó correctamente.";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "No se actualizó el rol. Verifica que la cuenta exista y no sea administradora.";
                $tipo_mensaje = "error";
            }

            $stmt->close();
        }
    } elseif ($accion === "cambiar_estado") {
        $nuevo_estado = filter_input(
            INPUT_POST,
            "activo",
            FILTER_VALIDATE_INT
        );

        if (!in_array($nuevo_estado, [0, 1], true)) {
            $mensaje = "El estado seleccionado no es válido.";
            $tipo_mensaje = "error";
        } else {
            $sql = "UPDATE usuarios
                    SET activo = ?
                    WHERE id = ?
                      AND rol <> 'Administrador'";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ii", $nuevo_estado, $usuario_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $mensaje = $nuevo_estado === 1
                    ? "La cuenta se reactivó correctamente."
                    : "La cuenta se desactivó correctamente.";

                $tipo_mensaje = "exito";
            } else {
                $mensaje = "No se cambió el estado. Verifica que la cuenta exista y no sea administradora.";
                $tipo_mensaje = "error";
            }

            $stmt->close();
        }
    } else {
        $mensaje = "La operación solicitada no es válida.";
        $tipo_mensaje = "error";
    }
}

$usuario = null;

if ($usuario_id) {
    $sql = "SELECT id, nombre, correo, rol, activo
            FROM usuarios
            WHERE id = ?
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    $stmt->close();
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 25px 15px;
        }

        .contenedor {
            max-width: 650px;
            margin: auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .08);
        }

        h1 {
            color: #1d4ed8;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 15px 0 7px;
        }

        select, button {
            padding: 10px;
            border-radius: 6px;
        }

        select {
            width: 100%;
            border: 1px solid #cbd5e1;
        }

        button {
            margin-top: 12px;
            border: none;
            background: #1d4ed8;
            color: white;
            cursor: pointer;
        }

        .mensaje {
            padding: 12px;
            border-radius: 6px;
        }

        .exito {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .enlace {
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>

<body>
<main class="contenedor">

    <h1>Editar usuario</h1>

    <?php if ($mensaje !== ""): ?>
        <p class="mensaje <?= escapar($tipo_mensaje) ?>">
            <?= escapar($mensaje) ?>
        </p>
    <?php endif; ?>

    <?php if ($usuario): ?>

        <p><strong>Nombre:</strong> <?= escapar($usuario["nombre"]) ?></p>
        <p><strong>Correo:</strong> <?= escapar($usuario["correo"]) ?></p>
        <p><strong>Rol actual:</strong> <?= escapar($usuario["rol"]) ?></p>
        <p>
            <strong>Estado:</strong>
            <?= (int) $usuario["activo"] === 1 ? "Activo" : "Desactivado" ?>
        </p>

        <?php if (
            (int) $usuario["id"] !== (int) ($_SESSION["usuario_id"] ?? 0)
            && $usuario["rol"] !== "Administrador"
        ): ?>

            <h2>Cambiar rol</h2>

            <form method="POST" action="editar_usuario.php">
                <input type="hidden" name="id" value="<?= (int) $usuario["id"] ?>">
                <input type="hidden" name="accion" value="cambiar_rol">

                <label for="rol">Selecciona el nuevo rol</label>
                <select name="rol" id="rol" required>
                    <option value="Solicitante"
                        <?= $usuario["rol"] === "Solicitante" ? "selected" : "" ?>>
                        Solicitante
                    </option>

                    <option value="Tecnico"
                        <?= $usuario["rol"] === "Tecnico" ? "selected" : "" ?>>
                        Técnico
                    </option>
                </select>

                <button type="submit">Guardar rol</button>
            </form>

            <h2>Estado de la cuenta</h2>

            <form method="POST" action="editar_usuario.php">
                <input type="hidden" name="id" value="<?= (int) $usuario["id"] ?>">
                <input type="hidden" name="accion" value="cambiar_estado">

                <label for="activo">Selecciona el estado</label>
                <select name="activo" id="activo" required>
                    <option value="1"
                        <?= (int) $usuario["activo"] === 1 ? "selected" : "" ?>>
                        Activo
                    </option>

                    <option value="0"
                        <?= (int) $usuario["activo"] === 0 ? "selected" : "" ?>>
                        Desactivado
                    </option>
                </select>

                <button type="submit">Guardar estado</button>
            </form>

        <?php else: ?>
            <p>Esta cuenta no se puede modificar desde este módulo.</p>
        <?php endif; ?>

    <?php else: ?>
        <p>No se encontró el usuario solicitado.</p>
    <?php endif; ?>

    <a class="enlace" href="gestionar_usuarios.php">Volver al listado</a>

</main>
</body>
</html>
