
<?php

require_once "auth.php";

$nombreUsuario = $_SESSION["nombre"] ?? null;
$rolUsuario = $_SESSION["rol"] ?? null;
$haIniciadoSesion = !empty($_SESSION["usuario_id"]);

function escapar($valor): string {
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

    <title>Sistema de soporte técnico</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 30px 16px;
            color: #1e293b;
        }

        .contenedor {
            max-width: 850px;
            margin: 30px auto;
            background: white;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            text-align: center;
        }

        .sesion {
            padding: 14px;
            background: #e0f2fe;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .opciones {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .opcion {
            padding: 20px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        a {
            color: #2563eb;
            font-weight: bold;
        }

        .salir {
            display: inline-block;
            margin-top: 12px;
            color: #b91c1c;
        }
    </style>
</head>

<body>
<main class="contenedor">

    <h1>Sistema de gestión de soporte técnico</h1>

    <?php if ($haIniciadoSesion): ?>
        <div class="sesion">
            <strong>Sesión iniciada</strong><br>

            Usuario: <?= escapar($nombreUsuario ?? "") ?><br>
            Rol: <?= escapar($rolUsuario ?? "") ?><br>

            <a class="salir" href="cerrar_sesion.php">
                Cerrar sesión
            </a>
        </div>

        <section class="opciones">

            <div class="opcion">
                <h2>Registrar ticket</h2>
                <p>Crear una solicitud de soporte técnico.</p>
                <a href="registrar_ticket.php">Abrir formulario</a>
            </div>

            <div class="opcion">
                <h2>Consultar tickets</h2>
                <p>Consultar solicitudes y utilizar los filtros disponibles.</p>
                <a href="listar_tickets.php">Ver tickets</a>
            </div>

            <?php if ($rolUsuario === "Administrador"): ?>

                <div class="opcion">
                    <h2>Gestión de usuarios</h2>
                    <p>Consultar las cuentas registradas.</p>
                    <a href="gestionar_usuarios.php">Administrar usuarios</a>
                </div>

                <div class="opcion">
                    <h2>Crear usuario</h2>
                    <p>Registrar una cuenta de solicitante o técnico.</p>
                    <a href="crear_usuario.php">Crear cuenta</a>
                </div>

<a href="asignar_ticket.php">Asignar tickets a técnicos</a>

<?php endif; ?>

<?php if (in_array($rolUsuario, ["Administrador", "Tecnico"], true)): ?>
    <div class="opcion">
        <h2>Actualizar estado</h2>
        <p>Consultar los tickets autorizados y actualizar su estado.</p>
        <a href="actualizar_estado.php">Actualizar estados de tickets</a>
    </div>
    <div class="opcion">
    <h2>Cancelar ticket</h2>
    <p>Cancelar un ticket activo indicando el motivo.</p>
    <a href="cancelar_ticket.php">Cancelar tickets</a>
</div>
<?php endif; ?>


        </section>

    <?php else: ?>

        <p>
            No has iniciado sesión.
            <a href="login.php">Iniciar sesión</a>
        </p>

    <?php endif; ?>

</main>
</body>
</html>
