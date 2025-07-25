@extends('layouts.app')
@section('title', 'Products')
@if(auth()->check() && auth()->user()->is_admin)
@section('content')
@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
body {
    font-family: 'Rubik', sans-serif;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.button-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* === Buttons === */
.button-fill {
    position: relative;
    overflow: hidden;
    z-index: 1;
    padding: 8px 16px;
    font-weight: bold;
    border-radius: 999px;
    cursor: pointer;
    border: 2px solid transparent;
    background: transparent;
    transition: color 0.3s ease, border-color 0.3s ease;
}

.button-fill::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 0;
    height: 100%;
    z-index: -1;
    transition: width 0.4s ease;
}

.button-fill:hover::before {
    width: 100%;
    left: 0;
}

.green-button { color: #4caf50; border-color: #4caf50; }
.green-button::before { background: #4caf50; }
.green-button:hover { color: white; }

.blue-button { color: #0d6efd; border-color: #0d6efd; }
.blue-button::before { background: #0d6efd; }
.blue-button:hover { color: white; }

.red-button { color: #dc3545; border-color: #dc3545; }
.red-button::before { background: #dc3545; }
.red-button:hover { color: white; }

.button-fill.active {
    background-color: #dc3545;
    color: white;
}

/* === Filter Tabs === */
.filter-tabs {
    margin: 20px 0;
}

.category-tab {
    position: relative;
    overflow: hidden;
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    background: #eee;
    margin-right: 10px;
    text-decoration: none;
    font-weight: 600;
    color: #333;
    transition: color 0.3s ease, background-color 0.3s ease;
    z-index: 1;
}

.category-tab::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 0;
    background: #007bff;
    z-index: -1;
    transition: width 0.4s ease;
}

.category-tab:hover::before {
    width: 100%;
}
.category-tab:hover { color: white; }

.category-tab.active {
    background: #007bff !important;
    color: white !important;
    z-index: 1 !important;
    position: relative;
    transition: none !important;
}

.category-tab.active::before,
.category-tab.active:hover::before,
.category-tab.active:focus::before,
.category-tab.all-active::before,
.category-tab.all-active:hover::before {
    width: 0 !important;
    background: transparent !important;
    transition: none !important;
}

/* === Table === */
table {
    width: 100%;
    border-collapse: collapse;
    background: #e6f7f7;
    margin-top: 20px;
}
table th, table td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: center;
}
table th {
    background-color: #f0f0f0;
}
table th:last-child,
table td:last-child {
    width: 140px;
}

/* === Actions === */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
    max-width: 160px;
    margin: 0 auto;
}
.action-buttons button {
    padding: 4px 10px;
    font-size: 13px;
    line-height: 1.2;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}
.action-buttons button:first-child {
    background-color: #0d6efd;
    color: white;
}
.action-buttons button:last-child {
    background-color: #dc3545;
    color: white;
}
.action-buttons button:hover {
    opacity: 0.9;
    transform: scale(1.03);
}

/* === Search Form === */
.search-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.search-form input {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 999px;
    width: 400px;
    max-width: 100%;
}
@media (min-width: 1024px) {
    .search-form input {
        width: 500px;
    }
}
.search-form button,
.clear-btn {
    background-color: #4caf50;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 999px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    transition: transform 0.2s ease;
}
.search-form button:hover,
.clear-btn:hover {
    transform: scale(1.05);
}
.clear-btn {
    background-color: #888;
}

/* === Toast === */
.toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #28a745;
    color: white;
    padding: 14px 22px;
    border-radius: 8px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    opacity: 0;
    transform: translateY(40px);
    transition: opacity 0.3s ease, transform 0.3s ease;
    z-index: 2000;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.toast.show {
    opacity: 1;
    transform: translateY(0);
}
.toast button {
    background: white;
    color: #28a745;
    border: none;
    padding: 6px 12px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s ease;
}
.toast button:hover {
    background: #e0e0e0;
}

/* === Low Stock Tags === */
.low-stock-orange { color: orange; font-weight: bold; }
.low-stock-red { color: red; font-weight: bold; }

/* === Modal === */
.modal-overlay,
.modal-box {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s;
}
.modal-overlay.show,
.modal-box.show {
    opacity: 1;
    visibility: visible;
}
.modal-box {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 25px;
    width: 90%;
    max-width: 700px;
    border-radius: 10px;
    z-index: 1100;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.close-btn {
    cursor: pointer;
    font-size: 20px;
    font-weight: bold;
}
.modal-box form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.modal-box label {
    display: block;
    font-weight: 600;
    margin-bottom: 4px;
}
.modal-box input[type="text"],
.modal-box input[type="number"],
.modal-box input[type="date"],
.modal-box select {
    width: 90%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}
.modal-box button[type="submit"] {
    grid-column: span 2;
    padding: 12px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
}
.modal-box button[type="submit"]:hover {
    background: #0056b3;
}
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1099;
    display: none;
}
.modal-overlay.show {
    display: block;
}

/* === Animations === */
.slide-left { animation: smoothSlideLeft 0.4s ease-in-out forwards; }
.slide-right { animation: smoothSlideRight 0.4s ease-in-out forwards; }
@keyframes smoothSlideLeft {
    0% { opacity: 0; transform: translateX(60px); }
    100% { opacity: 1; transform: translateX(0); }
}
@keyframes smoothSlideRight {
    0% { opacity: 0; transform: translateX(-60px); }
    100% { opacity: 1; transform: translateX(0); }
}
.slide-up { animation: slideUp 0.4s ease-in-out forwards; }
@keyframes slideUp {
    0% { opacity: 0; transform: translateY(60px); }
    100% { opacity: 1; transform: translateY(0); }
}

/* === Responsive === */
@media (max-width: 768px) {
    .modal-box form {
        grid-template-columns: 1fr;
    }
    .modal-box {
        width: 85%;
    }
}
/* === Improved Filter Dropdown Styling === */
.filter-btn {
    padding: 10px 20px;
    border: 2px solid #007bff;
    border-radius: 999px;
    background: transparent;
    color: #007bff;
    font-weight: 600;
    font-family: 'Lexend', sans-serif;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: all 0.2s ease-in-out;
}

.filter-btn:hover {
    background-color: #007bff;
    color: white;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.filter-dropdown {
    display: none;
    position: absolute;
    top: 48px;
    right: 0;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 14px;
    padding: 12px 0;
    width: 200px;
    z-index: 200;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.2s ease;
}

.filter-dropdown.show {
    display: block;
}

.filter-dropdown a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    color: #333;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease;
}

.filter-dropdown a:hover {
    background-color: #f0f8ff;
    color: #0d6efd;
}

.filter-dropdown a::before {
    content: '🔹';
    font-size: 12px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
.filter-dropdown a.active-sort {
    font-weight: bold;
    color: #28a745;
    background-color: #e9fcef;
}

    .history-card {
        margin-bottom: 20px;
        border: 1px solid #ddd;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 0 8px rgba(0,0,0,0.06);
        background: white;
        font-family: 'Rubik', sans-serif;
    }

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    font-weight: 500;
}

.details-table table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.details-table th,
.details-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-responsive::-webkit-scrollbar {
    height: 6px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}
.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    list-style: none;
    margin-top: 30px;
    padding: 0;
}

.pagination li a,
.pagination li span {
    display: inline-block;
    padding: 10px 18px;
    border: 2px solid #007bff;
    border-radius: 999px;
    color: #007bff;
    font-weight: bold;
    text-decoration: none;
    transition: all 0.3s ease;
    background: white;
}

.pagination li a:hover {
    background-color: #007bff;
    color: white;
}

.pagination li.active span {
    background-color: #007bff;
    color: white;
    cursor: default;
}

.pagination li.disabled span {
    opacity: 0.5;
    pointer-events: none;
}

</style>

<div class="top-bar">
    <form method="GET" action="{{ route('products.index') }}" class="search-form">
        <input type="text" name="search" placeholder="Search drug name or brand..." value="{{ request('search') }}">
        <button type="submit">Search</button>
        @if(request('search'))
            <a href="{{ route('products.index') }}" class="clear-btn">Clear</a>
        @endif
    </form>
    <div class="button-group">
          @php
                $currentCategory = request('category');
                $currentLowStock = request('low_stock') ? ['low_stock' => 1] : [];
                $sortName = request('sort_name');
                $sortExpiry = request('sort_expiry');
            @endphp

            <div class="filter-dropdown-wrapper">
                <button class="filter-btn" onclick="toggleFilterMenu()">Filter</button>
                <div id="filterMenu" class="filter-dropdown">
                    <a href="{{ route('products.index', array_merge(['category' => $currentCategory], $currentLowStock, ['sort_name' => 'asc'])) }}"
                    class="{{ $sortName === 'asc' ? 'active-sort' : '' }}">Sort: Name A–Z</a>

                    <a href="{{ route('products.index', array_merge(['category' => $currentCategory], $currentLowStock, ['sort_name' => 'desc'])) }}"
                    class="{{ $sortName === 'desc' ? 'active-sort' : '' }}">Sort: Name Z–A</a>

                    <a href="{{ route('products.index', array_merge(['category' => $currentCategory], $currentLowStock, ['sort_expiry' => 'asc'])) }}"
                    class="{{ $sortExpiry === 'asc' ? 'active-sort' : '' }}">Sort: Expiry Asc</a>

                    <a href="{{ route('products.index', array_merge(['category' => $currentCategory], $currentLowStock, ['sort_expiry' => 'desc'])) }}"
                    class="{{ $sortExpiry === 'desc' ? 'active-sort' : '' }}">Sort: Expiry Desc</a>

                    <a href="{{ route('products.index', array_merge(['category' => $currentCategory], request()->except('low_stock'), ['low_stock' => request('low_stock') ? null : 1])) }}"
                    class="{{ request('low_stock') ? 'active-sort' : '' }}">
                        {{ request('low_stock') ? 'Show All Stock' : 'Show Low Stock' }}
                    </a>
                </div>
            </div>
            <button class="button-fill green-button" onclick="openAddModal()">+ Add Product</button>
    </div>
</div>


<!-- Category Tabs -->
@php
    $isLowStock = request()->has('low_stock') ? ['low_stock' => 1] : [];
@endphp

<div class="filter-tabs">
   <a href="{{ route('products.index', array_merge([], $isLowStock)) }}"
   class="category-tab {{ request()->get('category') === null ? 'all-active' : '' }}">All</a>

    <a href="{{ route('products.index', array_merge(['category' => 'medicine'], $isLowStock)) }}"
    class="category-tab {{ request()->get('category') === 'medicine' ? 'active' : '' }}">Medicines</a>

    <a href="{{ route('products.index', array_merge(['category' => 'supplies'], $isLowStock)) }}"
    class="category-tab {{ request()->get('category') === 'supplies' ? 'active' : '' }}">Supplies</a>

</div>





<div id="toast" class="toast">Action completed</div>

<!-- Modals -->
<div id="modalOverlay" class="modal-overlay" onclick="closeModal()"></div>
<div id="modalBox" class="modal-box">
    <div class="modal-header">
        <h2 id="modalTitle">Add Product</h2>
        <span class="close-btn" onclick="closeModal()">&times;</span>
    </div>
    <form id="productForm" class="modal-form">
        @csrf
        <input type="hidden" id="formMethod" value="POST">

        <div>
            <label>Drug Name</label>
            <input type="text" name="name" id="inputName" required>
        </div>

        <div>
            <label>Brand</label>
            <input type="text" name="brand" id="inputBrand" required>
        </div>
        
        <div>
            <label>Selling Price</label>
            <input type="number" name="selling_price" id="inputPrice" step="0.01" required>
        </div>

        <div>
            <label>Supplier Price</label>
            <input type="number" name="supplier_price" id="inputSupplierPrice" step="0.01" required>
        </div>

        

        <div>
            <label>Stocks</label>
            <input type="number" name="stock" id="inputStock" required>
        </div>

        <div>
            <label>Category</label>
            <select name="category" id="inputCategory" required>
                <option value="medicine">Medicine</option>
                <option value="supplies">Supplies</option>
            </select>
        </div>

        <div class="full-width">
            <label>Expiry Date</label>
            <input type="date" name="expiry_date" id="inputExpiryDate" required>
        </div>

        <div class="full-width">
            <button type="submit" id="formButton" class="button-fill blue-button">Add</button>
        </div>
    </form>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteOverlay" class="modal-overlay" onclick="closeDeleteModal()"></div>
<div id="deleteModal" class="modal-box">
    <div class="modal-header">
        <h2>Confirm Deletion</h2>
        <span class="close-btn" onclick="closeDeleteModal()">&times;</span>
    </div>
    <p id="deleteMessage">Are you sure?</p>
    <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
        <button onclick="closeDeleteModal()">Cancel</button>
        <button id="confirmDeleteBtn" class="button-fill red-button">Yes, Delete</button>
    </div>
</div>

<div id="productContainer">
    @include('inventory.partials.table')
</div>

@if(session('deleted_product_id'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        showToast("{{ session('deleted_product_name') }} deleted.", false, {{ session('deleted_product_id') }});
    });
</script>
@endif

<script>
let deleteTargetId = null;
let currentPage = 1;

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Add New Product';
    document.getElementById('formButton').innerText = 'Add';
    document.getElementById('productForm').action = '/products';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('inputName').value = '';
    document.getElementById('inputBrand').value = '';
    document.getElementById('inputSupplierPrice').value = '';
    document.getElementById('inputPrice').value = '';
    document.getElementById('inputStock').value = '';
    document.getElementById('inputCategory').value = 'medicine';
    document.getElementById('inputExpiryDate').value = '';
    showModal();
    setTimeout(() => document.getElementById('inputName').focus(), 100);
}

function openEditModal(product) {
    document.getElementById('modalTitle').innerText = 'Edit Product';
    document.getElementById('formButton').innerText = 'Update';
    document.getElementById('productForm').action = `/products/${product.id}`;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('inputName').value = product.name;
    document.getElementById('inputBrand').value = product.brand;
    document.getElementById('inputSupplierPrice').value = product.supplier_price;
    document.getElementById('inputPrice').value = product.selling_price;
    document.getElementById('inputStock').value = product.stock;
    document.getElementById('inputCategory').value = product.category;
    document.getElementById('inputExpiryDate').value = product.expiry_date;
    showModal();
}

function showModal() {
    document.getElementById('modalOverlay').classList.add('show');
    document.getElementById('modalBox').classList.add('show');
}
function closeModal() {
    document.getElementById('modalOverlay').classList.remove('show');
    document.getElementById('modalBox').classList.remove('show');
}

function triggerDelete(id, name) {
    deleteTargetId = id;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${name}"?`;
    document.getElementById('deleteModal').classList.add('show');
    document.getElementById('deleteOverlay').classList.add('show');
}

function closeDeleteModal() {
    deleteTargetId = null;
    document.getElementById('deleteModal').classList.remove('show');
    document.getElementById('deleteOverlay').classList.remove('show');
}

function undoDelete(id) {
    fetch(`/products/${id}/restore`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message || "Undo complete.");
        location.reload();
    })
    .catch(() => showToast("Undo failed.", true));
}

