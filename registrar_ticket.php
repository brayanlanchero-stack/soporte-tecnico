
<?php
require_once "conexion.php";

$mensaje = "";
$tipo_mensaje = "";

$solicitante = "";
$asunto = "";
$descripcion = "";
$categoria = "";
$prioridad = "Media";

$categorias = [
    "Hardware",
    "Software",
    "Redes",
    "Impresoras",
    "Otros"
];

$prioridades = [
    "Baja",
    "Media",
    "Alta",
    "Crítica"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $solicitante = trim($_POST["solicitante"] ?? "");
    $asunto = trim($_POST["asunto"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $categoria = $_POST["categoria"] ?? "";
    $prioridad = $_POST["prioridad"] ?? "Media";

    if (
        $solicitante === "" ||
        $asunto === "" ||
        $descripcion === "" ||
        !in_array($categoria, $categorias, true) ||
        !in_array($prioridad, $prioridades, true)
    ) {
        $mensaje = "Completa los campos obligatorios y selecciona opciones válidas.";
        $tipo_mensaje = "error";

    } elseif (
        mb_strlen($solicitante) > 100 ||
        mb_strlen($asunto) > 150
    ) {
        $mensaje = "El solicitante admite hasta 100 caracteres y el asunto hasta 150.";
        $tipo_mensaje = "error";

    } else {
        $sql = "INSERT INTO tickets
                (solicitante, asunto, descripcion, categoria, prioridad)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param(
                "sssss",
                $solicitante,
                $asunto,
                $descripcion,
                $categoria,
                $prioridad
            );

            if ($stmt->execute()) {
                $id_ticket = $conexion->insert_id;

                $mensaje = "Ticket registrado correctamente. Número de ticket: "
                         . $id_ticket;
                $tipo_mensaje = "exito";

                $solicitante = "";
                $asunto = "";
                $descripcion = "";
                $categoria = "";
                $prioridad = "Media";
            } else {
                error_log("Error al registrar ticket: " . $stmt->error);
                $mensaje = "No fue posible registrar el ticket. Intenta nuevamente.";
                $tipo_mensaje = "error";
            }

            $stmt->close();
        } else {
            error_log("Error al preparar el registro: " . $conexion->error);
            $mensaje = "No fue posible procesar la solicitud.";
            $tipo_mensaje = "error";
        }
    }
}

function escapar($valor) {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar ticket - Soporte técnico</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 30px 15px;
            color: #1e293b;
        }

        .contenedor {
            max-width: 650px;
            margin: auto;
            background: white;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #1d4ed8;
            margin-top: 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 17px;
        }

        input, textarea, select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            margin-top: 7px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        button {
            margin-top: 22px;
            padding: 12px 18px;
            background: #1d4ed8;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #1e40af;
        }

        .mensaje {
            padding: 12px;
            border-radius: 6px;
            margin: 18px 0;
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
            margin-right: 15px;
            color: #1d4ed8;
        }
    </style>
</head>

<body>
    <main class="contenedor">
        <h1>Registrar ticket de soporte</h1>

        <p>Describe el problema para registrar una nueva solicitud.</p>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje <?= escapar($tipo_mensaje) ?>">
                <?= escapar($mensaje) ?>
            </div>
        <?php endif; ?>

        <form action="registrar_ticket.php" method="POST">

            <label for="solicitante">Nombre del solicitante *</label>
            <input
                type="text"
                id="solicitante"
                name="solicitante"
                maxlength="100"
                required
                value="<?= escapar($solicitante) ?>"
            >

            <label for="asunto">Asunto *</label>
            <input
                type="text"
                id="asunto"
                name="asunto"
                maxlength="150"
                required
                value="<?= escapar($asunto) ?>"
            >

            <label for="descripcion">Descripción del problema *</label>
            <textarea
                id="descripcion"
                name="descripcion"
                required
            ><?= escapar($descripcion) ?></textarea>

            <label for="categoria">Categoría *</label>
            <select id="categoria" name="categoria" required>
                <option value="">Selecciona una categoría</option>

                <?php foreach ($categorias as $opcion): ?>
                    <option
                        value="<?= escapar($opcion) ?>"
                        <?= $categoria === $opcion ? "selected" : "" ?>
                    >
                        <?= escapar($opcion) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="prioridad">Prioridad *</label>
            <select id="prioridad" name="prioridad" required>
                <?php foreach ($prioridades as $opcion): ?>
                    <option
                        value="<?= escapar($opcion) ?>"
                        <?= $prioridad === $opcion ? "selected" : "" ?>
                    >
                        <?= escapar($opcion) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Registrar ticket</button>
        </form>

        <a class="enlace" href="index.php">Volver al inicio</a>
    </main>
</body>
</html>
