<?php
require __DIR__ . '/auth_guard.php';

$conexion = mysqli_connect("localhost", "root", "", "tienda_zapatos");

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];
    $id_categoria = $_POST['id_categoria'];
    $id_marca = $_POST['id_marca'];

    $sql = "INSERT INTO productos (nombre, descripcion, precio, cantidad, id_categoria, id_marca)
            VALUES ('$nombre', '$descripcion', '$precio', '$cantidad', '$id_categoria', '$id_marca')";

    if (mysqli_query($conexion, $sql)) {
        echo "Producto registrado correctamente";
    } else {
        echo "Error al registrar el producto: " . mysqli_error($conexion);
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar producto</title>
</head>
<body>

    <h2>Registrar producto</h2>

    <form method="POST">

        <label>Nombre:</label>
        <input type="text" name="nombre" required>
        <br><br>

        <label>Descripción:</label>
        <textarea name="descripcion"></textarea>
        <br><br>

        <label>Precio:</label>
        <input type="number" name="precio" step="0.01" required>
        <br><br>

        <label>Cantidad:</label>
        <input type="number" name="cantidad" min="0" required>
        <br><br>

        <label>Categoría:</label>
        <select name="id_categoria" required>
            <?php
            $categorias = mysqli_query($conexion, "SELECT * FROM categorias");

            while ($categoria = mysqli_fetch_assoc($categorias)) {
                echo "<option value='" . $categoria['id_categoria'] . "'>" . htmlspecialchars($categoria['nombre']) . "</option>";
            }
            ?>
        </select>
        <br><br>

        <label>Marca:</label>
        <select name="id_marca" required>
            <?php
            $marcas = mysqli_query($conexion, "SELECT * FROM marcas");

            while ($marca = mysqli_fetch_assoc($marcas)) {
                echo "<option value='" . $marca['id_marca'] . "'>" . htmlspecialchars($marca['nombre']) . "</option>";
            }
            ?>
        </select>
        <br><br>

        <button type="submit">Registrar producto</button>

    </form>

</body>
</html>

<?php
mysqli_close($conexion);
?>
