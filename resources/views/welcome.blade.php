<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Product Table</title>

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>

  <style>
    /* ── Override Bootstrap primary → orange ── */
    :root {
      --bs-primary:         #F97316;
      --bs-primary-rgb:     249,115,22;
      --bs-primary-hover:   #EA6C0A;
      --bs-link-color:      #F97316;

      --surface:            #FFFFFF;
      --surface-alt:        #FAFAFA;
      --border:             #F0F0F0;
      --text-main:          #111111;
      --text-muted:         #888888;
      --shadow-card:        0 2px 20px rgba(0,0,0,.07);
      --radius-card:        16px;
      --radius-badge:       8px;
    }

    * { box-sizing: border-box; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #F5F5F5;
      color: var(--text-main);
      min-height: 100vh;
      padding: 2.5rem 1rem;
    }

    /* ── Card wrapper ── */
    .table-card {
      background: var(--surface);
      border-radius: var(--radius-card);
      box-shadow: var(--shadow-card);
      overflow: hidden;
    }

    /* ── Card header ── */
    .table-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
      padding: 1.5rem 1.75rem 1.25rem;
      border-bottom: 1px solid var(--border);
    }

    .table-card-header h5 {
      font-weight: 700;
      font-size: 1.05rem;
      margin: 0;
      letter-spacing: -.3px;
    }

    .table-card-header .subtitle {
      font-size: .78rem;
      color: var(--text-muted);
      margin-top: 2px;
    }

    /* ── Search + Add ── */
    .search-wrapper {
      position: relative;
    }
    .search-wrapper .bi-search {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: .85rem;
      pointer-events: none;
    }
    .search-wrapper input {
      padding-left: 2rem;
      border-radius: 10px;
      border: 1.5px solid var(--border);
      font-size: .85rem;
      font-family: inherit;
      transition: border-color .2s;
      height: 38px;
      width: 220px;
      background: var(--surface-alt);
    }
    .search-wrapper input:focus {
      outline: none;
      border-color: var(--bs-primary);
      box-shadow: 0 0 0 3px rgba(249,115,22,.12);
      background: #fff;
    }

    /* ── Table core ── */
    .table-responsive {
      padding: 0 .25rem;
    }

    .table {
      margin: 0;
      font-size: .875rem;
    }

    .table thead th {
      font-weight: 600;
      font-size: .72rem;
      text-transform: uppercase;
      letter-spacing: .7px;
      color: var(--text-muted);
      border-bottom: 1px solid var(--border);
      padding: .9rem 1.25rem;
      white-space: nowrap;
      background: #fff;
    }

    .table tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background .15s;
    }
    .table tbody tr:last-child { border-bottom: none; }
    .table tbody tr:hover { background: #FFF7F0; }

    .table tbody td {
      padding: 1rem 1.25rem;
      vertical-align: middle;
      color: var(--text-main);
    }

    /* ── No column ── */
    .col-no {
      font-weight: 600;
      color: var(--text-muted);
      font-size: .8rem;
    }

    /* ── Product cell ── */
    .product-cell {
      display: flex;
      align-items: center;
      gap: .75rem;
    }
    .product-avatar {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      background: #FFF0E6;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      flex-shrink: 0;
    }
    .product-name {
      font-weight: 600;
      font-size: .875rem;
      line-height: 1.3;
    }
    .product-sku {
      font-size: .72rem;
      color: var(--text-muted);
      margin-top: 1px;
    }

    /* ── Price ── */
    .price-label {
      font-weight: 700;
      font-size: .9rem;
      color: var(--text-main);
    }

    /* ── Status badges ── */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      padding: .32rem .75rem;
      border-radius: 50px;
      font-size: .75rem;
      font-weight: 600;
      letter-spacing: .2px;
      white-space: nowrap;
    }
    .status-badge i { font-size: .9rem; }

    .status-active {
      background: #ECFDF5;
      color: #16A34A;
    }
    .status-warning {
      background: #FFFBEB;
      color: #D97706;
    }
    .status-inactive {
      background: #FEF2F2;
      color: #DC2626;
    }

    /* ── Action buttons ── */
    .action-group {
      display: flex;
      gap: .5rem;
    }
    .btn-action {
      width: 34px;
      height: 34px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 9px;
      font-size: .9rem;
      border: 1.5px solid transparent;
      transition: all .18s ease;
    }
    .btn-edit {
      background: #FFF0E6;
      color: var(--bs-primary);
      border-color: #FFD9B8;
    }
    .btn-edit:hover {
      background: var(--bs-primary);
      color: #fff;
      border-color: var(--bs-primary);
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(249,115,22,.3);
    }
    .btn-delete {
      background: #FEF2F2;
      color: #DC2626;
      border-color: #FECACA;
    }
    .btn-delete:hover {
      background: #DC2626;
      color: #fff;
      border-color: #DC2626;
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(220,38,38,.25);
    }

    /* ── Footer / pagination area ── */
    .table-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
      padding: 1rem 1.75rem;
      border-top: 1px solid var(--border);
      font-size: .8rem;
      color: var(--text-muted);
    }

    /* ── Bootstrap primary btn override ── */
    .btn-primary {
      background-color: var(--bs-primary) !important;
      border-color: var(--bs-primary) !important;
      color: #fff !important;
    }
    .btn-primary:hover {
      background-color: var(--bs-primary-hover) !important;
      border-color: var(--bs-primary-hover) !important;
    }
    .btn-primary:focus-visible {
      box-shadow: 0 0 0 3px rgba(249,115,22,.35) !important;
    }

    /* ── Pagination dots ── */
    .pagination .page-link {
      border-radius: 8px !important;
      margin: 0 2px;
      font-size: .8rem;
      font-family: inherit;
      font-weight: 500;
      color: var(--text-muted);
      border: 1.5px solid var(--border);
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      transition: all .15s;
    }
    .pagination .page-link:hover {
      background: #FFF0E6;
      border-color: #FFD9B8;
      color: var(--bs-primary);
    }
    .pagination .page-item.active .page-link {
      background: var(--bs-primary) !important;
      border-color: var(--bs-primary) !important;
      color: #fff !important;
      box-shadow: 0 3px 8px rgba(249,115,22,.3);
    }

    /* ── Empty state ── */
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: var(--text-muted);
    }
    .empty-state i { font-size: 2.5rem; opacity: .35; }

    /* ── Responsive adjustments ── */
    @media (max-width: 576px) {
      .search-wrapper input { width: 160px; }
      .table-card-header { padding: 1.1rem 1.1rem .9rem; }
      .table tbody td, .table thead th { padding: .8rem .9rem; }
    }
  </style>
