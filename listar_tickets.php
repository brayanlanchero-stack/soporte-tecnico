
<?php
require_once "conexion.php";

function escapar($valor) {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

$estados = [
    "Nuevo",
    "Asignado",
    "En progreso",
    "Pendiente del solicitante",
    "Resuelto",
    "Cerrado",
    "Cancelado"
];

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

$estado = $_GET["estado"] ?? "";
$categoria = $_GET["categoria"] ?? "";
$prioridad = $_GET["prioridad"] ?? "";
$fecha_desde = $_GET["fecha_desde"] ?? "";
$fecha_hasta = $_GET["fecha_hasta"] ?? "";

if (!in_array($estado, array_merge([""], $estados), true)) {
    $estado = "";
}

if (!in_array($categoria, array_merge([""], $categorias), true)) {
    $categoria = "";
}

if (!in_array($prioridad, array_merge([""], $prioridades), true)) {
    $prioridad = "";
}

function fechaValida($fecha) {
    if ($fecha === "") {
        return true;
    }

    $objeto_fecha = DateTime::createFromFormat("!Y-m-d", $fecha);

    return $objeto_fecha !== false
        && $objeto_fecha->format("Y-m-d") === $fecha;
}

if (!fechaValida($fecha_desde)) {
    $fecha_desde = "";
}

if (!fechaValida($fecha_hasta)) {
    $fecha_hasta = "";
}

if (
    $fecha_desde !== "" &&
    $fecha_hasta !== "" &&
    $fecha_desde > $fecha_hasta
) {
    $fecha_desde = "";
    $fecha_hasta = "";
    $error_fechas = "La fecha inicial no puede ser posterior a la fecha final.";
}

$sql = "SELECT
            id,
            solicitante,
            asunto,
            categoria,
            prioridad,
            estado,
            fecha_creacion
        FROM tickets
        WHERE (? = '' OR estado = ?)
          AND (? = '' OR categoria = ?)
          AND (? = '' OR prioridad = ?)
          AND (? = '' OR fecha_creacion >= ?)
          AND (? = '' OR fecha_creacion < DATE_ADD(?, INTERVAL 1 DAY))
        ORDER BY id DESC";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ssssssssss",
    $estado,
    $estado,
    $categoria,
    $categoria,
    $prioridad,
    $prioridad,
    $fecha_desde,
    $fecha_desde,
    $fecha_hasta,
    $fecha_hasta
);

$stmt->execute();

$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Consultar tickets</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 25px 15px;
        }

        .contenedor {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #1d4ed8;
        }

        .filtros {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        select, input {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        button, .boton {
            display: inline-block;
            padding: 11px 16px;
            border: none;
            border-radius: 6px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .secundario {
            background: #475569;
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

        .mensaje {
            padding: 12px;
            background: #fff7ed;
            color: #9a3412;
            border-radius: 6px;
        }

        .enlace {
            display: inline-block;
            margin: 15px 15px 0 0;
            color: #1d4ed8;
        }
    </style>
</head>

<body>
<main class="contenedor">

    <h1>Tickets de soporte técnico</h1>

    <p>Consulta las solicitudes registradas y filtra los resultados.</p>

    <?php if (isset($error_fechas)): ?>
        <p class="mensaje"><?= escapar($error_fechas) ?></p>
    <?php endif; ?>

    <form method="GET" action="listar_tickets.php" class="filtros">

        <div>
            <label for="estado">Estado</label>
            <select name="estado" id="estado">
                <option value="">Todos</option>

                <?php foreach ($estados as $opcion): ?>
                    <option
                        value="<?= escapar($opcion) ?>"
                        <?= $estado === $opcion ? "selected" : "" ?>
                    >
                        <?= escapar($opcion) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="categoria">Categoría</label>
            <select name="categoria" id="categoria">
                <option value="">Todas</option>

                <?php foreach ($categorias as $opcion): ?>
                    <option
                        value="<?= escapar($opcion) ?>"
                        <?= $categoria === $opcion ? "selected" : "" ?>
                    >
                        <?= escapar($opcion) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="prioridad">Prioridad</label>
            <select name="prioridad" id="prioridad">
                <option value="">Todas</option>

                <?php foreach ($prioridades as $opcion): ?>
                    <option
                        value="<?= escapar($opcion) ?>"
                        <?= $prioridad === $opcion ? "selected" : "" ?>
                    >
                        <?= escapar($opcion) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="fecha_desde">Fecha desde</label>
            <input
                type="date"
                name="fecha_desde"
                id="fecha_desde"
                value="<?= escapar($fecha_desde) ?>"
            >
        </div>

        <div>
            <label for="fecha_hasta">Fecha hasta</label>
            <input
                type="date"
                name="fecha_hasta"
                id="fecha_hasta"
                value="<?= escapar($fecha_hasta) ?>"
            >
        </div>

        <div>
            <label>&nbsp;</label>
            <button type="submit">Filtrar tickets</button>
            <a class="boton secundario" href="listar_tickets.php">Limpiar</a>
        </div>

    </form>

    <h2>Resultados</h2>

    <p>
        Cantidad de tickets encontrados:
        <strong><?= $resultado->num_rows ?></strong>
    </p>

    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Solicitante</th>
                    <th>Asunto</th>
                    <th>Categoría</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Fecha de creación</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($resultado->num_rows > 0): ?>

                    <?php while ($ticket = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= escapar($ticket["id"]) ?></td>
                            <td><?= escapar($ticket["solicitante"]) ?></td>
                            <td><?= escapar($ticket["asunto"]) ?></td>
                            <td><?= escapar($ticket["categoria"]) ?></td>
                            <td><?= escapar($ticket["prioridad"]) ?></td>
                            <td><?= escapar($ticket["estado"]) ?></td>
                            <td><?= escapar($ticket["fecha_creacion"]) ?></td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            No se encontraron tickets con esos filtros.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a class="enlace" href="registrar_ticket.php">
        Registrar otro ticket
    </a>

    <a class="enlace" href="index.php">
        Volver al inicio
    </a>

</main>
</body>
</html>

<?php
$stmt->close();
$conexion->close();
?>
