// File: public/js/kasir.js
let cart = [];
let selectedPelanggan = null;

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

const searchProduk = document.getElementById('searchProduk');
const searchResults = document.getElementById('searchResults');
const barcodeInput = document.getElementById('barcodeInput');
const BASE_URL = document.getElementById('baseUrl').value;

searchProduk.addEventListener('input', debounce(function() {
    const keyword = this.value.trim();
    if (keyword.length < 2) {
        searchResults.style.display = 'none';
        return;
    }
    fetch(`${BASE_URL}/api/produk/search?keyword=${encodeURIComponent(keyword)}`)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                searchResults.innerHTML = '<div class="list-group-item text-muted">Produk tidak ditemukan</div>';
            } else {
                searchResults.innerHTML = data.map(p => `
                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="addToCart(${p.id}, '${p.nama.replace(/'/g, "\\'")}', ${p.harga_jual}, ${p.stok}); return false;">
                        <div><strong>${p.nama}</strong><br><small class="text-muted">${p.kode} | Stok: ${p.stok}</small></div>
                        <span class="badge bg-primary rounded-pill">Rp ${p.harga_jual.toLocaleString('id-ID')}</span>
                    </a>
                `).join('');
            }
            searchResults.style.display = 'block';
        });
}, 300));

barcodeInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const barcode = this.value.trim();
        if (barcode) {
            fetch(`${BASE_URL}/api/produk/barcode/${barcode}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        Swal.fire('Error', 'Produk tidak ditemukan', 'warning');
                    } else {
                        addToCart(data.id, data.nama, data.harga_jual, data.stok);
                    }
                    barcodeInput.value = '';
                });
        }
    }
});

document.addEventListener('click', function(e) {
    if (!searchProduk.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

function addToCart(id, nama, harga, stok) {
    if (stok <= 0) { Swal.fire('Error', 'Stok produk habis!', 'warning'); return; }
    const existing = cart.find(item => item.id === id);
    if (existing) {
        if (existing.qty >= stok) { Swal.fire('Error', 'Stok tidak mencukupi!', 'warning'); return; }
        existing.qty++;
        existing.subtotal = existing.qty * existing.harga_jual;
    } else {
        cart.push({ id: id, nama: nama, harga_jual: harga, qty: 1, subtotal: harga, stok: stok });
    }
    searchProduk.value = '';
    searchResults.style.display = 'none';
    renderCart();
    hitungTotal();
}

function removeFromCart(index) { cart.splice(index, 1); renderCart(); hitungTotal(); }

function updateQty(index, newQty) {
    newQty = parseInt(newQty);
    if (isNaN(newQty) || newQty < 1) { cart.splice(index, 1); }
    else if (newQty > cart[index].stok) {
        Swal.fire('Error', 'Stok tidak mencukupi! Maks: ' + cart[index].stok, 'warning');
        cart[index].qty = cart[index].stok;
        cart[index].subtotal = cart[index].qty * cart[index].harga_jual;
    } else {
        cart[index].qty = newQty;
        cart[index].subtotal = newQty * cart[index].harga_jual;
    }
    renderCart(); hitungTotal();
}

function clearCart() {
    if (cart.length === 0) return;
    Swal.fire({ title: 'Kosongkan Keranjang?', text: 'Semua item akan dihapus', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal' }).then(result => { if (result.isConfirmed) { cart = []; renderCart(); hitungTotal(); } });
}

function renderCart() {
    const cartBody = document.getElementById('cartBody');
    if (cart.length === 0) {
        cartBody.innerHTML = `<tr id="emptyCart"><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-cart-x display-4"></i><p class="mt-2">Keranjang masih kosong</p></td></tr>`;
        return;
    }
    cartBody.innerHTML = cart.map((item, index) => `
        <tr>
            <td><strong>${item.nama}</strong></td>
            <td>Rp ${item.harga_jual.toLocaleString('id-ID')}</td>
            <td><input type="number" class="form-control form-control-sm" value="${item.qty}" min="1" max="${item.stok}" onchange="updateQty(${index}, this.value)" style="width:70px;"></td>
            <td class="fw-semibold">Rp ${item.subtotal.toLocaleString('id-ID')}</td>
            <td><button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})"><i class="bi bi-x-lg"></i></button></td>
        </tr>
    `).join('');
}