</head>
<body>

<div class="container-xl">

  <!-- Page heading -->
  <div class="mb-4">
    <h4 class="fw-bold mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;">Manajemen Produk</h4>
    <p class="text-muted" style="font-size:.85rem;margin-top:4px;">Kelola seluruh data produk Anda di sini.</p>
  </div>

  <!-- Table card -->
  <div class="table-card">

    <!-- Header -->
    <div class="table-card-header">
      <div>
        <h5>Daftar Produk</h5>
        <div class="subtitle">Menampilkan 7 dari 7 produk</div>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="search-wrapper">
          <i class="bi bi-search"></i>
          <input type="text" id="searchInput" placeholder="Cari produk..." oninput="filterTable()"/>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" style="border-radius:10px;height:38px;padding:0 1rem;font-size:.85rem;font-weight:600;">
          <i class="bi bi-plus-lg"></i> Tambah
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table align-middle" id="productTable">
        <thead>
          <tr>
            <th style="width:50px;">No</th>
            <th>Product</th>
            <th>Harga</th>
            <th>Status</th>
            <th style="width:100px;">Action</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <!-- rows injected by JS -->
        </tbody>
      </table>

      <!-- Empty state (hidden by default) -->
      <div class="empty-state d-none" id="emptyState">
        <i class="bi bi-inbox d-block mb-2"></i>
        <div class="fw-600">Produk tidak ditemukan</div>
        <div style="font-size:.8rem;margin-top:4px;">Coba ubah kata kunci pencarian Anda.</div>
      </div>
    </div>

    <!-- Footer / pagination -->
    <div class="table-card-footer">
      <span id="footerInfo">Menampilkan 1–7 dari 7 entri</span>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item disabled">
            <a class="page-link" href="#"><i class="bi bi-chevron-left" style="font-size:.7rem;"></i></a>
          </li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">2</a></li>
          <li class="page-item"><a class="page-link" href="#">3</a></li>
          <li class="page-item">
            <a class="page-link" href="#"><i class="bi bi-chevron-right" style="font-size:.7rem;"></i></a>
          </li>
        </ul>
      </nav>
    </div>

  </div><!-- /table-card -->
