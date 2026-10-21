
<?php
require_once "auth.php";
exigirRol(["Administrador", "Tecnico"]);

require_once "conexion.php";

function escapar($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

$rol = $_SESSION["rol"];
$usuario_id = (int) $_SESSION["usuario_id"];
$mensaje = "";
$error = "";

$estadosPermitidos = [
    "Asignado",
    "En progreso",
    "Pendiente del solicitante",
    "Resuelto",
    "Cerrado"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ticket_id = filter_input(
        INPUT_POST,
        "ticket_id",
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $nuevo_estado = $_POST["estado"] ?? "";

    if (!$ticket_id || !in_array($nuevo_estado, $estadosPermitidos, true)) {
        $error = "Selecciona un ticket y un estado válidos.";
    } else {
        // Buscar el ticket y verificar los permisos.
        $sql = "SELECT id, estado
                FROM tickets
                WHERE id = ?";

        if ($rol === "Tecnico") {
            $sql .= " AND tecnico_id = ?";
        }

        $stmt = $conexion->prepare($sql);

        if ($rol === "Tecnico") {
            $stmt->bind_param("ii", $ticket_id, $usuario_id);
        } else {
            $stmt->bind_param("i", $ticket_id);
        }

        $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$ticket) {
            $error = "El ticket no existe o no tienes permiso para modificarlo.";
        } elseif (in_array(
            $ticket["estado"],
            ["Cerrado", "Cancelado"],
            true
        )) {
            $error = "No se puede modificar un ticket cerrado o cancelado.";
        
} elseif (
    $ticket["estado"] === "Resuelto"
    && $nuevo_estado !== "Resuelto"
    && $nuevo_estado !== "Cerrado"
) {
    $error = "Un ticket resuelto solo puede permanecer resuelto o pasar a cerrado.";
} elseif (
    $nuevo_estado === "Cerrado"
    && $ticket["estado"] !== "Resuelto"
) {
    $error = "Solo se puede cerrar un ticket que esté resuelto.";
} else {

            $sqlActualizar = "UPDATE tickets
                  SET estado = ?,
                      fecha_resolucion = CASE
                          WHEN ? = 'Resuelto' THEN NOW()
                          ELSE fecha_resolucion
                      END,
                      fecha_cierre = CASE
                          WHEN ? = 'Cerrado' THEN NOW()
                          ELSE fecha_cierre
                      END
                  WHERE id = ?";

            if ($rol === "Tecnico") {
                $sqlActualizar .= " AND tecnico_id = ?";
            }

         if ($rol === "Tecnico") {
    $stmtActualizar->bind_param(
        "sssii",
        $nuevo_estado,
        $nuevo_estado,
        $nuevo_estado,
        $ticket_id,
        $usuario_id
    );
} else {
    $stmtActualizar->bind_param(
        "sssi",
        $nuevo_estado,
        $nuevo_estado,
        $nuevo_estado,
        $ticket_id
    );
}
        }
    }
}

// Listar tickets que el usuario puede gestionar.
$sqlLista = "SELECT id, asunto, estado
             FROM tickets
             WHERE estado NOT IN ('Cerrado', 'Cancelado')";

if ($rol === "Tecnico") {
    $sqlLista .= " AND tecnico_id = ?";
}

$sqlLista .= " ORDER BY id DESC";

$stmtLista = $conexion->prepare($sqlLista);

if ($rol === "Tecnico") {
    $stmtLista->bind_param("i", $usuario_id);
}

$stmtLista->execute();
$tickets = $stmtLista->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtLista->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar estado del ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 35px auto;
            padding: 0 16px;
            background: #f1f5f9;
            color: #1e293b;
        }

        main {
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
        }

        select, button {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            margin-top: 7px;
        }

        button {
            margin-top: 22px;
            background: #2563eb;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }

        .mensaje { color: #166534; }
        .error { color: #b91c1c; }
    </style>
</head>
<body>
<main>
    <h1>Actualizar estado del ticket</h1>

    <p><a href="index.php">Volver al menú principal</a></p>

    <?php if ($mensaje !== ""): ?>
        <p class="mensaje"><?= escapar($mensaje) ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error"><?= escapar($error) ?></p>
    <?php endif; ?>

    <?php if (count($tickets) === 0): ?>
        <p>No hay tickets disponibles para gestionar.</p>
    <?php else: ?>
        <form method="POST" action="actualizar_estado.php">
            <label for="ticket_id">Selecciona el ticket</label>
            <select name="ticket_id" id="ticket_id" required>
                <option value="">-- Selecciona un ticket --</option>
                <?php foreach ($tickets as $ticket): ?>
                    <option value="<?= (int) $ticket["id"] ?>">
                        <?= "#" . (int) $ticket["id"] . " - "
                            . escapar($ticket["asunto"])
                            . " (" . escapar($ticket["estado"]) . ")" ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="estado">Nuevo estado</label>
            <select name="estado" id="estado" required>
                <option value="">-- Selecciona un estado --</option>
                <?php foreach ($estadosPermitidos as $estado): ?>
                    <option value="<?= escapar($estado) ?>">
                        <?= escapar($estado) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Guardar estado</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