function hitungTotal() {
    const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const diskon = parseInt(document.getElementById('inputDiskon').value) || 0;
    const pajak = parseInt(document.getElementById('inputPajak').value) || 0;
    const grandTotal = subtotal - diskon + pajak;
    document.getElementById('displaySubtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('displayGrandTotal').textContent = 'Rp ' + Math.max(0, grandTotal).toLocaleString('id-ID');
    hitungKembalian();
}

function hitungKembalian() {
    const grandTotalText = document.getElementById('displayGrandTotal').textContent.replace(/[^0-9]/g, '');
    const grandTotal = parseInt(grandTotalText) || 0;
    const bayar = parseInt(document.getElementById('inputBayar').value) || 0;
    const kembalian = bayar - grandTotal;
    const divKembalian = document.getElementById('divKembalian');
    const displayKembalian = document.getElementById('displayKembalian');
    if (bayar > 0) {
        divKembalian.style.display = 'block';
        displayKembalian.textContent = 'Rp ' + kembalian.toLocaleString('id-ID');
        displayKembalian.className = kembalian >= 0 ? 'text-success mb-0' : 'text-danger mb-0';
    } else {
        divKembalian.style.display = 'none';
    }
}

function toggleBayar() {
    const metode = document.getElementById('metodeBayar').value;
    const divBayar = document.getElementById('divBayar');
    if (metode === 'tunai') {
        divBayar.style.display = 'block';
    } else {
        divBayar.style.display = 'none';
        document.getElementById('inputBayar').value = '';
        document.getElementById('divKembalian').style.display = 'none';
    }
}

function prosesTransaksi() {
    if (cart.length === 0) { Swal.fire('Error', 'Keranjang masih kosong!', 'warning'); return; }
    const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const diskon = parseInt(document.getElementById('inputDiskon').value) || 0;
    const pajak = parseInt(document.getElementById('inputPajak').value) || 0;
    const grandTotal = Math.max(0, subtotal - diskon + pajak);
    const metode = document.getElementById('metodeBayar').value;
    const bayar = metode === 'tunai' ? (parseInt(document.getElementById('inputBayar').value) || 0) : grandTotal;
    if (metode === 'tunai' && bayar < grandTotal) { Swal.fire('Error', 'Pembayaran kurang!', 'warning'); return; }
    const pelangganSelect = document.getElementById('selectPelanggan');
    const idPelanggan = pelangganSelect.value || null;
    const catatan = document.getElementById('inputCatatan').value;
    const csrfToken = document.getElementById('csrfToken').value;

    const data = {
        csrf_token: csrfToken,
        items: cart,
        total: subtotal,
        diskon: diskon,
        pajak: pajak,
        grand_total: grandTotal,
        bayar: bayar,
        metode_pembayaran: metode,
        id_pelanggan: idPelanggan,
        catatan: catatan
    };

    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    fetch(`${BASE_URL}/transaksi/store`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.error) {
            Swal.fire('Error', result.error, 'error');
        } else {
            Swal.fire({
                title: 'Transaksi Berhasil!',
                text: `Invoice: ${result.no_invoice}`,
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Cetak Struk',
                cancelButtonText: 'Selesai'
            }).then(swalResult => {
                if (swalResult.isConfirmed) {
                    window.open(`${BASE_URL}/transaksi/cetak/${result.transaksi.id}`, '_blank');
                }
                cart = [];
                renderCart();
                hitungTotal();
                document.getElementById('inputDiskon').value = 0;
                document.getElementById('inputPajak').value = 0;
                document.getElementById('inputBayar').value = '';
                document.getElementById('divKembalian').style.display = 'none';
                document.getElementById('inputCatatan').value = '';
                document.getElementById('searchProduk').focus();
                // Update CSRF token untuk transaksi berikutnya
                if (result.new_csrf_token) {
                    document.getElementById('csrfToken').value = result.new_csrf_token;
                }
            });
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Gagal memproses transaksi', 'error');
        console.error(err);
    });
}

renderCart();