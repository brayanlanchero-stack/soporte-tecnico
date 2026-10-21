
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de soporte técnico</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        header {
            background: #1d4ed8;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        header h1 {
            margin: 0 0 10px;
        }

        header p {
            margin: 0;
        }

        main {
            max-width: 900px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .opciones {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .tarjeta {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .tarjeta h2 {
            color: #1d4ed8;
            margin-top: 0;
        }

        .boton {
            display: inline-block;
            padding: 12px 16px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 10px;
        }

        .boton:hover {
            background: #1e40af;
        }

        .estado {
            margin-top: 25px;
            background: #dcfce7;
            color: #166534;
            padding: 16px;
            border-radius: 8px;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #64748b;
        }
    </style>
</head>

<body>

<header>
    <h1>Sistema de gestión de soporte técnico</h1>
    <p>Registra y consulta las solicitudes de soporte tecnológico.</p>
</header>

<main>
    <h2>Menú principal</h2>

    <div class="opciones">

        <section class="tarjeta">
            <h2>Registrar ticket</h2>
            <p>Registra un incidente indicando el asunto, la descripción, la categoría y la prioridad.</p>

            <a class="boton" href="registrar_ticket.php">
                Registrar solicitud
            </a>
        </section>

        <section class="tarjeta">
            <h2>Consultar tickets</h2>
            <p>Consulta las solicitudes registradas y utiliza filtros para encontrar tickets.</p>

            <a class="boton" href="listar_tickets.php">
                Ver tickets
            </a>
        </section>

    </div>

    <div class="estado">
        <strong>Estado del proyecto:</strong>
        el registro y la consulta de tickets están implementados.
    </div>
</main>

<footer>
    Proyecto académico de gestión de solicitudes de soporte técnico
</footer>

</body>
</html>
