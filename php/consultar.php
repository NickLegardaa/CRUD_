<?php
require __DIR__ . '/auth_guard.php';

$conexion = mysqli_connect("localhost", "root", "", "tienda_zapatos");

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

$sql = "SELECT productos.id_producto, productos.nombre, productos.descripcion, productos.precio, productos.cantidad, categorias.nombre AS categoria, marcas.nombre AS marca
        FROM productos
        INNER JOIN categorias ON productos.id_categoria = categorias.id_categoria
        INNER JOIN marcas ON productos.id_marca = marcas.id_marca";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consultar productos</title>
</head>
<body>

    <h2>Productos registrados</h2>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Categoría</th>
            <th>Marca</th>
        </tr>

        <?php while ($producto = mysqli_fetch_assoc($resultado)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($producto['id_producto']); ?></td>
                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($producto['precio']); ?></td>
                <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                <td><?php echo htmlspecialchars($producto['categoria']); ?></td>
                <td><?php echo htmlspecialchars($producto['marca']); ?></td>
            </tr>
        <?php } ?>

    </table>

</body>
</html>

<?php
mysqli_close($conexion);
?>
