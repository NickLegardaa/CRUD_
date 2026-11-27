const PHP = 'php/';
const tableBody = document.getElementById('productsTable');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const productModal = new bootstrap.Modal(document.getElementById('productModal'));
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
const toast = new bootstrap.Toast(document.getElementById('appToast'), {delay: 3600});
let products = [];
let visibleProducts = [];
let deleteId = null;
let searchTimer = null;

const money = value => new Intl.NumberFormat('es-CO', {style:'currency', currency:'COP', maximumFractionDigits:0}).format(Number(value || 0));
const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));

function notify(message, type='success') {
  const el = document.getElementById('appToast');
  el.className = `toast border-0 text-bg-${type}`;
  document.getElementById('toastMessage').textContent = message;
  toast.show();
}

async function loadPage(url, options={}) {
  const response = await fetch(url, options);
  const html = await response.text();
  if (response.status === 401) {
    window.location.replace('login.php');
    throw new Error('Tu sesión terminó. Vuelve a iniciar sesión.');
  }
  if (!response.ok) throw new Error(`El servidor respondió con el código ${response.status}.`);
  return new DOMParser().parseFromString(html, 'text/html');
}

function formMessage(documentPage, fallback) {
  const alert = documentPage.querySelector('[role="alert"]');
  const status = documentPage.querySelector('[role="status"]');
  if (alert?.textContent.trim()) return {error: alert.textContent.trim()};
  if (status?.textContent.trim()) return {message: status.textContent.trim()};
  const text = documentPage.body.textContent.replace(/\s+/g, ' ').trim();
  if (/^Error\b/i.test(text)) return {error: text};
  return {message: fallback};
}

async function loadMeta() {
  const page = await loadPage(PHP + 'crear.php');
  const form = page.querySelector('form');
  if (!form) throw new Error('No se encontró el formulario de productos en php/crear.php.');
  const categoryOptions = [...form.querySelectorAll('select[name="id_categoria"] option')];
  const brandOptions = [...form.querySelectorAll('select[name="id_marca"] option')];
  const addOptions = (select, options, placeholder) => {
    select.innerHTML = `<option value="">${placeholder}</option>` + options.map(option =>
      `<option value="${escapeHtml(option.value)}">${escapeHtml(option.textContent.trim())}</option>`
    ).join('');
  };
  addOptions(categoryFilter, categoryOptions, 'Todas las categorías');
  addOptions(document.getElementById('productCategory'), categoryOptions, 'Selecciona una categoría');
  addOptions(document.getElementById('productBrand'), brandOptions, 'Selecciona una marca');
}