</div>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  /* ── Data ── */
  const products = [
    { name: 'Kemeja Oxford Slim',  sku: 'KMJ-001', emoji: '👔', price: 'Rp 185.000', status: 'active'   },
    { name: 'Sepatu Kulit Casual', sku: 'SPT-042', emoji: '👟', price: 'Rp 520.000', status: 'warning'  },
    { name: 'Tas Ransel Canvas',   sku: 'TAS-017', emoji: '🎒', price: 'Rp 299.000', status: 'active'   },
    { name: 'Jam Tangan Analog',   sku: 'JAM-009', emoji: '⌚', price: 'Rp 875.000', status: 'inactive' },
    { name: 'Celana Chino Slim',   sku: 'CLN-031', emoji: '👖', price: 'Rp 210.000', status: 'active'   },
    { name: 'Kacamata Photochromic',sku: 'KAC-005',emoji: '🕶️', price: 'Rp 450.000', status: 'warning'  },
    { name: 'Topi Bucket Hat',     sku: 'TOP-022', emoji: '🪣', price: 'Rp 95.000',  status: 'inactive' },
  ];

  const statusConfig = {
    active:   { icon: 'bi-check-circle-fill', label: 'Aktif',     cls: 'status-active'   },
    warning:  { icon: 'bi-exclamation-circle-fill', label: 'Perlu Review', cls: 'status-warning'  },
    inactive: { icon: 'bi-x-circle-fill',     label: 'Nonaktif',  cls: 'status-inactive' },
  };

  /* ── Render rows ── */
  function renderRows(data) {
    const tbody = document.getElementById('tableBody');
    const empty = document.getElementById('emptyState');

    if (!data.length) {
      tbody.innerHTML = '';
      empty.classList.remove('d-none');
      return;
    }
    empty.classList.add('d-none');

    tbody.innerHTML = data.map((p, i) => {
      const s = statusConfig[p.status];
      return `
        <tr>
          <td class="col-no">${String(i + 1).padStart(2, '0')}</td>
          <td>
            <div class="product-cell">
              <div class="product-avatar">${p.emoji}</div>
              <div>
                <div class="product-name">${p.name}</div>
                <div class="product-sku">${p.sku}</div>
              </div>
            </div>
          </td>
          <td><span class="price-label">${p.price}</span></td>
          <td>
            <span class="status-badge ${s.cls}">
              <i class="bi ${s.icon}"></i>${s.label}
            </span>
          </td>
          <td>
            <div class="action-group">
              <button class="btn btn-action btn-edit" title="Edit produk" onclick="handleEdit('${p.sku}')">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button class="btn btn-action btn-delete" title="Hapus produk" onclick="handleDelete(this, '${p.sku}')">
                <i class="bi bi-trash3-fill"></i>
              </button>
            </div>
          </td>
        </tr>`;
    }).join('');
  }

  /* ── Search / filter ── */
  function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const filtered = products.filter(p =>
      p.name.toLowerCase().includes(q) ||
      p.sku.toLowerCase().includes(q)
    );
    renderRows(filtered);
    document.getElementById('footerInfo').textContent =
      `Menampilkan 1–${filtered.length} dari ${filtered.length} entri`;
  }

  /* ── Action handlers ── */
  function handleEdit(sku) {
    alert(`✏️ Edit produk: ${sku}`);
  }

  function handleDelete(btn, sku) {
    if (!confirm(`Hapus produk ${sku}?`)) return;
    const row = btn.closest('tr');
    row.style.transition = 'opacity .3s, transform .3s';
    row.style.opacity = '0';
    row.style.transform = 'translateX(12px)';
    setTimeout(() => {
      const idx = products.findIndex(p => p.sku === sku);
      if (idx > -1) products.splice(idx, 1);
      renderRows(products);
      document.getElementById('footerInfo').textContent =
        `Menampilkan 1–${products.length} dari ${products.length} entri`;
    }, 300);
  }

  /* ── Init ── */
  renderRows(products);
</script>
</body>
</html>