<?php
require __DIR__ . '/conexion.php';
function h($valor) { return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); }

$mensaje = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
    if (!$id) $error = 'El producto seleccionado no es válido.';
    else {
        try {
            $borrar = $conexion->prepare('DELETE FROM productos WHERE id_producto = ?');
            $borrar->bind_param('i', $id);
            $borrar->execute();
            $mensaje = $borrar->affected_rows ? 'Producto eliminado correctamente.' : 'El producto ya no existe.';
        } catch (mysqli_sql_exception $e) {
            $error = 'No se pudo eliminar porque el producto está relacionado con otros registros.';
        }
    }
}

$productos = mysqli_query($conexion,
    'SELECT p.id_producto, p.nombre, p.precio, p.cantidad, c.nombre AS categoria, m.nombre AS marca
     FROM productos p JOIN categorias c ON c.id_categoria=p.id_categoria
     JOIN marcas m ON m.id_marca=p.id_marca ORDER BY p.nombre'
)->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Eliminar producto</title></head><body>
<main>
    <h1>Eliminar producto</h1>
    <?php if ($mensaje): ?><p role="status"><?= h($mensaje) ?></p><?php endif; ?>
    <?php if ($error): ?><p role="alert"><?= h($error) ?></p><?php endif; ?>
    <?php if (!$productos): ?><p>No hay productos registrados.</p><?php else: ?>
        <table>
            <thead><tr><th>Nombre</th><th>Marca</th><th>Categoría</th><th>Precio</th><th>Cantidad</th><th>Acción</th></tr></thead>
            <tbody><?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= h($p['nombre']) ?></td><td><?= h($p['marca']) ?></td><td><?= h($p['categoria']) ?></td>
                    <td>$<?= number_format((float) $p['precio'], 2, ',', '.') ?></td><td><?= (int) $p['cantidad'] ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar este producto?');">
                            <input type="hidden" name="id_producto" value="<?= (int) $p['id_producto'] ?>">
                            <button type="submit">Eliminar</button>
                        </form>
                        <a href="actualizar.php?id=<?= (int) $p['id_producto'] ?>">Actualizar</a>
                    </td>
                </tr>
            <?php endforeach; ?></tbody>
        </table>
    <?php endif; ?>
    <p><a href="actualizar.php">Ir a actualizar productos</a></p>
</main>
</body></html>
