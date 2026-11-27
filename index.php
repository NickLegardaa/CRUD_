<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$pageTitle = "GANGA Shoes | Inventario";
$userName = htmlspecialchars((string) ($_SESSION['user_name'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <a class="brand" href="#"><span class="brand-mark"><i class="bi bi-bag-heart-fill"></i></span><span>GANGA<span class="brand-light">SHOES</span></span></a>
    <div class="sidebar-label">MENÚ PRINCIPAL</div>
    <a class="side-link active" href="#inventario"><i class="bi bi-grid-1x2-fill"></i> Inventario <span class="side-pill">01</span></a>
    <a class="side-link" href="#resumen"><i class="bi bi-bar-chart-line"></i> Resumen</a>
    <div class="sidebar-bottom">
      <div class="help-card"><div class="help-icon"><i class="bi bi-lightbulb"></i></div><strong>¿Necesitas ayuda?</strong><p>Administra tu inventario de forma sencilla.</p><a href="#ayuda">Ver guía <i class="bi bi-arrow-up-right"></i></a></div>
      <div class="profile"><div class="avatar">GS</div><div><strong><?= $userName ?></strong><small>Panel de tienda</small></div><i class="bi bi-three-dots ms-auto"></i></div>
    </div>
  </aside>

  <main class="main-content">
    <header class="topbar">
      <button class="btn icon-btn d-lg-none" id="menuToggle" aria-label="Abrir menú"><i class="bi bi-list"></i></button>
      <div class="breadcrumb-text">Panel de control <span>/</span> <strong>Inventario</strong></div>
      <div class="top-actions"><span class="status-dot"></span><span class="store-status">Tienda activa</span><a class="btn icon-btn" href="logout.php" title="Cerrar sesión" aria-label="Cerrar sesión"><i class="bi bi-box-arrow-right"></i></a><div class="top-avatar">GS</div></div>
    </header>

    <section class="page-heading" id="inventario">
      <div><div class="eyebrow">GESTIÓN DE TIENDA</div><h1>Inventario de productos</h1><p>Controla, organiza y actualiza todos tus zapatos desde un solo lugar.</p></div>
      <button class="btn btn-primary btn-add" id="newProductBtn"><i class="bi bi-plus-lg"></i> Nuevo producto</button>
    </section>

    <section class="stats-grid" id="resumen">
      <article class="stat-card"><div class="stat-top"><span>Total de productos</span><span class="stat-icon purple"><i class="bi bi-box-seam"></i></span></div><div class="stat-number" id="statTotal">—</div><div class="stat-foot"><span class="stat-note">Referencias registradas</span></div></article>
      <article class="stat-card"><div class="stat-top"><span>Unidades disponibles</span><span class="stat-icon blue"><i class="bi bi-archive"></i></span></div><div class="stat-number" id="statUnits">—</div><div class="stat-foot"><span class="stat-note">Stock actual</span></div></article>
      <article class="stat-card"><div class="stat-top"><span>Valor del inventario</span><span class="stat-icon green"><i class="bi bi-currency-dollar"></i></span></div><div class="stat-number" id="statValue">—</div><div class="stat-foot"><span class="stat-note">Precio × unidades</span></div></article>
      <article class="stat-card"><div class="stat-top"><span>Stock bajo</span><span class="stat-icon orange"><i class="bi bi-exclamation-triangle"></i></span></div><div class="stat-number" id="statLow">—</div><div class="stat-foot"><span class="stat-note">5 unidades o menos</span></div></article>
    </section>

    <section class="inventory-panel">
      <div class="panel-heading"><div><h2>Todos los productos</h2><p>Consulta y administra las referencias de tu tienda.</p></div><span class="results-count" id="resultsCount">Cargando…</span></div>
      <div class="filters-row">
        <div class="search-box"><i class="bi bi-search"></i><input type="search" id="searchInput" placeholder="Buscar por nombre o descripción..." aria-label="Buscar productos"></div>
        <select class="form-select category-filter" id="categoryFilter" aria-label="Filtrar por categoría"><option value="">Todas las categorías</option></select>
        <button class="btn btn-outline-secondary filter-reset" id="resetFilters"><i class="bi bi-arrow-counterclockwise"></i><span>Limpiar</span></button>
      </div>
      <div class="table-responsive product-table-wrap">
        <table class="table product-table align-middle mb-0">
          <thead><tr><th>PRODUCTO</th><th>CATEGORÍA</th><th>MARCA</th><th>PRECIO</th><th>STOCK</th><th class="text-end">ACCIONES</th></tr></thead>
          <tbody id="productsTable"><tr><td colspan="6" class="loading-cell"><span class="spinner-border spinner-border-sm"></span> Cargando productos…</td></tr></tbody>
        </table>
      </div>
      <div class="table-footer"><span id="footerCount">—</span><span class="footer-hint"><i class="bi bi-shield-check"></i> Inventario sincronizado con la base de datos</span></div>
    </section>
    <footer class="page-footer"><span>© <?= date('Y') ?> GANGA Shoes</span><span>Gestión de inventario <b>·</b> CRUD PHP + MySQL</span></footer>
  </main>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3"><div id="appToast" class="toast border-0" role="status" aria-live="polite"><div class="d-flex"><div class="toast-body" id="toastMessage"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button></div></div></div>

<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content product-modal">
  <div class="modal-header"><div><div class="eyebrow">INVENTARIO</div><h2 class="modal-title" id="productModalLabel">Nuevo producto</h2><p>Completa la información de la referencia.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
  <form id="productForm" novalidate>
   <div class="modal-body"><input type="hidden" id="productId">
    <div class="row g-3">
     <div class="col-md-7"><label for="productName" class="form-label">Nombre del producto <span>*</span></label><input class="form-control" id="productName" maxlength="100" required placeholder="Ej. Nike Air Max"></div>
     <div class="col-md-5"><label for="productCategory" class="form-label">Categoría <span>*</span></label><select class="form-select" id="productCategory" required><option value="">Selecciona una categoría</option></select></div>
     <div class="col-12"><label for="productDescription" class="form-label">Descripción</label><textarea class="form-control" id="productDescription" rows="3" maxlength="1000" placeholder="Describe el producto..."></textarea></div>
     <div class="col-md-4"><label for="productPrice" class="form-label">Precio (COP) <span>*</span></label><div class="input-group"><span class="input-group-text">$</span><input type="number" class="form-control" id="productPrice" min="0" step="0.01" required placeholder="0"></div></div>
     <div class="col-md-4"><label for="productQuantity" class="form-label">Cantidad disponible <span>*</span></label><input type="number" class="form-control" id="productQuantity" min="0" step="1" required placeholder="0"></div>
     <div class="col-md-4"><label for="productBrand" class="form-label">Marca <span>*</span></label><select class="form-select" id="productBrand" required><option value="">Selecciona una marca</option></select></div>
    </div>
    <div class="form-note"><i class="bi bi-info-circle"></i> Los campos marcados con * son obligatorios. El precio y la cantidad no pueden ser negativos.</div>
   </div>
   <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary" id="saveProductBtn"><i class="bi bi-check2"></i> Guardar producto</button></div>
  </form>
 </div></div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content delete-modal"><div class="delete-symbol"><i class="bi bi-trash3"></i></div><h3>¿Eliminar producto?</h3><p>Vas a eliminar <strong id="deleteProductName"></strong>. Esta acción no se puede deshacer.</p><div class="d-flex gap-2"><button class="btn btn-light flex-fill" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-danger flex-fill" id="confirmDeleteBtn">Eliminar</button></div></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
