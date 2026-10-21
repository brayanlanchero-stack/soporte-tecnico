
<?php
require_once "auth.php";
exigirRol(["Administrador"]);

require_once "conexion.php";

function escapar($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ticket_id = filter_input(
        INPUT_POST,
        "ticket_id",
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $motivo = trim($_POST["motivo_cancelacion"] ?? "");

    if (!$ticket_id) {
        $error = "Selecciona un ticket válido.";
    } elseif ($motivo === "") {
        $error = "Debes escribir el motivo de la cancelación.";
    } else {
        $sql = "SELECT id, estado
                FROM tickets
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$ticket) {
            $error = "El ticket seleccionado no existe.";
        } elseif (in_array(
            $ticket["estado"],
            ["Resuelto", "Cerrado", "Cancelado"],
            true
        )) {
            $error = "No se puede cancelar un ticket resuelto, cerrado o ya cancelado.";
        } else {
            $sql = "UPDATE tickets
                    SET estado = 'Cancelado',
                        motivo_cancelacion = ?
                    WHERE id = ?";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("si", $motivo, $ticket_id);

            if ($stmt->execute() && $stmt->affected_rows >= 0) {
                $mensaje = "El ticket se canceló correctamente.";
            } else {
                $error = "No fue posible cancelar el ticket.";
            }

            $stmt->close();
        }
    }
}

$sqlLista = "SELECT id, asunto, estado
             FROM tickets
             WHERE estado NOT IN ('Resuelto', 'Cerrado', 'Cancelado')
             ORDER BY id DESC";

$resultado = $conexion->query($sqlLista);
$tickets = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar ticket</title>
</head>
<body>
    <h1>Cancelar ticket</h1>

    <p><a href="index.php">Volver al menú principal</a></p>

    <?php if ($mensaje !== ""): ?>
        <p style="color: green;"><?= escapar($mensaje) ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p style="color: red;"><?= escapar($error) ?></p>
    <?php endif; ?>

    <?php if (count($tickets) === 0): ?>
        <p>No hay tickets disponibles para cancelar.</p>
    <?php else: ?>
        <form method="POST" action="cancelar_ticket.php">
            <label for="ticket_id">Selecciona el ticket:</label>
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

            <br><br>

            <label for="motivo_cancelacion">Motivo de cancelación:</label>
            <br>
            <textarea
                name="motivo_cancelacion"
                id="motivo_cancelacion"
                rows="4"
                required
            ></textarea>

            <br><br>

            <button type="submit">Cancelar ticket</button>
        </form>
    <?php endif; ?>
</body>
</html>
