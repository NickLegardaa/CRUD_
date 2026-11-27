<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = mysqli_connect('localhost', 'root', '', 'tienda_zapatos');
    mysqli_set_charset($conexion, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Error de conexión. Revisa que MySQL esté activo y que exista tienda_zapatos.');
}