function showToast(message, isError = false, undoId = null) {
    const toast = document.getElementById('toast');
    toast.innerHTML = message;
    if (undoId) {
        toast.innerHTML += ` <button onclick="undoDelete(${undoId})" style="margin-left:10px;">Undo</button>`;
    }
    toast.style.background = isError ? '#dc3545' : '#28a745';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 5000);
}

document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const url = form.action;
    const method = document.getElementById('formMethod').value;

    const formData = {
        name: document.getElementById('inputName').value,
        brand: document.getElementById('inputBrand').value,
        supplier_price: document.getElementById('inputSupplierPrice').value,
        selling_price: document.getElementById('inputPrice').value,
        stock: document.getElementById('inputStock').value,
        category: document.getElementById('inputCategory').value,
        expiry_date: document.getElementById('inputExpiryDate').value,
        _token: '{{ csrf_token() }}',
        _method: method
    };

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message || 'Saved!');
        closeModal();
        location.reload();
    })
    .catch(() => showToast('Failed to save.', true));
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
    if (!deleteTargetId) return;
    fetch(`/products/${deleteTargetId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ _method: 'DELETE' })
    })
    .then(res => res.ok ? res.json() : Promise.reject(res))
    .then(() => location.reload())
    .catch(() => showToast("Delete failed.", true));
});

// Filters
function toggleFilterMenu() {
    const menu = document.getElementById('filterMenu');
    menu.classList.toggle('show');
}

function applyFilters() {
    const nameSort = document.getElementById('sortName').value;
    const expirySort = document.getElementById('sortExpiry').value;
    const stock = document.getElementById('stockFilter').value;

    const params = new URLSearchParams(window.location.search);
    nameSort ? params.set('sort_name', nameSort) : params.delete('sort_name');
    expirySort ? params.set('sort_expiry', expirySort) : params.delete('sort_expiry');
    stock === 'low' ? params.set('low_stock', '1') : params.delete('low_stock');

    window.location.href = `?${params.toString()}`;
}

// On load tab + pagination logic
document.addEventListener('DOMContentLoaded', function () {
    let currentPage = parseInt(new URL(window.location.href).searchParams.get('page')) || 1;
    const lowStockBtn = document.getElementById('filterLowStock');
    const lowStockParam = new URLSearchParams(window.location.search).get('low_stock');
    if (lowStockBtn) {
        lowStockBtn.innerText = lowStockParam === '1' ? 'Show All' : 'Show Low Stock';
    }

    const allTab = document.querySelector('.category-tab.all-active');
    if (allTab) {
        allTab.classList.remove('all-active');
        void allTab.offsetWidth;
        allTab.classList.add('all-active');
    }

    document.addEventListener('click', function (e) {
        const categoryBtn = e.target.closest('.filter-tabs a');
        if (categoryBtn) {
            e.preventDefault();
            const url = new URL(categoryBtn.href);
            if (lowStockBtn?.innerText === 'Show All') {
                url.searchParams.set('low_stock', '1');
            } else {
                url.searchParams.delete('low_stock');
            }

            fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text())
            .then(html => {
                const container = document.getElementById('productContainer');
                container.innerHTML = html;
                container.classList.remove('slide-up');
                void container.offsetWidth;
                container.classList.add('slide-up');

                document.querySelectorAll('.category-tab').forEach(tab => tab.classList.remove('active', 'all-active'));
                if (categoryBtn.innerText.trim().toLowerCase() === 'all') {
                    categoryBtn.classList.add('all-active');
                } else {
                    categoryBtn.classList.add('active');
                }
            });
            return;
        }

        const paginationLink = e.target.closest('.pagination a');
        if (paginationLink) {
            e.preventDefault();
            const link = paginationLink.href;
            const page = new URL(link).searchParams.get('page');
            const direction = parseInt(page) > currentPage ? 'slide-left' : 'slide-right';
            const container = document.getElementById('productContainer');

            fetch(link, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                container.classList.remove('slide-left', 'slide-right');
                void container.offsetWidth;
                container.classList.add(direction);
                currentPage = parseInt(page);
            });
        }
    });
});

function toggleFilterMenu() {
    const menu = document.getElementById('filterMenu');
    menu.classList.toggle('show');

    // Close if clicked outside
    document.addEventListener('click', function handleClickOutside(event) {
        if (!menu.contains(event.target) && !event.target.closest('.filter-btn')) {
            menu.classList.remove('show');
            document.removeEventListener('click', handleClickOutside);
        }
    });
}
</script>


@endsection
@endif