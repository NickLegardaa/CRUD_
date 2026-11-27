# GANGA Shoes — Panel de inventario

Interfaz responsive con Bootstrap 5 para administrar el inventario de GANGA Shoes. Incluye inicio y cierre de sesión, y conecta el panel con los formularios existentes del CRUD.

## Funciones

- Ingreso con nombre de usuario o correo y contraseña.
- Sesión requerida para abrir el panel y usar las rutas del CRUD.
- Consultar, crear, editar y eliminar productos.
- Buscar por nombre, descripción o marca y filtrar por categoría.
- Ver unidades, valor del inventario y productos con stock bajo.

## Ejecutar en XAMPP

1. Copia `GANGA_Shoes_Bootstrap` a `C:\xampp\htdocs\`.
2. Inicia Apache y MySQL.
3. Importa `database/tienda_zapatos.sql` desde phpMyAdmin.
4. Crea una cuenta en la tabla `usuarios`. El login acepta contraseñas antiguas en texto plano y las convierte a un hash seguro al iniciar sesión correctamente; las cuentas nuevas deben guardar el resultado de `password_hash()`.
5. Abre `http://localhost/GANGA_Shoes_Bootstrap/`; te llevará al login.

Los datos de conexión se encuentran en `php/conexion.php` y usan `localhost`, usuario `root`, contraseña vacía y base `tienda_zapatos`.

## Archivos principales

- `login.php` y `logout.php`: inicio y cierre de sesión.
- `index.php`: panel protegido.
- `assets/css/style.css`: estilos del panel; el login usa Bootstrap.
- `assets/js/app.js`: conexión entre el panel y las respuestas HTML del CRUD.
- `php/auth_guard.php`: exige una sesión para acceder a los PHP del CRUD.
- `php/conexion.php`, `crear.php`, `consultar.php`, `actualizar.php`, `eliminar.php`: CRUD existente; se añadió el control de sesión a sus rutas.
- `database/tienda_zapatos.sql`: esquema y registros de ejemplo.

El CRUD devuelve páginas HTML. El frontend envía los formularios y adapta esas respuestas; la búsqueda y el filtro se realizan en el navegador.