async function loadProducts() {
  tableBody.innerHTML = '<tr><td colspan="6" class="loading-cell"><span class="spinner-border spinner-border-sm"></span> Cargando productos…</td></tr>';
  try {
    const page = await loadPage(PHP + 'consultar.php');
    products = [...page.querySelectorAll('table tr')].map(row => [...row.querySelectorAll('td')]).filter(cells => cells.length >= 7).map(cells => ({
      id_producto: Number(cells[0].textContent.trim()),
      nombre: cells[1].textContent.trim(),
      descripcion: cells[2].textContent.trim(),
      precio: Number(cells[3].textContent.trim().replace(',', '.')),
      cantidad: Number(cells[4].textContent.trim()),
      categoria: cells[5].textContent.trim(),
      marca: cells[6].textContent.trim()
    }));
    renderProducts();
    updateStats();
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="6" class="empty-cell"><i class="bi bi-cloud-slash"></i><strong>No se pudo cargar el inventario</strong><span>${escapeHtml(error.message)}</span><button class="btn btn-sm btn-outline-primary mt-2" onclick="loadProducts()">Reintentar</button></td></tr>`;
    notify(error.message, 'danger');
  }
}

function filterProducts() {
  const query = searchInput.value.trim().toLocaleLowerCase('es');
  const category = categoryFilter.selectedOptions[0]?.textContent.trim();
  visibleProducts = products.filter(product => {
    const matchesText = !query || [product.nombre, product.descripcion, product.marca].some(value => value.toLocaleLowerCase('es').includes(query));
    const matchesCategory = !categoryFilter.value || product.categoria === category;
    return matchesText && matchesCategory;
  });
}

function renderProducts() {
  filterProducts();
  document.getElementById('resultsCount').textContent = `${visibleProducts.length} ${visibleProducts.length === 1 ? 'producto' : 'productos'}`;
  document.getElementById('footerCount').textContent = `Mostrando ${visibleProducts.length} ${visibleProducts.length === 1 ? 'referencia' : 'referencias'}`;
  if (!visibleProducts.length) {
    tableBody.innerHTML = '<tr><td colspan="6" class="empty-cell"><div class="empty-icon"><i class="bi bi-box-seam"></i></div><strong>No hay productos para mostrar</strong><span>Prueba otra búsqueda o registra un nuevo producto.</span></td></tr>';
    return;
  }
  tableBody.innerHTML = visibleProducts.map(p => {
    const initials = p.nombre.trim().split(/\s+/).slice(0,2).map(w => w[0]).join('').toUpperCase();
    const stockClass = Number(p.cantidad) === 0 ? 'stock-empty' : Number(p.cantidad) <= 5 ? 'stock-low' : 'stock-good';
    const stockLabel = Number(p.cantidad) === 0 ? 'Agotado' : Number(p.cantidad) <= 5 ? 'Bajo' : 'Disponible';
    return `<tr>
      <td><div class="product-cell"><div class="product-thumb">${escapeHtml(initials)}</div><div class="product-info"><strong>${escapeHtml(p.nombre)}</strong><small>${escapeHtml(p.descripcion || 'Sin descripción')}</small></div></div></td>
      <td><span class="category-tag">${escapeHtml(p.categoria)}</span></td>
      <td class="brand-cell">${escapeHtml(p.marca)}</td>
      <td class="price-cell">${money(p.precio)}</td>
      <td><div class="stock-cell"><span class="stock-badge ${stockClass}">${Number(p.cantidad)} <span>${stockLabel}</span></span></div></td>
      <td><div class="row-actions"><button class="btn action-btn edit-btn" title="Editar producto" aria-label="Editar ${escapeHtml(p.nombre)}" onclick="editProduct(${Number(p.id_producto)})"><i class="bi bi-pencil-square"></i></button><button class="btn action-btn delete-btn" title="Eliminar producto" aria-label="Eliminar ${escapeHtml(p.nombre)}" onclick="askDelete(${Number(p.id_producto)})"><i class="bi bi-trash3"></i></button></div></td>
    </tr>`;
  }).join('');
}

function updateStats() {
  const units = products.reduce((sum,p) => sum + Number(p.cantidad), 0);
  const value = products.reduce((sum,p) => sum + Number(p.precio) * Number(p.cantidad), 0);
  const low = products.filter(p => Number(p.cantidad) <= 5).length;
  document.getElementById('statTotal').textContent = products.length.toLocaleString('es-CO');
  document.getElementById('statUnits').textContent = units.toLocaleString('es-CO');
  document.getElementById('statValue').textContent = money(value);
  document.getElementById('statLow').textContent = low.toLocaleString('es-CO');
}

function clearForm() {
  document.getElementById('productForm').reset();
  document.getElementById('productId').value = '';
  document.getElementById('productForm').classList.remove('was-validated');
}

document.getElementById('newProductBtn').addEventListener('click', () => {
  clearForm();
  document.getElementById('productModalLabel').textContent = 'Nuevo producto';
  document.getElementById('saveProductBtn').innerHTML = '<i class="bi bi-check2"></i> Guardar producto';
  productModal.show();
});

window.editProduct = async id => {
  const button = document.querySelector(`[onclick="editProduct(${Number(id)})"]`);
  if (button) button.disabled = true;
  try {
    const page = await loadPage(`${PHP}actualizar.php?id=${encodeURIComponent(id)}`);
    const sourceForm = page.querySelector('form');
    if (!sourceForm) throw new Error(page.querySelector('[role="alert"]')?.textContent.trim() || 'No se encontró el formulario para editar el producto.');
    const values = new FormData(sourceForm);
    clearForm();
    document.getElementById('productId').value = values.get('id_producto') || id;
    document.getElementById('productName').value = values.get('nombre') || '';
    document.getElementById('productDescription').value = values.get('descripcion') || '';
    document.getElementById('productPrice').value = values.get('precio') || '';
    document.getElementById('productQuantity').value = values.get('cantidad') || '';
    document.getElementById('productCategory').value = values.get('id_categoria') || '';
    document.getElementById('productBrand').value = values.get('id_marca') || '';
    document.getElementById('productModalLabel').textContent = 'Editar producto';
    document.getElementById('saveProductBtn').innerHTML = '<i class="bi bi-check2"></i> Guardar cambios';
    productModal.show();
  } catch (error) { notify(error.message, 'danger'); }
  finally { if (button) button.disabled = false; }
};

document.getElementById('productForm').addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.currentTarget;
  if (!form.checkValidity()) { form.classList.add('was-validated'); return; }
  const price = Number(document.getElementById('productPrice').value);
  const quantity = Number(document.getElementById('productQuantity').value);
  if (!Number.isFinite(price) || price < 0 || !Number.isInteger(quantity) || quantity < 0) {
    notify('El precio y la cantidad deben ser valores válidos y no negativos.', 'danger'); return;
  }
  const id = document.getElementById('productId').value;
  const payload = new URLSearchParams();
  payload.set('nombre', document.getElementById('productName').value.trim());
  payload.set('descripcion', document.getElementById('productDescription').value.trim());
  payload.set('precio', String(price));
  payload.set('cantidad', String(quantity));
  payload.set('id_categoria', document.getElementById('productCategory').value);
  payload.set('id_marca', document.getElementById('productBrand').value);
  if (id) payload.set('id_producto', id);
  const button = document.getElementById('saveProductBtn');
  button.disabled = true; button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando…';
  try {
    const endpoint = id ? 'actualizar.php' : 'crear.php';
    const page = await loadPage(PHP + endpoint + (id ? `?id=${encodeURIComponent(id)}` : ''), {method:'POST', body:payload});
    const result = formMessage(page, id ? 'Producto actualizado correctamente.' : 'Producto registrado correctamente.');
    if (result.error) throw new Error(result.error);
    productModal.hide(); notify(result.message); await loadProducts();
  } catch (error) { notify(error.message, 'danger'); }
  finally { button.disabled = false; button.innerHTML = id ? '<i class="bi bi-check2"></i> Guardar cambios' : '<i class="bi bi-check2"></i> Guardar producto'; }
});

window.askDelete = id => {
  const product = products.find(item => Number(item.id_producto) === Number(id));
  if (!product) return notify('No se encontró el producto solicitado.', 'danger');
  deleteId = Number(id);
  document.getElementById('deleteProductName').textContent = product.nombre;
  deleteModal.show();
};

document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
  if (!deleteId) return;
  const button = document.getElementById('confirmDeleteBtn');
  button.disabled = true; button.textContent = 'Eliminando…';
  try {
    const payload = new URLSearchParams({id_producto: String(deleteId)});
    const page = await loadPage(PHP + 'eliminar.php', {method:'POST', body:payload});
    const result = formMessage(page, 'Producto eliminado correctamente.');
    if (result.error) throw new Error(result.error);
    deleteModal.hide(); notify(result.message); deleteId = null; await loadProducts();
  } catch (error) { notify(error.message, 'danger'); }
  finally { button.disabled = false; button.textContent = 'Eliminar'; }
});

searchInput.addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(renderProducts, 180); });
categoryFilter.addEventListener('change', renderProducts);
document.getElementById('resetFilters').addEventListener('click', () => { searchInput.value = ''; categoryFilter.value = ''; renderProducts(); });
document.getElementById('menuToggle').addEventListener('click', () => document.getElementById('sidebar').classList.toggle('show'));
document.addEventListener('DOMContentLoaded', async () => {
  try { await loadMeta(); await loadProducts(); }
  catch (error) { notify(error.message, 'danger'); }
});
