
<?php
require_once "auth.php";
exigirLogin();

require_once "conexion.php";

function escapar($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

$usuario_id = (int) $_SESSION["usuario_id"];
$rol = $_SESSION["rol"] ?? "";
$mensaje = "";
$error = "";

$ticket_id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ticket_id = filter_input(
        INPUT_POST,
        "ticket_id",
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );
    $comentario = trim($_POST["comentario"] ?? "");

    if (!$ticket_id) {
        $error = "El ticket seleccionado no es válido.";
    } elseif ($comentario === "") {
        $error = "Escribe un comentario antes de enviarlo.";
    } elseif (mb_strlen($comentario) > 5000) {
        $error = "El comentario no puede superar los 5000 caracteres.";
    } else {
        // Verificar que el usuario tenga permiso para acceder al ticket.
        $sqlPermiso = "SELECT id, estado
                       FROM tickets
                       WHERE id = ?";

        if ($rol === "Solicitante") {
            $sqlPermiso .= " AND solicitante_id = ?";
        } elseif ($rol === "Tecnico") {
            $sqlPermiso .= " AND tecnico_id = ?";
        } elseif ($rol !== "Administrador") {
            $sqlPermiso .= " AND 1 = 0";
        }

        $stmtPermiso = $conexion->prepare($sqlPermiso);

        if ($rol === "Administrador") {
            $stmtPermiso->bind_param("i", $ticket_id);
        } else {
            $stmtPermiso->bind_param("ii", $ticket_id, $usuario_id);
        }

        $stmtPermiso->execute();
        $ticket = $stmtPermiso->get_result()->fetch_assoc();
        $stmtPermiso->close();

        if (!$ticket) {
            $error = "El ticket no existe o no tienes permiso para acceder a él.";
        } elseif (in_array($ticket["estado"], ["Cerrado", "Cancelado"], true)) {
            $error = "No se pueden agregar comentarios a tickets cerrados o cancelados.";
        } else {
            $sqlInsertar = "INSERT INTO comentarios_tickets
                            (ticket_id, usuario_id, comentario)
                            VALUES (?, ?, ?)";

            $stmtInsertar = $conexion->prepare($sqlInsertar);
            $stmtInsertar->bind_param(
                "iis",
                $ticket_id,
                $usuario_id,
                $comentario
            );

            if ($stmtInsertar->execute()) {
                $stmtInsertar->close();
                header("Location: comentar_ticket.php?id=" . $ticket_id);
                exit;
            } else {
                $error = "No fue posible guardar el comentario.";
                $stmtInsertar->close();
            }
        }
    }
}

// Consultar el ticket y comprobar permisos antes de mostrar sus datos.
$ticket = null;

if ($ticket_id) {
    $sqlTicket = "SELECT id, asunto, descripcion, estado, solicitante_id, tecnico_id
                  FROM tickets
                  WHERE id = ?";

    if ($rol === "Solicitante") {
        $sqlTicket .= " AND solicitante_id = ?";
    } elseif ($rol === "Tecnico") {
        $sqlTicket .= " AND tecnico_id = ?";
    } elseif ($rol !== "Administrador") {
        $sqlTicket .= " AND 1 = 0";
    }

    $stmtTicket = $conexion->prepare($sqlTicket);

    if ($rol === "Administrador") {
        $stmtTicket->bind_param("i", $ticket_id);
    } else {
        $stmtTicket->bind_param("ii", $ticket_id, $usuario_id);
    }

    $stmtTicket->execute();
    $ticket = $stmtTicket->get_result()->fetch_assoc();
    $stmtTicket->close();
}

$comentarios = [];

if ($ticket) {
    $sqlComentarios = "SELECT c.comentario, c.fecha_comentario, u.nombre, u.rol
                       FROM comentarios_tickets c
                       INNER JOIN usuarios u ON u.id = c.usuario_id
                       WHERE c.ticket_id = ?
                       ORDER BY c.fecha_comentario ASC, c.id ASC";

    $stmtComentarios = $conexion->prepare($sqlComentarios);
    $stmtComentarios->bind_param("i", $ticket_id);
    $stmtComentarios->execute();
    $comentarios = $stmtComentarios->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtComentarios->close();
}

$puedeComentar = $ticket
    && !in_array($ticket["estado"], ["Cerrado", "Cancelado"], true);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentarios del ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
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

        textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            min-height: 120px;
        }

        button {
            margin-top: 12px;
            padding: 11px 16px;
            border: 0;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            cursor: pointer;
        }

        .comentario {
            margin: 16px 0;
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .mensaje { color: #166534; }
        .error { color: #b91c1c; }
        .secundario { color: #1d4ed8; }
    </style>
</head>
<body>
<main>
    <h1>Comentarios del ticket</h1>
    <p><a href="index.php">Volver al menú principal</a></p>

    <?php if ($error !== ""): ?>
        <p class="error"><?= escapar($error) ?></p>
    <?php endif; ?>

    <?php if (!$ticket): ?>
        <p>No se encontró el ticket o no tienes permiso para consultarlo.</p>
        <p>Abre esta página desde un ticket válido usando su identificador.</p>
    <?php else: ?>
        <h2>
            Ticket #<?= (int) $ticket["id"] ?>:
            <?= escapar($ticket["asunto"]) ?>
        </h2>

        <p><strong>Estado:</strong> <?= escapar($ticket["estado"]) ?></p>
        <p><strong>Descripción:</strong></p>
        <p><?= nl2br(escapar($ticket["descripcion"])) ?></p>

        <h2>Historial de comentarios</h2>

        <?php if (count($comentarios) === 0): ?>
            <p>Aún no hay comentarios para este ticket.</p>
        <?php else: ?>
            <?php foreach ($comentarios as $item): ?>
                <article class="comentario">
                    <strong><?= escapar($item["nombre"]) ?></strong>
                    (<?= escapar($item["rol"]) ?>)
                    <p><?= nl2br(escapar($item["comentario"])) ?></p>
                    <small><?= escapar($item["fecha_comentario"]) ?></small>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($puedeComentar): ?>
            <h2>Agregar comentario</h2>

            <form method="POST" action="comentar_ticket.php">
                <input
                    type="hidden"
                    name="ticket_id"
                    value="<?= (int) $ticket["id"] ?>"
                >

                <label for="comentario">Escribe tu comentario</label>
                <textarea
                    id="comentario"
                    name="comentario"
                    maxlength="5000"
                    required
                ></textarea>

                <button type="submit">Guardar comentario</button>
            </form>
        <?php else: ?>
            <p class="secundario">
                Este ticket está cerrado o cancelado y no admite nuevos comentarios.
            </p>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>

<?php
$conexion->close();
?>

