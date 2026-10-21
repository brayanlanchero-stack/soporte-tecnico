
<?php
session_start();

require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST["correo"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";

    if ($correo === "" || $contrasena === "") {
        $mensaje = "Debes ingresar el correo y la contraseña.";
    } else {
        $sql = "SELECT id, nombre, correo, contrasena, rol, activo
                FROM usuarios
                WHERE correo = ?
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();

        if (
            $usuario &&
            (int) $usuario["activo"] === 1 &&
            password_verify($contrasena, $usuario["contrasena"])
        ) {
            session_regenerate_id(true);

            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["correo"] = $usuario["correo"];
            $_SESSION["rol"] = $usuario["rol"];

            header("Location: index.php");
            exit;
        }

        $mensaje = "Correo o contraseña incorrectos, o cuenta inactiva.";
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 40px 16px;
        }

        .contenedor {
            max-width: 400px;
            margin: 40px auto;
            background: white;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            text-align: center;
            color: #1e293b;
        }

        label {
            display: block;
            margin-top: 16px;
            font-weight: bold;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 12px;
            border: 0;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .mensaje {
            margin-top: 16px;
            color: #b91c1c;
            text-align: center;
        }
    </style>
</head>
<body>
    <main class="contenedor">
        <h1>Iniciar sesión</h1>

        <form method="POST" action="login.php">
            <label for="correo">Correo electrónico</label>
            <input
                type="email"
                id="correo"
                name="correo"
                required
                autocomplete="username"
            >

            <label for="contrasena">Contraseña</label>
            <input
                type="password"
                id="contrasena"
                name="contrasena"
                required
                autocomplete="current-password"
            >

            <button type="submit">Ingresar</button>
        </form>

        <?php if ($mensaje !== ""): ?>
            <p class="mensaje">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>
    </main>
</body>
</html>
