
<?php

$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "soporte_tecnico";

$conexion = new mysqli(
    $servidor,
    $usuario,
    $contrasena,
    $base_datos
);

if ($conexion->connect_error) {
    error_log("Error de conexión a MySQL: " . $conexion->connect_error);
    exit("No fue posible conectar con la base de datos.");
}

$conexion->set_charset("utf8mb4");
?>
