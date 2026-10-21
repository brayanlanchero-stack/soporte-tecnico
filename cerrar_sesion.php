
<?php
session_start();

// Eliminar las variables de la sesión.
$_SESSION = [];

// Destruir la sesión actual.
session_destroy();

// Regresar al inicio de sesión.
header("Location: login.php");
exit;
?>
