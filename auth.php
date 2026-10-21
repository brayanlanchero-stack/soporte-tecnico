
<?php

// Iniciar la sesión si todavía no está iniciada.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario haya iniciado sesión.
function exigirLogin(): void
{
    if (empty($_SESSION["usuario_id"])) {
        header("Location: login.php");
        exit;
    }
}

// Verificar que el usuario tenga uno de los roles permitidos.
function exigirRol(array $rolesPermitidos): void
{
    exigirLogin();

    $rol = $_SESSION["rol"] ?? "";

    if (!in_array($rol, $rolesPermitidos, true)) {
        http_response_code(403);
        exit("No tienes permisos para acceder a esta página.");
    }
}
