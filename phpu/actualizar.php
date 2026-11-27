<?php
require __DIR__ . '/conexion.php';
function h($valor) { return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); }

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error = '';
$mensaje = isset($_GET['actualizado']) ? 'Producto actualizado correctamente.' : '';
$productos = mysqli_query($conexion, 'SELECT id_producto, nombre FROM productos ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);
$categorias = mysqli_query($conexion, 'SELECT id_categoria, nombre FROM categorias ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);
$marcas = mysqli_query($conexion, 'SELECT id_marca, nombre FROM marcas ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);
$producto = null;

if ($id) {
    $consulta = $conexion->prepare('SELECT * FROM productos WHERE id_producto = ?');
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $producto = $consulta->get_result()->fetch_assoc();
    if (!$producto) $error = 'No se encontró el producto.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precioTexto = trim($_POST['precio'] ?? '');
    $cantidadTexto = trim($_POST['cantidad'] ?? '');
    $precio = filter_var($precioTexto, FILTER_VALIDATE_FLOAT);
    $cantidad = filter_var($cantidadTexto, FILTER_VALIDATE_INT);
    $categoria = filter_var($_POST['id_categoria'] ?? null, FILTER_VALIDATE_INT);
    $marca = filter_var($_POST['id_marca'] ?? null, FILTER_VALIDATE_INT);

    if (!$id) $error = 'Selecciona un producto válido.';
    elseif (!$producto) $error = 'No se encontró el producto.';
    elseif ($nombre === '') $error = 'El nombre es obligatorio.';
    elseif ($precioTexto === '' || $precio === false) $error = 'Ingresa un precio válido.';
    elseif ($precio < 0) $error = 'El precio no puede ser negativo.';
    elseif ($cantidadTexto === '' || $cantidad === false) $error = 'Ingresa una cantidad entera válida.';
    elseif ($cantidad < 0) $error = 'La cantidad no puede ser negativa.';
    elseif (!$categoria || !$marca) $error = 'Selecciona una categoría y una marca.';
    else {
        try {
            $actualizar = $conexion->prepare(
                'UPDATE productos SET nombre=?, descripcion=?, precio=?, cantidad=?, id_categoria=?, id_marca=? WHERE id_producto=?'
            );
            $actualizar->bind_param('ssdiiii', $nombre, $descripcion, $precio, $cantidad, $categoria, $marca, $id);
            $actualizar->execute();
            header('Location: actualizar.php?id=' . $id . '&actualizado=1');
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = 'No se pudo actualizar. Revisa que la categoría y la marca existan.';
        }
    }

    if ($producto) $producto = array_merge($producto, $_POST);
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Actualizar producto</title></head><body>
<main>
    <h1>Actualizar producto</h1>
    <?php if ($mensaje): ?><p role="status"><?= h($mensaje) ?></p><?php endif; ?>
    <?php if ($error): ?><p role="alert"><?= h($error) ?></p><?php endif; ?>

    <?php if (!$producto): ?>
        <h2>Selecciona un producto</h2>
        <?php if (!$productos): ?><p>No hay productos registrados.</p><?php endif; ?>
        <ul><?php foreach ($productos as $item): ?>
            <li><a href="?id=<?= (int) $item['id_producto'] ?>"><?= h($item['nombre']) ?> (ID <?= (int) $item['id_producto'] ?>)</a></li>
        <?php endforeach; ?></ul>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="id_producto" value="<?= (int) $producto['id_producto'] ?>">
            <label>Nombre <input name="nombre" maxlength="100" required value="<?= h($producto['nombre']) ?>"></label><br>
            <label>Descripción <textarea name="descripcion"><?= h($producto['descripcion']) ?></textarea></label><br>
            <label>Precio <input type="number" name="precio" min="0" step="0.01" required value="<?= h($producto['precio']) ?>"></label><br>
            <label>Cantidad <input type="number" name="cantidad" min="0" step="1" required value="<?= h($producto['cantidad']) ?>"></label><br>
            <label>Categoría <select name="id_categoria" required><?php foreach ($categorias as $c): ?>
                <option value="<?= (int) $c['id_categoria'] ?>" <?= (int) $producto['id_categoria'] === (int) $c['id_categoria'] ? 'selected' : '' ?>><?= h($c['nombre']) ?></option>
            <?php endforeach; ?></select></label><br>
            <label>Marca <select name="id_marca" required><?php foreach ($marcas as $m): ?>
                <option value="<?= (int) $m['id_marca'] ?>" <?= (int) $producto['id_marca'] === (int) $m['id_marca'] ? 'selected' : '' ?>><?= h($m['nombre']) ?></option>
            <?php endforeach; ?></select></label><br>
            <button type="submit">Guardar cambios</button>
        </form>
        <p><a href="actualizar.php">Volver a productos</a></p>
    <?php endif; ?>
    <p><a href="eliminar.php">Ir a eliminar productos</a></p>
</main>
</body></html>
