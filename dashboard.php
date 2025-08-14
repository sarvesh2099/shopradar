<?php




require_once 'config.php';
require_once 'helpers.php';
require_login();
$merchantName = htmlspecialchars($_SESSION['merchant_name'] ?? 'Merchant');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ShopRadar — Merchant Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root{ --sr-primary:#173ea5; --sr-accent:#08a36f; --sr-bg:#f5f7fb; }
body{ background:var(--sr-bg); }
.sr-appbar{ background:linear-gradient(90deg, var(--sr-primary), var(--sr-accent)); color:#fff; }
.sr-card{ border:0; border-radius:1rem; box-shadow:0 10px 30px rgba(0,0,0,.06); }
.table img.thumb{ width:40px; height:40px; object-fit:cover; border-radius:6px; margin-right:6px; }
.badge-stock{ font-weight:600; }
</style>
</head>
<body>
<nav class="sr-appbar py-3">
  <div class="container d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <i class="bi bi-shop fs-3"></i>
      <div><div class="fw-bold">ShopRadar</div><small>Your Business Compass</small></div>
    </div>
    <div class="text-end">
      <div class="fw-semibold">Welcome, <?php echo $merchantName; ?> 👋</div>
      <a class="link-light" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container my-4">
  <div class="row g-4">
    <!-- Add Product -->
    <div class="col-lg-4">
      <div class="card sr-card">
        <div class="card-body">
          <h5 class="card-title mb-3"><i class="bi bi-plus-circle"></i> Add Product</h5>
          <form id="addForm" enctype="multipart/form-data">
            <div class="mb-2">
              <label class="form-label">Product Name</label>
              <input required name="product_name" class="form-control" placeholder="e.g., iPhone 13">
            </div>
            <div class="mb-2">
              <label class="form-label">Category</label>
              <select class="form-select" name="category" id="categorySelect" required>
                <option value="">Select Category</option>
                <option>Mobiles</option>
                <option>Electronics</option>
                <option>Clothing</option>
                <option>Groceries</option>
                <option>Home & Kitchen</option>
                <option>Beauty</option>
                <option value="Other">Other (type below)</option>
              </select>
              <input type="text" id="customCategory" name="custom_category" class="form-control mt-2 d-none" placeholder="Enter custom category">
            </div>
            <div class="mb-2">
              <label class="form-label">Price (₹)</label>
              <input required type="number" step="0.01" min="0" name="price" class="form-control">
            </div>
            <div class="mb-2">
              <label class="form-label">Quantity</label>
              <input required type="number" min="0" name="quantity" class="form-control">
            </div>
            <div class="mb-2">
              <label class="form-label">Product Image</label>
              <input type="file" name="image" class="form-control" accept="image/*">
              <div class="form-text">PNG/JPG/WebP, max 2 MB.</div>
            </div>
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" id="in_stock" name="in_stock" checked>
              <label class="form-check-label" for="in_stock">In Stock</label>
            </div>
            <button class="btn btn-primary w-100" type="submit">Save Product</button>
            <div id="formMsg" class="small mt-2"></div>
          </form>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="col-lg-8">
      <div class="card sr-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0"><i class="bi bi-box-seam"></i> Your Products</h5>
            <input id="searchBox" class="form-control w-auto" placeholder="Search…">
          </div>
          <div class="table-responsive">
            <table class="table align-middle" id="productsTable">
              <thead>
                <tr>
                  <th>Product</th><th>Category</th><th>Price</th><th>Qty</th><th>Status</th><th style="width:150px">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id">
          <div class="mb-2">
            <label class="form-label">Product Name</label>
            <input required name="product_name" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Category</label>
            <select class="form-select" name="category" id="edit_categorySelect" required>
              <option value="">Select Category</option>
              <option>Mobiles</option><option>Electronics</option><option>Clothing</option>
              <option>Groceries</option><option>Home & Kitchen</option><option>Beauty</option>
              <option value="Other">Other (type below)</option>
            </select>
            <input type="text" id="edit_customCategory" name="custom_category" class="form-control mt-2 d-none" placeholder="Enter custom category">
          </div>
          <div class="mb-2">
            <label class="form-label">Price (₹)</label>
            <input required type="number" step="0.01" min="0" name="price" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Quantity</label>
            <input required type="number" min="0" name="quantity" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Replace Image (optional)</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <div class="form-text">Leave empty to keep current image.</div>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="edit_in_stock" name="in_stock">
            <label class="form-check-label" for="edit_in_stock">In Stock</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const $  = (s, r=document)=>r.querySelector(s);
const $$ = (s, r=document)=>[...r.querySelectorAll(s)];
const tbody = $('#productsTable tbody');
const editModal = new bootstrap.Modal($('#editModal'));
const fmt = new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR'});

function rowHtml(p){
  return `<tr data-id="${p.id}">
    <td>${p.image?`<img class="thumb" src="uploads/${p.image}" alt="">`:``}${p.product_name}</td>
    <td>${p.category}</td>
    <td>${fmt.format(p.price)}</td>
    <td>${p.quantity}</td>
    <td>${p.in_stock==1?'<span class="badge text-bg-success badge-stock">In Stock</span>':'<span class="badge text-bg-secondary badge-stock">Out</span>'}</td>
    <td>
      <button class="btn btn-sm btn-outline-primary me-1" data-action="edit"><i class="bi bi-pencil"></i></button>
      <button class="btn btn-sm btn-outline-danger" data-action="delete"><i class="bi bi-trash"></i></button>
    </td>
  </tr>`;
}

async function fetchProducts(){
  const res = await fetch('product_actions.php?action=list');
  const data = await res.json();
  tbody.innerHTML = data.map(rowHtml).join('');
}

$('#categorySelect').addEventListener('change', e=>{
  $('#customCategory').classList.toggle('d-none', e.target.value !== 'Other');
});
$('#edit_categorySelect').addEventListener('change', e=>{
  $('#edit_customCategory').classList.toggle('d-none', e.target.value !== 'Other');
});

// Add
$('#addForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('action','add');
  fd.set('in_stock', $('#in_stock').checked ? '1':'0');
  const res = await fetch('product_actions.php',{method:'POST', body:fd});
  const out = await res.json();
  $('#formMsg').textContent = out.message || '';
  if(out.ok){ e.target.reset(); $('#in_stock').checked = true; $('#customCategory').classList.add('d-none'); fetchProducts(); }
});

// Search
$('#searchBox').addEventListener('input', e=>{
  const q = e.target.value.toLowerCase();
  $$('#productsTable tbody tr').forEach(tr=> tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none');
});

// Row actions
tbody.addEventListener('click', async (e)=>{
  const btn = e.target.closest('button'); if(!btn) return;
  const tr = e.target.closest('tr'); const id = tr.dataset.id;

  if(btn.dataset.action==='delete'){
    if(!confirm('Delete this product?')) return;
    const fd = new FormData(); fd.append('action','delete'); fd.append('id',id);
    const res = await fetch('product_actions.php',{method:'POST', body:fd});
    const out = await res.json();
    if(out.ok) tr.remove(); else alert(out.message||'Error');
  }

  if(btn.dataset.action==='edit'){
    const res = await fetch('product_actions.php?action=get&id='+id);
    const p = await res.json();
    const f = $('#editForm');
    f.id.value = p.id;
    f.product_name.value = p.product_name;
    f.price.value = p.price;
    f.quantity.value = p.quantity;
    $('#edit_in_stock').checked = p.in_stock==1;

    // category
    const known = ["Mobiles","Electronics","Clothing","Groceries","Home & Kitchen","Beauty"];
    if(known.includes(p.category)){ $('#edit_categorySelect').value = p.category; $('#edit_customCategory').classList.add('d-none'); }
    else { $('#edit_categorySelect').value = 'Other'; $('#edit_customCategory').classList.remove('d-none'); $('#edit_customCategory').value = p.category; }

    editModal.show();
  }
});

// Update
$('#editForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('action','update');
  fd.set('in_stock', $('#edit_in_stock').checked ? '1':'0');
  const res = await fetch('product_actions.php',{method:'POST', body:fd});
  const out = await res.json();
  if(out.ok){ editModal.hide(); fetchProducts(); } else { alert(out.message||'Error'); }
});

fetchProducts();
</script>
</body>
</html>
