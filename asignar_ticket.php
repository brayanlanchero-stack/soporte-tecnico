
<?php
require_once "auth.php";
exigirRol(["Administrador"]);

require_once "conexion.php";

$mensaje = "";
$error = "";

// Consultar técnicos activos
$sqlTecnicos = "SELECT id, nombre
                FROM usuarios
                WHERE rol = 'Tecnico' AND activo = 1
                ORDER BY nombre";

$resultadoTecnicos = $conexion->query($sqlTecnicos);
$tecnicos = $resultadoTecnicos->fetch_all(MYSQLI_ASSOC);

// Procesar la asignación
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ticket_id = filter_input(
        INPUT_POST,
        "ticket_id",
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $tecnico_id = filter_input(
        INPUT_POST,
        "tecnico_id",
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if (!$ticket_id || !$tecnico_id) {
        $error = "Selecciona un ticket y un técnico válidos.";
    } else {
        // Comprobar que el técnico exista, esté activo y tenga ese rol
        $sqlValidar = "SELECT id
                       FROM usuarios
                       WHERE id = ?
                         AND rol = 'Tecnico'
                         AND activo = 1";

        $stmtValidar = $conexion->prepare($sqlValidar);
        $stmtValidar->bind_param("i", $tecnico_id);
        $stmtValidar->execute();
        $tecnicoValido = $stmtValidar->get_result()->fetch_assoc();
        $stmtValidar->close();

        if (!$tecnicoValido) {
            $error = "El técnico seleccionado no existe o está inactivo.";
        } else {
            // Comprobar que el ticket exista y no esté cerrado o cancelado
            $sqlTicket = "SELECT id, estado
                          FROM tickets
                          WHERE id = ?";

            $stmtTicket = $conexion->prepare($sqlTicket);
            $stmtTicket->bind_param("i", $ticket_id);
            $stmtTicket->execute();
            $ticket = $stmtTicket->get_result()->fetch_assoc();
            $stmtTicket->close();

            if (!$ticket) {
                $error = "El ticket seleccionado no existe.";
            } elseif (in_array(
                $ticket["estado"],
                ["Cerrado", "Cancelado"],
                true
            )) {
                $error = "No se puede asignar un ticket cerrado o cancelado.";
            } else {
                $administrador_id = (int) $_SESSION["usuario_id"];

                $sqlAsignar = "UPDATE tickets
                               SET tecnico_id = ?,
                                   asignado_por = ?,
                                   fecha_asignacion = NOW(),
                                   estado = 'Asignado'
                               WHERE id = ?";

                $stmtAsignar = $conexion->prepare($sqlAsignar);
                $stmtAsignar->bind_param(
                    "iii",
                    $tecnico_id,
                    $administrador_id,
                    $ticket_id
                );

                if ($stmtAsignar->execute()) {
                    $mensaje = "Ticket asignado correctamente.";
                } else {
                    $error = "No fue posible asignar el ticket.";
                }

                $stmtAsignar->close();
            }
        }
    }
}

// Consultar tickets disponibles para asignar
$sqlTickets = "SELECT id, asunto, estado
               FROM tickets
               WHERE estado NOT IN ('Cerrado', 'Cancelado')
               ORDER BY id DESC";

$resultadoTickets = $conexion->query($sqlTickets);
$tickets = $resultadoTickets->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar tickets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 35px auto;
            padding: 0 16px;
            background: #f1f5f9;
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
            box-sizing: border-box;
            width: 100%;
            padding: 10px;
            margin-top: 7px;
        }

        button {
            margin-top: 22px;
            color: white;
            background: #2563eb;
            border: 0;
            border-radius: 5px;
            cursor: pointer;
        }

        .mensaje {
            color: #166534;
        }

        .error {
            color: #b91c1c;
        }
    </style>
</head>
<body>
<main>
    <h1>Asignar ticket a un técnico</h1>

    <p><a href="index.php">Volver al menú principal</a></p>

    <?php if ($mensaje !== ""): ?>
        <p class="mensaje">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <?php if (count($tecnicos) === 0): ?>
        <p>No hay técnicos activos disponibles para asignar.</p>
    <?php elseif (count($tickets) === 0): ?>
        <p>No hay tickets disponibles para asignar.</p>
    <?php else: ?>
        <form method="POST" action="asignar_ticket.php">
            <label for="ticket_id">Selecciona el ticket</label>
            <select id="ticket_id" name="ticket_id" required>
                <option value="">-- Selecciona un ticket --</option>
                <?php foreach ($tickets as $ticket): ?>
                    <option value="<?= (int) $ticket["id"] ?>">
                        <?= "#" . (int) $ticket["id"] . " - " .
                            htmlspecialchars(
                                $ticket["asunto"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) .
                            " (" .
                            htmlspecialchars(
                                $ticket["estado"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) . ")" ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="tecnico_id">Selecciona el técnico</label>
            <select id="tecnico_id" name="tecnico_id" required>
                <option value="">-- Selecciona un técnico --</option>
                <?php foreach ($tecnicos as $tecnico): ?>
                    <option value="<?= (int) $tecnico["id"] ?>">
                        <?= htmlspecialchars(
                            $tecnico["nombre"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Asignar ticket</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
