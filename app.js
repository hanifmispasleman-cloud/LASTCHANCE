/* =============================================================
   KasirKu – AI POS Multi-Tenant | app.js
   React 18 + Babel Standalone + Dexie.js (via window.db)
   Routing: State-based internal (NO BrowserRouter / History API)
   Kompatibel: GitHub Pages (static, no server-side routing)
   ============================================================= */

/* ── Destruct React hooks dari global window.React ── */
const {
  useState, useEffect, useRef, useCallback, useMemo, Fragment
} = React;

/* ── Akses DB & prefix dari db.js ── */
const STORAGE_PREFIX = window.STORAGE_PREFIX || 'kasirku_ghpages_v1_';

/* ─────────────────────────────────────────────────────────────
   HELPERS
───────────────────────────────────────────────────────────── */
const formatRp = (n) =>
  'Rp\u00A0' + Math.round(n || 0).toLocaleString('id-ID');

const formatDate = (ts) =>
  new Date(ts).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });

const uid = () =>
  Date.now().toString(36) + Math.random().toString(36).slice(2, 7);

const saveSession = (user, business) => {
  window.lsSet('session', { user, business });
};
const clearSession = () => window.lsDel('session');

/* ─────────────────────────────────────────────────────────────
   TOAST HOOK
───────────────────────────────────────────────────────────── */
function useToast() {
  const [toast, setToast] = useState(null);
  const show = useCallback((msg, type = 'success') => {
    setToast({ msg, type, id: uid() });
    setTimeout(() => setToast(null), 3200);
  }, []);
  return { toast, show };
}

/* ─────────────────────────────────────────────────────────────
   TOAST COMPONENT
───────────────────────────────────────────────────────────── */
function Toast({ toast }) {
  if (!toast) return null;
  const pal = {
    success: 'bg-green-600 text-white',
    error:   'bg-red-600   text-white',
    info:    'bg-blue-600  text-white',
    warning: 'bg-yellow-500 text-white',
  };
  return (
    <div
      className={`fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-5 py-3
                  rounded-2xl text-sm font-semibold shadow-2xl toast-enter
                  ${pal[toast.type] || pal.info}`}
    >
      {toast.type === 'success' && <span>✓</span>}
      {toast.type === 'error'   && <span>✕</span>}
      {toast.type === 'warning' && <span>⚠</span>}
      {toast.msg}
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   MODAL COMPONENT
───────────────────────────────────────────────────────────── */
function Modal({ open, onClose, title, children, wide }) {
  if (!open) return null;
  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center modal-overlay bg-black/40"
      onClick={onClose}
    >
      <div
        className={`bg-white rounded-2xl shadow-2xl w-full mx-4
                    max-h-[90vh] overflow-y-auto page-enter
                    ${wide ? 'max-w-2xl' : 'max-w-md'}`}
        onClick={(e) => e.stopPropagation()}
      >
        <div className="flex items-center justify-between p-5 border-b border-gray-100">
          <h2 className="text-base font-bold text-gray-800">{title}</h2>
          <button
            onClick={onClose}
            className="w-8 h-8 flex items-center justify-center text-gray-400
                       hover:text-gray-600 hover:bg-gray-100 rounded-xl text-xl transition-colors"
          >
            &times;
          </button>
        </div>
        <div className="p-5">{children}</div>
      </div>
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   SPINNER
───────────────────────────────────────────────────────────── */
function Spinner({ small }) {
  const sz = small ? 'w-5 h-5 border-2' : 'w-9 h-9 border-4';
  return (
    <div className={small ? 'inline-flex' : 'flex items-center justify-center p-8'}>
      <div className={`${sz} border-green-500 border-t-transparent rounded-full spinner`} />
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   EMPTY STATE
───────────────────────────────────────────────────────────── */
function Empty({ icon, label }) {
  return (
    <div className="flex flex-col items-center justify-center py-16 text-gray-400">
      <div className="text-5xl mb-3">{icon || '📭'}</div>
      <p className="text-sm">{label || 'Belum ada data.'}</p>
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   AUTH SCREEN – Login & Registrasi
───────────────────────────────────────────────────────────── */
function AuthScreen({ onLogin }) {
  const [mode, setMode]  = useState('login');
  const [form, setForm]  = useState({ username: '', password: '', confirm: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError]     = useState('');
  const { toast, show } = useToast();

  const hf = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }));

  async function doLogin() {
    setError('');
    if (!form.username || !form.password) { setError('Username dan password wajib diisi.'); return; }
    setLoading(true);
    try {
      const user = await window.db.users
        .where('username').equalsIgnoreCase(form.username.trim()).first();
      if (!user || user.password !== form.password) {
        setError('Username atau password tidak cocok.'); setLoading(false); return;
      }
      show('Selamat datang, ' + user.username + '! 👋');
      setTimeout(() => onLogin(user), 500);
    } catch (e) { setError('Error: ' + e.message); }
    setLoading(false);
  }

  async function doRegister() {
    setError('');
    if (!form.username || !form.password) { setError('Username dan password wajib diisi.'); return; }
    if (form.password !== form.confirm)    { setError('Konfirmasi password tidak cocok.'); return; }
    if (form.password.length < 4)          { setError('Password minimal 4 karakter.'); return; }
    setLoading(true);
    try {
      const exists = await window.db.users
        .where('username').equalsIgnoreCase(form.username.trim()).first();
      if (exists) { setError('Username sudah terdaftar.'); setLoading(false); return; }
      const id = uid();
      await window.db.users.add({
        id, username: form.username.trim(), password: form.password, createdAt: Date.now(),
      });
      const user = await window.db.users.get(id);
      show('Akun berhasil dibuat!');
      setTimeout(() => onLogin(user), 500);
    } catch (e) { setError('Error: ' + e.message); }
    setLoading(false);
  }

  const submit = mode === 'login' ? doLogin : doRegister;

  return (
    <div className="min-h-screen flex items-center justify-center
                    bg-gradient-to-br from-green-700 via-green-600 to-emerald-500 p-4">
      <Toast toast={toast} />
      <div className="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-8 page-enter">
        {/* Logo */}
        <div className="text-center mb-7">
          <div className="inline-flex items-center justify-center w-16 h-16
                          bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl mb-3 shadow-lg">
            <svg className="w-9 h-9 text-white" fill="none" stroke="currentColor"
                 strokeWidth="2" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13l-1-4H3
                   m10 4a2 2 0 100 4 2 2 0 000-4zm-6 0a2 2 0 100 4 2 2 0 000-4z" />
            </svg>
          </div>
          <h1 className="text-2xl font-black text-gray-800 tracking-tight">KasirKu</h1>
          <p className="text-xs text-gray-400 mt-0.5">AI POS Multi-Tenant</p>
        </div>

        {/* Tab */}
        <div className="flex bg-gray-100 rounded-xl p-1 mb-6">
          {[['login','Masuk'], ['register','Daftar']].map(([m, l]) => (
            <button key={m} onClick={() => { setMode(m); setError(''); }}
              className={`flex-1 py-2 text-sm font-semibold rounded-lg transition-all
                          ${mode === m
                            ? 'bg-white shadow text-green-700'
                            : 'text-gray-500 hover:text-gray-700'}`}>
              {l}
            </button>
          ))}
        </div>

        {error && (
          <div className="mb-4 px-4 py-3 bg-red-50 border border-red-100
                          text-red-700 text-sm rounded-xl">
            {error}
          </div>
        )}

        <div className="space-y-4">
          <div>
            <label htmlFor="auth-user" className="block text-xs font-semibold text-gray-600 mb-1.5">
              Username
            </label>
            <input
              id="auth-user" type="text" autoComplete="username"
              value={form.username} onChange={hf('username')}
              onKeyDown={(e) => e.key === 'Enter' && submit()}
              placeholder="Masukkan username"
              className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                         focus:outline-none focus:ring-2 focus:ring-green-400"
            />
          </div>
          <div>
            <label htmlFor="auth-pass" className="block text-xs font-semibold text-gray-600 mb-1.5">
              Password
            </label>
            <input
              id="auth-pass" type="password" autoComplete={mode === 'login' ? 'current-password' : 'new-password'}
              value={form.password} onChange={hf('password')}
              onKeyDown={(e) => e.key === 'Enter' && submit()}
              placeholder="Masukkan password"
              className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                         focus:outline-none focus:ring-2 focus:ring-green-400"
            />
          </div>
          {mode === 'register' && (
            <div>
              <label htmlFor="auth-confirm" className="block text-xs font-semibold text-gray-600 mb-1.5">
                Konfirmasi Password
              </label>
              <input
                id="auth-confirm" type="password" autoComplete="new-password"
                value={form.confirm} onChange={hf('confirm')}
                onKeyDown={(e) => e.key === 'Enter' && doRegister()}
                placeholder="Ulangi password"
                className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-green-400"
              />
            </div>
          )}
          <button
            onClick={submit} disabled={loading}
            className="w-full py-3 bg-gradient-to-r from-green-600 to-emerald-500
                       text-white font-black rounded-xl text-sm shadow-lg
                       hover:opacity-90 transition-opacity disabled:opacity-50
                       flex items-center justify-center gap-2"
          >
            {loading && <Spinner small />}
            {loading ? 'Memproses...' : (mode === 'login' ? 'Masuk' : 'Buat Akun')}
          </button>
        </div>
        <p className="text-center text-xs text-gray-400 mt-6">
          Data tersimpan lokal di browser Anda (IndexedDB)
        </p>
      </div>
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   BUSINESS SELECTOR
───────────────────────────────────────────────────────────── */
function BusinessSelector({ user, onSelect, onLogout }) {
  const [businesses, setBusinesses] = useState([]);
  const [loading, setLoading]       = useState(true);
  const [showForm, setShowForm]     = useState(false);
  const [form, setForm] = useState({ name: '', type: 'F&B', address: '' });
  const { toast, show } = useToast();

  useEffect(() => { loadBiz(); }, []);

  async function loadBiz() {
    setLoading(true);
    const list = await window.db.businesses.where('userId').equals(user.id).toArray();
    setBusinesses(list);
    setLoading(false);
  }

  async function createBiz() {
    if (!form.name.trim()) { show('Nama bisnis wajib diisi', 'error'); return; }
    const b = {
      id: uid(), userId: user.id, name: form.name.trim(),
      type: form.type, address: form.address.trim(), createdAt: Date.now(),
    };
    await window.db.businesses.add(b);
    show('Bisnis berhasil dibuat!');
    setForm({ name: '', type: 'F&B', address: '' });
    setShowForm(false);
    loadBiz();
  }

  const TYPES  = ['F&B', 'Ritel', 'Otomotif', 'Jasa', 'Lainnya'];
  const ICONS  = { 'F&B':'🍽️', 'Ritel':'🛒', 'Otomotif':'🔧', 'Jasa':'💼', 'Lainnya':'🏪' };

  return (
    <div className="min-h-screen bg-gradient-to-br from-green-700 via-green-600
                    to-emerald-500 p-4 flex flex-col items-center justify-center">
      <Toast toast={toast} />
      <div className="w-full max-w-lg">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-2xl font-black text-white">Pilih Profil Bisnis</h1>
            <p className="text-green-100 text-sm mt-0.5">
              Halo, <span className="font-bold">{user.username}</span>!
            </p>
          </div>
          <button
            onClick={onLogout}
            className="text-white/80 hover:text-white text-sm border border-white/30
                       hover:border-white/60 px-3 py-1.5 rounded-lg transition-all"
          >
            Keluar
          </button>
        </div>

        {loading ? <Spinner /> : (
          <div className="space-y-3">
            {businesses.map((b) => (
              <button
                key={b.id} onClick={() => onSelect(b)}
                className="w-full bg-white rounded-2xl p-5 text-left shadow-lg
                           hover:shadow-xl transition-all flex items-center gap-4 group"
              >
                <span className="text-3xl">{ICONS[b.type] || '🏪'}</span>
                <div className="flex-1 min-w-0">
                  <p className="font-bold text-gray-800 group-hover:text-green-700 transition-colors truncate">
                    {b.name}
                  </p>
                  <p className="text-xs text-gray-400 mt-0.5">
                    {b.type}{b.address ? ' · ' + b.address : ''}
                  </p>
                </div>
                <svg className="w-5 h-5 text-gray-300 group-hover:text-green-500
                                transition-colors flex-shrink-0"
                     fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            ))}

            {!showForm ? (
              <button
                onClick={() => setShowForm(true)}
                className="w-full border-2 border-dashed border-white/50 text-white
                           rounded-2xl p-5 text-sm font-semibold hover:bg-white/10
                           transition-colors flex items-center justify-center gap-2"
              >
                <span className="text-xl font-light">+</span> Tambah Bisnis Baru
              </button>
            ) : (
              <div className="bg-white rounded-2xl p-5 shadow-xl page-enter">
                <h3 className="font-bold text-gray-800 mb-4">Bisnis Baru</h3>
                <div className="space-y-3">
                  <input
                    type="text" value={form.name}
                    onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
                    placeholder="Nama Bisnis *"
                    className="w-full border border-gray-200 rounded-xl px-4 py-2.5
                               text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                  />
                  <select
                    value={form.type}
                    onChange={(e) => setForm((f) => ({ ...f, type: e.target.value }))}
                    className="w-full border border-gray-200 rounded-xl px-4 py-2.5
                               text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                  >
                    {TYPES.map((t) => (
                      <option key={t} value={t}>{ICONS[t]} {t}</option>
                    ))}
                  </select>
                  <input
                    type="text" value={form.address}
                    onChange={(e) => setForm((f) => ({ ...f, address: e.target.value }))}
                    placeholder="Alamat (opsional)"
                    className="w-full border border-gray-200 rounded-xl px-4 py-2.5
                               text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                  />
                  <div className="flex gap-2 pt-1">
                    <button
                      onClick={() => setShowForm(false)}
                      className="flex-1 py-2.5 border border-gray-200 rounded-xl
                                 text-sm text-gray-600 hover:bg-gray-50"
                    >
                      Batal
                    </button>
                    <button
                      onClick={createBiz}
                      className="flex-1 py-2.5 bg-green-600 text-white rounded-xl
                                 text-sm font-bold hover:bg-green-700"
                    >
                      Buat
                    </button>
                  </div>
                </div>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   MAIN APP SHELL – Sidebar + Page Router
───────────────────────────────────────────────────────────── */
function MainApp({ user, business, onLogout, onSwitchBusiness }) {
  const [page, setPage] = useState('pos');
  const { toast, show } = useToast();

  const NAV = [
    {
      id: 'pos', label: 'Kasir / POS',
      d: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13l-1-4H3m10 4a2 2 0 100 4 2 2 0 000-4zm-6 0a2 2 0 100 4 2 2 0 000-4z',
    },
    {
      id: 'inventory', label: 'Gudang & Bahan',
      d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    },
    {
      id: 'menu', label: 'Menu & Resep',
      d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    },
    {
      id: 'opex', label: 'OPEX',
      d: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
    },
    {
      id: 'reports', label: 'Laporan Live',
      d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    },
    {
      id: 'ai', label: 'AI Diagnostic',
      d: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2',
    },
  ];

  const PAGE_MAP = {
    pos:       POSModule,
    inventory: InventoryModule,
    menu:      MenuModule,
    opex:      OPEXModule,
    reports:   ReportsModule,
    ai:        AIDiagnosticModule,
  };
  const PageComponent = PAGE_MAP[page] || POSModule;

  return (
    <div className="flex h-screen bg-gray-50 overflow-hidden">
      <Toast toast={toast} />

      {/* ── Sidebar ── */}
      <aside className="w-52 bg-white border-r border-gray-100 flex flex-col shadow-sm flex-shrink-0">
        {/* Header */}
        <div className="p-4 border-b border-gray-100">
          <div className="flex items-center gap-2.5 mb-2">
            <div className="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-600
                            rounded-lg flex items-center justify-center flex-shrink-0">
              <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor"
                   strokeWidth="2.5" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13" />
              </svg>
            </div>
            <span className="font-black text-gray-800">KasirKu</span>
          </div>
          <button
            onClick={onSwitchBusiness}
            title="Klik untuk ganti bisnis"
            className="w-full text-left group"
          >
            <p className="text-xs font-bold text-green-700 truncate
                          group-hover:text-green-800 transition-colors">
              {business.name}
            </p>
            <p className="text-[10px] text-gray-400">{business.type} · Ganti Bisnis</p>
          </button>
        </div>

        {/* Nav */}
        <nav className="flex-1 p-2.5 space-y-0.5 overflow-y-auto">
          {NAV.map((item) => (
            <button
              key={item.id}
              onClick={() => setPage(item.id)}
              className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl
                          text-sm font-semibold transition-all
                          ${page === item.id
                            ? 'nav-active'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800'}`}
            >
              <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                   strokeWidth="2" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" d={item.d} />
              </svg>
              <span className="truncate">{item.label}</span>
            </button>
          ))}
        </nav>

        {/* Footer */}
        <div className="p-2.5 border-t border-gray-100">
          <div className="px-3 py-2 mb-0.5">
            <p className="text-xs font-semibold text-gray-700 truncate">{user.username}</p>
            <p className="text-[10px] text-gray-400">Pemilik</p>
          </div>
          <button
            onClick={onLogout}
            className="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl
                       text-sm text-red-500 hover:bg-red-50 font-semibold transition-colors"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor"
                 strokeWidth="2" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Log Out
          </button>
        </div>
      </aside>

      {/* ── Main Content ── */}
      <main className="flex-1 overflow-y-auto">
        <PageComponent business={business} user={user} showToast={show} />
      </main>
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   POS MODULE – Kasir + Keranjang + Checkout + Struk
───────────────────────────────────────────────────────────── */
function POSModule({ business, showToast }) {
  const [products,    setProducts]    = useState([]);
  const [cart,        setCart]        = useState([]);
  const [category,    setCategory]    = useState('Semua');
  const [search,      setSearch]      = useState('');
  const [payMethod,   setPayMethod]   = useState('Tunai');
  const [cashIn,      setCashIn]      = useState('');
  const [receipt,     setReceipt]     = useState(null);
  const [showReceipt, setShowReceipt] = useState(false);

  useEffect(() => { loadProducts(); }, [business.id]);

  async function loadProducts() {
    const list = await window.db.products
      .where('businessId').equals(business.id)
      .filter((p) => p.active !== false)
      .toArray();
    setProducts(list);
  }

  const categories = useMemo(() =>
    ['Semua', ...new Set(products.map((p) => p.category).filter(Boolean))],
    [products]
  );

  const filtered = useMemo(() =>
    products.filter((p) =>
      (category === 'Semua' || p.category === category) &&
      p.name.toLowerCase().includes(search.toLowerCase())
    ),
    [products, category, search]
  );

  function addToCart(product) {
    setCart((prev) => {
      const idx = prev.findIndex((c) => c.id === product.id);
      if (idx >= 0) {
        const next = [...prev];
        next[idx] = { ...next[idx], qty: next[idx].qty + 1 };
        return next;
      }
      return [...prev, { ...product, qty: 1 }];
    });
  }

  function setQty(id, val) {
    setCart((prev) =>
      prev.map((c) => c.id === id ? { ...c, qty: Math.max(0, val) } : c)
          .filter((c) => c.qty > 0)
    );
  }

  const cartTotal    = useMemo(() => cart.reduce((s, c) => s + c.price * c.qty, 0), [cart]);
  const cartHPP      = useMemo(() => cart.reduce((s, c) => s + (c.hpp || 0) * c.qty, 0), [cart]);
  const grossProfit  = cartTotal - cartHPP;
  const cashInNum    = parseFloat(cashIn) || 0;
  const change       = Math.max(0, cashInNum - cartTotal);
  const cartCount    = useMemo(() => cart.reduce((s, c) => s + c.qty, 0), [cart]);

  async function checkout() {
    if (!cart.length) { showToast('Keranjang masih kosong!', 'error'); return; }
    if (payMethod === 'Tunai' && cashIn && cashInNum < cartTotal) {
      showToast('Uang diterima kurang dari total!', 'error'); return;
    }

    const txId  = uid();
    const items = cart.map((c) => ({
      id: uid(), transactionId: txId,
      productId: c.id, productName: c.name,
      qty: c.qty, price: c.price, hpp: c.hpp || 0,
    }));
    const tx = {
      id: txId, businessId: business.id, date: Date.now(),
      total: cartTotal, totalHPP: cartHPP,
      grossProfit, paymentMethod: payMethod, status: 'done',
    };

    await window.db.transactions.add(tx);
    await window.db.transaction_items.bulkAdd(items);

    /* ── Potong stok bahan baku sesuai resep ── */
    for (const item of cart) {
      const recipes = await window.db.recipes
        .where('productId').equals(item.id).toArray();
      for (const r of recipes) {
        const ing = await window.db.ingredients.get(r.ingredientId);
        if (ing) {
          await window.db.ingredients.update(r.ingredientId, {
            stock: Math.max(0, ing.stock - r.quantity * item.qty),
          });
        }
      }
    }

    setReceipt({
      ...tx, items,
      businessName: business.name,
      cashIn: cashInNum || cartTotal,
      change,
    });
    setShowReceipt(true);
    setCart([]);
    setCashIn('');
    showToast('Transaksi berhasil! ✓');
  }

  const EMOJI_FALLBACK = '🍽️';

  return (
    <div className="flex h-full">
      {/* ── Grid Produk ── */}
      <div className="flex-1 flex flex-col p-5 gap-3 overflow-hidden">
        {/* Search */}
        <div className="flex gap-2">
          <div className="flex-1 relative">
            <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                 fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.35-4.35" />
            </svg>
            <input
              type="text" value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari produk..."
              className="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl
                         text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-400"
            />
          </div>
        </div>

        {/* Category filter */}
        <div className="flex gap-2 overflow-x-auto pb-0.5 flex-shrink-0">
          {categories.map((cat) => (
            <button
              key={cat}
              onClick={() => setCategory(cat)}
              className={`px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all
                          ${category === cat
                            ? 'bg-green-600 text-white shadow-sm'
                            : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'}`}
            >
              {cat}
            </button>
          ))}
        </div>

        {/* Product grid */}
        <div className="flex-1 overflow-y-auto">
          {filtered.length === 0
            ? <Empty icon="🍽️" label="Belum ada produk. Tambah di menu Menu & Resep." />
            : (
              <div className="grid grid-cols-2 xl:grid-cols-3 gap-3 pb-2">
                {filtered.map((p) => (
                  <button
                    key={p.id}
                    onClick={() => addToCart(p)}
                    className="product-card bg-white rounded-2xl p-3 text-left
                               border border-gray-100 shadow-sm"
                  >
                    <div className="w-full h-14 bg-gradient-to-br from-green-50 to-emerald-100
                                    rounded-xl mb-2.5 flex items-center justify-center text-3xl">
                      {p.emoji || EMOJI_FALLBACK}
                    </div>
                    <p className="text-[10px] text-gray-400 mb-0.5 font-medium">{p.category || 'Umum'}</p>
                    <p className="font-bold text-gray-800 text-xs leading-tight mb-1 line-clamp-2">{p.name}</p>
                    <p className="text-green-700 font-black text-sm">{formatRp(p.price)}</p>
                    {p.hpp > 0 && (
                      <p className="text-[10px] text-gray-400">HPP {formatRp(p.hpp)}</p>
                    )}
                  </button>
                ))}
              </div>
            )
          }
        </div>
      </div>

      {/* ── Keranjang ── */}
      <div className="w-72 bg-white border-l border-gray-100 flex flex-col flex-shrink-0">
        <div className="p-4 border-b border-gray-100 flex items-center justify-between">
          <span className="font-bold text-gray-800 text-sm flex items-center gap-2">
            🛒 Keranjang
          </span>
          {cartCount > 0 && (
            <span className="bg-green-600 text-white text-xs font-bold
                             px-2 py-0.5 rounded-full">{cartCount}</span>
          )}
        </div>

        <div className="flex-1 overflow-y-auto p-3 space-y-2">
          {cart.length === 0
            ? <p className="text-center text-gray-400 text-xs mt-10">Tap produk untuk menambah</p>
            : cart.map((item) => (
              <div key={item.id} className="bg-gray-50 rounded-xl p-3">
                <div className="flex items-start justify-between mb-2">
                  <p className="text-xs font-semibold text-gray-800 flex-1 leading-tight pr-1 line-clamp-2">
                    {item.name}
                  </p>
                  <button
                    onClick={() => setCart((prev) => prev.filter((c) => c.id !== item.id))}
                    className="text-gray-300 hover:text-red-400 flex-shrink-0 mt-0.5"
                  >
                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor"
                         strokeWidth="2.5" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <div className="flex items-center justify-between">
                  <p className="text-green-700 font-bold text-xs">{formatRp(item.price * item.qty)}</p>
                  <div className="flex items-center gap-1.5">
                    <button
                      onClick={() => setQty(item.id, item.qty - 1)}
                      className="w-6 h-6 bg-gray-200 rounded-lg text-gray-600 font-bold
                                 hover:bg-gray-300 flex items-center justify-center text-sm"
                    >−</button>
                    <input
                      type="number" value={item.qty} min="1"
                      onChange={(e) => setQty(item.id, parseInt(e.target.value) || 1)}
                      className="w-8 text-center text-sm font-bold bg-transparent
                                 focus:outline-none border-b border-gray-200"
                    />
                    <button
                      onClick={() => setQty(item.id, item.qty + 1)}
                      className="w-6 h-6 bg-green-600 rounded-lg text-white font-bold
                                 hover:bg-green-700 flex items-center justify-center text-sm"
                    >+</button>
                  </div>
                </div>
              </div>
            ))
          }
        </div>

        {/* ── Ringkasan & Bayar ── */}
        <div className="p-4 border-t border-gray-100 space-y-3">
          <div className="space-y-1.5 text-xs">
            <div className="flex justify-between text-gray-500">
              <span>Total HPP</span><span>{formatRp(cartHPP)}</span>
            </div>
            <div className="flex justify-between text-gray-500">
              <span>Laba Kotor</span>
              <span className="font-semibold text-green-600">{formatRp(grossProfit)}</span>
            </div>
            <div className="flex justify-between font-bold text-gray-800 text-sm border-t pt-1.5">
              <span>TOTAL</span>
              <span className="text-green-700">{formatRp(cartTotal)}</span>
            </div>
          </div>

          <select
            value={payMethod}
            onChange={(e) => setPayMethod(e.target.value)}
            className="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs
                       focus:outline-none focus:ring-2 focus:ring-green-400"
          >
            {['Tunai','QRIS','Transfer','Kartu Debit','Kartu Kredit'].map((m) => (
              <option key={m}>{m}</option>
            ))}
          </select>

          {payMethod === 'Tunai' && (
            <div>
              <input
                type="number" value={cashIn}
                onChange={(e) => setCashIn(e.target.value)}
                placeholder="Uang diterima (Rp)"
                className="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs
                           focus:outline-none focus:ring-2 focus:ring-green-400"
              />
              {cashIn && cashInNum >= cartTotal && (
                <p className="text-xs text-green-600 font-semibold mt-1 text-right">
                  Kembalian: {formatRp(change)}
                </p>
              )}
            </div>
          )}

          {cart.length > 0 && (
            <button
              onClick={() => setCart([])}
              className="w-full py-1.5 text-xs text-red-400 hover:text-red-600 transition-colors"
            >
              Kosongkan Keranjang
            </button>
          )}

          <button
            onClick={checkout}
            disabled={!cart.length}
            className="w-full py-3 bg-gradient-to-r from-green-600 to-emerald-500
                       text-white font-black rounded-xl text-sm shadow-lg
                       hover:opacity-90 transition-opacity disabled:opacity-40"
          >
            {cart.length ? `Bayar ${formatRp(cartTotal)}` : 'Keranjang Kosong'}
          </button>
        </div>
      </div>

      {/* ── Struk Modal ── */}
      <Modal open={showReceipt} onClose={() => setShowReceipt(false)} title="Struk Digital">
        {receipt && (
          <div className="receipt-wrapper">
            <div className="receipt-header">
              <h2>{receipt.businessName}</h2>
              <p>{new Date(receipt.date).toLocaleString('id-ID')}</p>
              <p>No: {receipt.id.toUpperCase().slice(-10)}</p>
            </div>
            <div className="receipt-items">
              {receipt.items.map((it) => (
                <div key={it.id} className="receipt-row">
                  <span>{it.productName} x{it.qty}</span>
                  <span>{formatRp(it.price * it.qty)}</span>
                </div>
              ))}
            </div>
            <hr className="receipt-divider" />
            <div className="receipt-row bold receipt-total">
              <span>TOTAL</span><span>{formatRp(receipt.total)}</span>
            </div>
            <div className="receipt-row">
              <span>Bayar ({receipt.paymentMethod})</span>
              <span>{formatRp(receipt.cashIn)}</span>
            </div>
            {receipt.paymentMethod === 'Tunai' && (
              <div className="receipt-row bold">
                <span>Kembalian</span><span>{formatRp(receipt.change)}</span>
              </div>
            )}
            <div className="receipt-footer">
              <p>Terima kasih atas kunjungan Anda!</p>
              <p>Powered by KasirKu AI POS</p>
            </div>
          </div>
        )}
        <button
          onClick={() => setShowReceipt(false)}
          className="mt-4 w-full py-2.5 bg-green-600 text-white rounded-xl
                     text-sm font-bold hover:bg-green-700"
        >
          Tutup Struk
        </button>
      </Modal>
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   INVENTORY MODULE – Bahan Baku + Waste
───────────────────────────────────────────────────────────── */
function InventoryModule({ business, showToast }) {
  const [ingredients, setIngredients] = useState([]);
  const [showForm,    setShowForm]    = useState(false);
  const [showWaste,   setShowWaste]   = useState(false);
  const [editItem,    setEditItem]    = useState(null);
  const [wasteItem,   setWasteItem]   = useState(null);
  const [wasteQty,    setWasteQty]    = useState('');
  const [wasteReason, setWasteReason] = useState('');
  const [search,      setSearch]      = useState('');

  const emptyForm = { name:'', unit:'kg', pricePerUnit:'', stock:'', minStock:'' };
  const [form, setForm] = useState(emptyForm);

  useEffect(() => { load(); }, [business.id]);

  async function load() {
    const list = await window.db.ingredients
      .where('businessId').equals(business.id).toArray();
    setIngredients(list);
  }

  const filtered = ingredients.filter((i) =>
    i.name.toLowerCase().includes(search.toLowerCase())
  );

  async function saveIngredient() {
    if (!form.name.trim()) { showToast('Nama bahan wajib diisi', 'error'); return; }
    const data = {
      businessId: business.id,
      name:         form.name.trim(),
      unit:         form.unit,
      pricePerUnit: parseFloat(form.pricePerUnit) || 0,
      stock:        parseFloat(form.stock) || 0,
      minStock:     parseFloat(form.minStock) || 0,
    };
    if (editItem) {
      await window.db.ingredients.update(editItem.id, data);
      showToast('Bahan berhasil diperbarui');
    } else {
      await window.db.ingredients.add({ id: uid(), ...data });
      showToast('Bahan berhasil ditambahkan');
    }
    setForm(emptyForm); setShowForm(false); setEditItem(null); load();
  }

  async function deleteIngredient(id) {
    if (!confirm('Hapus bahan ini? Resep yang menggunakannya juga akan terpengaruh.')) return;
    await window.db.ingredients.delete(id);
    showToast('Bahan dihapus', 'info'); load();
  }

  async function recordWaste() {
    const qty = parseFloat(wasteQty);
    if (!wasteItem || isNaN(qty) || qty <= 0) {
      showToast('Jumlah waste tidak valid', 'error'); return;
    }
    await window.db.waste_entries.add({
      id: uid(), businessId: business.id, date: Date.now(),
      ingredientId: wasteItem.id, ingredientName: wasteItem.name,
      quantity: qty, unit: wasteItem.unit,
      valueLost: qty * wasteItem.pricePerUnit,
      reason: wasteReason,
    });
    await window.db.ingredients.update(wasteItem.id, {
      stock: Math.max(0, wasteItem.stock - qty),
    });
    showToast(`Waste ${qty} ${wasteItem.unit} ${wasteItem.name} dicatat`, 'warning');
    setShowWaste(false); setWasteItem(null); setWasteQty(''); setWasteReason(''); load();
  }

  function openEdit(item) {
    setEditItem(item);
    setForm({
      name: item.name, unit: item.unit,
      pricePerUnit: item.pricePerUnit,
      stock: item.stock, minStock: item.minStock || 0,
    });
    setShowForm(true);
  }

  const totalValue = ingredients.reduce((s, i) => s + i.stock * i.pricePerUnit, 0);
  const lowStock   = ingredients.filter((i) => i.minStock > 0 && i.stock <= i.minStock);
  const UNITS = ['kg','gr','ltr','ml','pcs','dus','btl','sachet','ikat','porsi'];

  return (
    <div className="p-5 page-enter">
      {/* Header */}
      <div className="flex items-center justify-between mb-5">
        <div>
          <h1 className="text-xl font-black text-gray-800">Gudang & Bahan Baku</h1>
          <p className="text-sm text-gray-500 mt-0.5">
            Nilai stok total:&nbsp;
            <span className="font-bold text-green-700">{formatRp(totalValue)}</span>
            {lowStock.length > 0 && (
              <span className="ml-2 text-red-600 font-semibold">
                · {lowStock.length} bahan menipis!
              </span>
            )}
          </p>
        </div>
        <button
          onClick={() => { setEditItem(null); setForm(emptyForm); setShowForm(true); }}
          className="px-4 py-2.5 bg-green-600 text-white rounded-xl text-sm font-bold
                     hover:bg-green-700 shadow flex items-center gap-1.5"
        >
          + Tambah Bahan
        </button>
      </div>

      {/* Search */}
      <div className="relative mb-4">
        <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
             fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8" /><path d="m21 21-4.35-4.35" />
        </svg>
        <input
          type="text" value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="Cari bahan..."
          className="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm
                     bg-white focus:outline-none focus:ring-2 focus:ring-green-400 max-w-sm"
        />
      </div>

      {/* Table */}
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-gray-50">
            <tr>
              {['Nama Bahan','Satuan','Harga/Unit','Stok','Nilai Stok','Min. Stok','Status','Aksi'].map((h) => (
                <th key={h}
                    className="px-4 py-3 text-left text-xs font-semibold text-gray-500 whitespace-nowrap">
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {filtered.length === 0
              ? (
                <tr>
                  <td colSpan="8">
                    <Empty icon="📦" label="Belum ada bahan baku." />
                  </td>
                </tr>
              )
              : filtered.map((item) => {
                const low  = item.minStock > 0 && item.stock <= item.minStock;
                const zero = item.stock <= 0;
                return (
                  <tr key={item.id} className="hover:bg-gray-50/50">
                    <td className="px-4 py-3 font-semibold text-gray-800">{item.name}</td>
                    <td className="px-4 py-3 text-gray-500">{item.unit}</td>
                    <td className="px-4 py-3 text-gray-600">{formatRp(item.pricePerUnit)}</td>
                    <td className={`px-4 py-3 font-bold ${zero ? 'text-red-600' : low ? 'text-orange-500' : 'text-gray-800'}`}>
                      {item.stock.toFixed(2)}
                    </td>
                    <td className="px-4 py-3 text-green-700 font-semibold">
                      {formatRp(item.stock * item.pricePerUnit)}
                    </td>
                    <td className="px-4 py-3 text-gray-400 text-xs">{item.minStock || '-'}</td>
                    <td className="px-4 py-3">
                      {zero
                        ? <span className="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">HABIS</span>
                        : low
                        ? <span className="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">Menipis</span>
                        : <span className="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">OK</span>
                      }
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex gap-1">
                        <button
                          onClick={() => openEdit(item)}
                          className="px-2 py-1 bg-blue-50 text-blue-600 rounded-lg
                                     text-xs font-semibold hover:bg-blue-100"
                        >Edit</button>
                        <button
                          onClick={() => { setWasteItem(item); setShowWaste(true); }}
                          className="px-2 py-1 bg-orange-50 text-orange-600 rounded-lg
                                     text-xs font-semibold hover:bg-orange-100"
                        >Waste</button>
                        <button
                          onClick={() => deleteIngredient(item.id)}
                          className="px-2 py-1 bg-red-50 text-red-500 rounded-lg
                                     text-xs font-semibold hover:bg-red-100"
                        >Hapus</button>
                      </div>
                    </td>
                  </tr>
                );
              })
            }
          </tbody>
        </table>
      </div>

      {/* ── Add / Edit Modal ── */}
      <Modal
        open={showForm}
        onClose={() => { setShowForm(false); setEditItem(null); setForm(emptyForm); }}
        title={editItem ? 'Edit Bahan Baku' : 'Tambah Bahan Baku'}
      >
        <div className="space-y-4">
          {[
            ['name',        'Nama Bahan *',           'text'],
            ['pricePerUnit','Harga per Unit (Rp)',     'number'],
            ['stock',       'Stok Saat Ini',           'number'],
            ['minStock',    'Stok Minimum (alert)',    'number'],
          ].map(([k, label, type]) => (
            <div key={k}>
              <label htmlFor={'inv-' + k}
                     className="block text-xs font-semibold text-gray-600 mb-1.5">
                {label}
              </label>
              <input
                id={'inv-' + k} type={type}
                value={form[k]}
                onChange={(e) => setForm((f) => ({ ...f, [k]: e.target.value }))}
                className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-green-400"
              />
            </div>
          ))}
          <div>
            <label htmlFor="inv-unit"
                   className="block text-xs font-semibold text-gray-600 mb-1.5">
              Satuan
            </label>
            <select
              id="inv-unit" value={form.unit}
              onChange={(e) => setForm((f) => ({ ...f, unit: e.target.value }))}
              className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                         focus:outline-none focus:ring-2 focus:ring-green-400"
            >
              {UNITS.map((u) => <option key={u}>{u}</option>)}
            </select>
          </div>
          <div className="flex gap-2 pt-1">
            <button
              onClick={() => { setShowForm(false); setEditItem(null); setForm(emptyForm); }}
              className="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600"
            >Batal</button>
            <button
              onClick={saveIngredient}
              className="flex-1 py-2.5 bg-green-600 text-white rounded-xl text-sm font-bold"
            >Simpan</button>
          </div>
        </div>
      </Modal>

      {/* ── Waste Modal ── */}
      <Modal
        open={showWaste}
        onClose={() => { setShowWaste(false); setWasteItem(null); setWasteQty(''); }}
        title="Catat Waste / Bahan Basi"
      >
        {wasteItem && (
          <div className="space-y-4">
            <div className="bg-orange-50 border border-orange-200 rounded-xl p-4">
              <p className="font-bold text-orange-800">{wasteItem.name}</p>
              <p className="text-sm text-orange-600 mt-0.5">
                Stok saat ini: <strong>{wasteItem.stock}</strong> {wasteItem.unit}
              </p>
            </div>
            <div>
              <label htmlFor="waste-qty"
                     className="block text-xs font-semibold text-gray-600 mb-1.5">
                Jumlah Waste ({wasteItem.unit}) *
              </label>
              <input
                id="waste-qty" type="number"
                value={wasteQty}
                onChange={(e) => setWasteQty(e.target.value)}
                max={wasteItem.stock}
                className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-orange-400"
              />
              {wasteQty && (
                <p className="text-xs text-orange-600 mt-1.5 bg-orange-50 px-3 py-2 rounded-lg">
                  Nilai terbuang: <strong>{formatRp(parseFloat(wasteQty || 0) * wasteItem.pricePerUnit)}</strong>
                </p>
              )}
            </div>
            <div>
              <label htmlFor="waste-reason"
                     className="block text-xs font-semibold text-gray-600 mb-1.5">
                Keterangan (opsional)
              </label>
              <input
                id="waste-reason" type="text"
                value={wasteReason}
                onChange={(e) => setWasteReason(e.target.value)}
                placeholder="Mis: Kedaluwarsa, tumpah, basi"
                className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-orange-400"
              />
            </div>
            <div className="flex gap-2">
              <button
                onClick={() => { setShowWaste(false); setWasteItem(null); setWasteQty(''); }}
                className="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm"
              >Batal</button>
              <button
                onClick={recordWaste}
                className="flex-1 py-2.5 bg-orange-500 text-white rounded-xl
                           text-sm font-bold hover:bg-orange-600"
              >Catat Waste</button>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   MENU MODULE – CRUD Produk + Resep
───────────────────────────────────────────────────────────── */
function MenuModule({ business, showToast }) {
  const [products,    setProducts]    = useState([]);
  const [ingredients, setIngredients] = useState([]);
  const [recipes,     setRecipes]     = useState([]);
  const [showForm,    setShowForm]    = useState(false);
  const [editItem,    setEditItem]    = useState(null);
  const [recipeRows,  setRecipeRows]  = useState([]);

  const emptyForm = { name:'', category:'', price:'', emoji:'🍽️', description:'' };
  const [form, setForm] = useState(emptyForm);

  useEffect(() => { load(); }, [business.id]);

  async function load() {
    const [p, i, r] = await Promise.all([
      window.db.products.where('businessId').equals(business.id).toArray(),
      window.db.ingredients.where('businessId').equals(business.id).toArray(),
      window.db.recipes.where('businessId').equals(business.id).toArray(),
    ]);
    setProducts(p); setIngredients(i); setRecipes(r);
  }

  function calcHPP(productId) {
    return recipes
      .filter((r) => r.productId === productId)
      .reduce((s, r) => {
        const ing = ingredients.find((i) => i.id === r.ingredientId);
        return s + (ing ? ing.pricePerUnit * r.quantity : 0);
      }, 0);
  }

  async function saveProduct() {
    if (!form.name.trim() || !form.price) {
      showToast('Nama dan harga wajib diisi', 'error'); return;
    }
    const hpp = recipeRows.reduce((s, row) => {
      const ing = ingredients.find((i) => i.id === row.ingredientId);
      return s + (ing && row.quantity ? ing.pricePerUnit * parseFloat(row.quantity) : 0);
    }, 0);

    const data = {
      businessId:  business.id,
      name:        form.name.trim(),
      category:    form.category.trim(),
      price:       parseFloat(form.price) || 0,
      emoji:       form.emoji || '🍽️',
      description: form.description.trim(),
      hpp,
      active:      true,
    };

    let productId;
    if (editItem) {
      await window.db.products.update(editItem.id, data);
      productId = editItem.id;
      await window.db.recipes.where('productId').equals(productId).delete();
      showToast('Menu berhasil diperbarui');
    } else {
      productId = uid();
      await window.db.products.add({ id: productId, ...data });
      showToast('Menu berhasil ditambahkan');
    }

    for (const row of recipeRows) {
      if (row.ingredientId && parseFloat(row.quantity) > 0) {
        await window.db.recipes.add({
          id: uid(), productId, businessId: business.id,
          ingredientId: row.ingredientId, quantity: parseFloat(row.quantity),
        });
      }
    }
    setShowForm(false); setEditItem(null); setForm(emptyForm); setRecipeRows([]); load();
  }

  async function deleteProduct(id) {
    if (!confirm('Hapus menu ini?')) return;
    await window.db.products.delete(id);
    await window.db.recipes.where('productId').equals(id).delete();
    showToast('Menu dihapus', 'info'); load();
  }

  async function toggleActive(item) {
    await window.db.products.update(item.id, { active: !(item.active !== false) });
    load();
  }

  function openEdit(item) {
    setEditItem(item);
    setForm({
      name: item.name, category: item.category || '',
      price: item.price, emoji: item.emoji || '🍽️',
      description: item.description || '',
    });
    setRecipeRows(
      recipes.filter((r) => r.productId === item.id)
             .map((r) => ({ ingredientId: r.ingredientId, quantity: r.quantity }))
    );
    setShowForm(true);
  }

  const EMOJIS = ['🍽️','🍜','🍛','🍕','🍔','☕','🧋','🥤','🍰','🎂','🥗','🍱','🍤','🍙','🌮','🥩','🍗','🥪','🍣','🍦'];

  return (
    <div className="p-5 page-enter">
      <div className="flex items-center justify-between mb-5">
        <h1 className="text-xl font-black text-gray-800">Menu & Resep</h1>
        <button
          onClick={() => { setEditItem(null); setForm(emptyForm); setRecipeRows([]); setShowForm(true); }}
          className="px-4 py-2.5 bg-green-600 text-white rounded-xl text-sm font-bold
                     hover:bg-green-700 shadow"
        >
          + Tambah Menu
        </button>
      </div>

      {products.length === 0
        ? <Empty icon="🍽️" label="Belum ada menu. Klik Tambah Menu untuk mulai." />
        : (
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            {products.map((p) => {
              const hpp    = calcHPP(p.id);
              const margin = p.price > 0
                ? Math.round(((p.price - hpp) / p.price) * 100) : 0;
              const active = p.active !== false;
              return (
                <div
                  key={p.id}
                  className={`bg-white rounded-2xl border p-4 shadow-sm transition-opacity
                              ${active ? 'border-gray-100' : 'border-gray-200 opacity-60'}`}
                >
                  <div className="flex items-start justify-between mb-3">
                    <div className="flex items-center gap-3">
                      <span className="text-3xl">{p.emoji || '🍽️'}</span>
                      <div>
                        <p className="font-bold text-gray-800 text-sm">{p.name}</p>
                        <p className="text-xs text-gray-400">{p.category || 'Umum'}</p>
                      </div>
                    </div>
                    {/* Toggle aktif */}
                    <label className="relative inline-flex items-center cursor-pointer">
                      <input
                        type="checkbox" className="sr-only peer"
                        checked={active} onChange={() => toggleActive(p)}
                      />
                      <div className="w-9 h-5 bg-gray-200 rounded-full peer
                                      peer-checked:after:translate-x-full after:content-['']
                                      after:absolute after:top-0.5 after:left-0.5
                                      after:bg-white after:rounded-full after:h-4 after:w-4
                                      after:transition-all peer-checked:bg-green-600" />
                    </label>
                  </div>

                  <div className="grid grid-cols-3 gap-2 mb-3 text-xs">
                    <div className="bg-green-50 rounded-xl px-2.5 py-2">
                      <p className="text-gray-400">Harga</p>
                      <p className="font-bold text-green-700">{formatRp(p.price)}</p>
                    </div>
                    <div className="bg-orange-50 rounded-xl px-2.5 py-2">
                      <p className="text-gray-400">HPP</p>
                      <p className="font-bold text-orange-600">{formatRp(hpp)}</p>
                    </div>
                    <div className={`rounded-xl px-2.5 py-2 ${margin >= 40 ? 'bg-blue-50' : margin >= 20 ? 'bg-yellow-50' : 'bg-red-50'}`}>
                      <p className="text-gray-400">Margin</p>
                      <p className={`font-bold ${margin >= 40 ? 'text-blue-700' : margin >= 20 ? 'text-yellow-700' : 'text-red-600'}`}>
                        {margin}%
                      </p>
                    </div>
                  </div>

                  <div className="flex gap-2">
                    <button
                      onClick={() => openEdit(p)}
                      className="flex-1 py-1.5 bg-blue-50 text-blue-600 rounded-lg
                                 text-xs font-semibold hover:bg-blue-100"
                    >Edit & Resep</button>
                    <button
                      onClick={() => deleteProduct(p.id)}
                      className="px-3 py-1.5 bg-red-50 text-red-500 rounded-lg
                                 text-xs font-semibold hover:bg-red-100"
                    >Hapus</button>
                  </div>
                </div>
              );
            })}
          </div>
        )
      }

      {/* ── Form Modal ── */}
      <Modal
        open={showForm}
        onClose={() => { setShowForm(false); setEditItem(null); }}
        title={editItem ? 'Edit Menu' : 'Tambah Menu Baru'}
        wide
      >
        <div className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label htmlFor="m-name"
                     className="block text-xs font-semibold text-gray-600 mb-1.5">
                Nama Menu *
              </label>
              <input
                id="m-name" type="text"
                value={form.name}
                onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
                className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-green-400"
              />
            </div>
            <div>
              <label htmlFor="m-cat"
                     className="block text-xs font-semibold text-gray-600 mb-1.5">
                Kategori
              </label>
              <input
                id="m-cat" type="text"
                value={form.category}
                onChange={(e) => setForm((f) => ({ ...f, category: e.target.value }))}
                placeholder="Makanan, Minuman, dll"
                className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-green-400"
              />
            </div>
          </div>

          <div>
            <label htmlFor="m-price"
                   className="block text-xs font-semibold text-gray-600 mb-1.5">
              Harga Jual (Rp) *
            </label>
            <input
              id="m-price" type="number"
              value={form.price}
              onChange={(e) => setForm((f) => ({ ...f, price: e.target.value }))}
              className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                         focus:outline-none focus:ring-2 focus:ring-green-400"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold text-gray-600 mb-1.5">
              Emoji Icon
            </label>
            <div className="flex flex-wrap gap-1.5">
              {EMOJIS.map((e) => (
                <button
                  key={e} type="button"
                  onClick={() => setForm((f) => ({ ...f, emoji: e }))}
                  className={`text-xl p-1.5 rounded-lg transition-all
                              ${form.emoji === e
                                ? 'bg-green-100 ring-2 ring-green-400'
                                : 'hover:bg-gray-100'}`}
                >{e}</button>
              ))}
            </div>
          </div>

          {/* Resep */}
          <div>
            <label className="block text-xs font-semibold text-gray-600 mb-2">
              Komposisi Resep
              <span className="font-normal text-gray-400 ml-1">
                (untuk kalkulasi HPP & potongan stok otomatis)
              </span>
            </label>
            {recipeRows.length > 0 && (
              <div className="space-y-2 mb-2">
                {recipeRows.map((row, i) => (
                  <div key={i} className="flex gap-2 items-center">
                    <select
                      value={row.ingredientId}
                      onChange={(e) => {
                        const nr = [...recipeRows];
                        nr[i] = { ...nr[i], ingredientId: e.target.value };
                        setRecipeRows(nr);
                      }}
                      className="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-green-400"
                    >
                      <option value="">Pilih Bahan</option>
                      {ingredients.map((ing) => (
                        <option key={ing.id} value={ing.id}>
                          {ing.name} ({ing.unit})
                        </option>
                      ))}
                    </select>
                    <input
                      type="number" placeholder="Qty" value={row.quantity}
                      onChange={(e) => {
                        const nr = [...recipeRows];
                        nr[i] = { ...nr[i], quantity: e.target.value };
                        setRecipeRows(nr);
                      }}
                      className="w-24 border border-gray-200 rounded-xl px-3 py-2 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-green-400"
                    />
                    <button
                      onClick={() => setRecipeRows((r) => r.filter((_, j) => j !== i))}
                      className="text-red-400 hover:text-red-600 p-1"
                    >
                      <svg className="w-4 h-4" fill="none" stroke="currentColor"
                           strokeWidth="2.5" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                ))}
              </div>
            )}
            {ingredients.length > 0
              ? (
                <button
                  type="button"
                  onClick={() => setRecipeRows((r) => [...r, { ingredientId: '', quantity: '' }])}
                  className="text-xs text-green-600 font-semibold hover:text-green-700"
                >
                  + Tambah Bahan Resep
                </button>
              )
              : (
                <p className="text-xs text-gray-400">
                  Tambahkan bahan baku di modul Gudang untuk membuat resep.
                </p>
              )
            }
          </div>

          <div className="flex gap-2 pt-2">
            <button
              onClick={() => { setShowForm(false); setEditItem(null); }}
              className="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600"
            >Batal</button>
            <button
              onClick={saveProduct}
              className="flex-1 py-2.5 bg-green-600 text-white rounded-xl text-sm font-bold"
            >Simpan Menu</button>
          </div>
        </div>
      </Modal>
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   OPEX MODULE – Biaya Operasional
───────────────────────────────────────────────────────────── */
function OPEXModule({ business, showToast }) {
  const [entries,  setEntries]  = useState([]);
  const [showForm, setShowForm] = useState(false);
  const emptyForm = {
    category: 'Listrik', description: '', amount: '',
    date: new Date().toISOString().split('T')[0],
  };
  const [form, setForm] = useState(emptyForm);

  useEffect(() => { load(); }, [business.id]);

  async function load() {
    const list = await window.db.opex_entries
      .where('businessId').equals(business.id).reverse().sortBy('date');
    setEntries(list);
  }

  async function save() {
    const amt = parseFloat(form.amount);
    if (!amt || amt <= 0) { showToast('Nominal tidak valid', 'error'); return; }
    await window.db.opex_entries.add({
      id: uid(), businessId: business.id,
      category: form.category, description: form.description.trim(),
      amount: amt, date: new Date(form.date).getTime(),
    });
    showToast('Pengeluaran dicatat');
    setForm(emptyForm); setShowForm(false); load();
  }

  async function del(id) {
    await window.db.opex_entries.delete(id);
    showToast('Dihapus', 'info'); load();
  }

  const totalOPEX  = entries.reduce((s, e) => s + e.amount, 0);
  const byCategory = entries.reduce((acc, e) => {
    acc[e.category] = (acc[e.category] || 0) + e.amount;
    return acc;
  }, {});

  const CAT_COLORS = {
    Listrik:'bg-yellow-50 text-yellow-700', Air:'bg-blue-50 text-blue-700',
    Gas:'bg-orange-50 text-orange-700', Gaji:'bg-purple-50 text-purple-700',
    Sewa:'bg-indigo-50 text-indigo-700', Internet:'bg-sky-50 text-sky-700',
    Transport:'bg-teal-50 text-teal-700', Promosi:'bg-pink-50 text-pink-700',
    Maintenance:'bg-stone-50 text-stone-700', Lainnya:'bg-gray-50 text-gray-600',
  };

  const CATEGORIES = Object.keys(CAT_COLORS);

  return (
    <div className="p-5 page-enter">
      <div className="flex items-center justify-between mb-5">
        <div>
          <h1 className="text-xl font-black text-gray-800">Biaya Operasional (OPEX)</h1>
          <p className="text-sm text-gray-500 mt-0.5">
            Total OPEX:&nbsp;
            <span className="font-bold text-red-600">{formatRp(totalOPEX)}</span>
          </p>
        </div>
        <button
          onClick={() => setShowForm(true)}
          className="px-4 py-2.5 bg-green-600 text-white rounded-xl text-sm font-bold
                     hover:bg-green-700 shadow"
        >
          + Catat OPEX
        </button>
      </div>

      {/* Summary by category */}
      {Object.keys(byCategory).length > 0 && (
        <div className="flex flex-wrap gap-2 mb-5">
          {Object.entries(byCategory).sort((a, b) => b[1] - a[1]).map(([cat, val]) => (
            <div key={cat}
                 className={`px-3 py-2 rounded-xl text-xs font-semibold ${CAT_COLORS[cat] || CAT_COLORS.Lainnya}`}>
              {cat}: {formatRp(val)}
            </div>
          ))}
        </div>
      )}

      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-gray-50">
            <tr>
              {['Tanggal','Kategori','Keterangan','Jumlah','Hapus'].map((h) => (
                <th key={h} className="px-4 py-3 text-left text-xs font-semibold text-gray-500">
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {entries.length === 0
              ? (
                <tr>
                  <td colSpan="5">
                    <Empty icon="💸" label="Belum ada catatan OPEX." />
                  </td>
                </tr>
              )
              : entries.map((e) => (
                <tr key={e.id} className="hover:bg-gray-50/50">
                  <td className="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                    {new Date(e.date).toLocaleDateString('id-ID')}
                  </td>
                  <td className="px-4 py-3">
                    <span className={`px-2 py-0.5 rounded-full text-xs font-semibold
                                    ${CAT_COLORS[e.category] || CAT_COLORS.Lainnya}`}>
                      {e.category}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-gray-700 max-w-xs truncate">
                    {e.description || '—'}
                  </td>
                  <td className="px-4 py-3 font-bold text-red-600">{formatRp(e.amount)}</td>
                  <td className="px-4 py-3">
                    <button
                      onClick={() => del(e.id)}
                      className="px-2 py-1 bg-red-50 text-red-500 rounded-lg
                                 text-xs hover:bg-red-100"
                    >Hapus</button>
                  </td>
                </tr>
              ))
            }
          </tbody>
        </table>
      </div>

      <Modal open={showForm} onClose={() => { setShowForm(false); setForm(emptyForm); }}
             title="Catat Biaya Operasional">
        <div className="space-y-4">
          <div>
            <label htmlFor="opex-cat"
                   className="block text-xs font-semibold text-gray-600 mb-1.5">Kategori</label>
            <select
              id="opex-cat" value={form.category}
              onChange={(e) => setForm((f) => ({ ...f, category: e.target.value }))}
              className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                         focus:outline-none focus:ring-2 focus:ring-green-400"
            >
              {CATEGORIES.map((c) => <option key={c}>{c}</option>)}
            </select>
          </div>
          <div>
            <label htmlFor="opex-desc"
                   className="block text-xs font-semibold text-gray-600 mb-1.5">
              Keterangan (opsional)
            </label>
            <input
              id="opex-desc" type="text"
              value={form.description}
              onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))}
              placeholder="Mis: Tagihan PLN Juli 2025"
              className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                         focus:outline-none focus:ring-2 focus:ring-green-400"
            />
          </div>
          <div>
            <label htmlFor="opex-amt"
                   className="block text-xs font-semibold text-gray-600 mb-1.5">
              Jumlah (Rp) *
            </label>
            <input
              id="opex-amt" type="number"
              value={form.amount}
              onChange={(e) => setForm((f) => ({ ...f, amount: e.target.value }))}
              className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                         focus:outline-none focus:ring-2 focus:ring-green-400"
            />
          </div>
          <div>
            <label htmlFor="opex-date"
                   className="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal</label>
            <input
              id="opex-date" type="date"
              value={form.date}
              onChange={(e) => setForm((f) => ({ ...f, date: e.target.value }))}
              className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                         focus:outline-none focus:ring-2 focus:ring-green-400"
            />
          </div>
          <div className="flex gap-2">
            <button
              onClick={() => { setShowForm(false); setForm(emptyForm); }}
              className="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600"
            >Batal</button>
            <button
              onClick={save}
              className="flex-1 py-2.5 bg-green-600 text-white rounded-xl text-sm font-bold"
            >Simpan</button>
          </div>
        </div>
      </Modal>
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   REPORTS MODULE – Live Monitoring + Rekap Keuangan
───────────────────────────────────────────────────────────── */
function ReportsModule({ business }) {
  const [transactions, setTransactions] = useState([]);
  const [opex,         setOpex]         = useState([]);
  const [filter,       setFilter]       = useState('today');
  const [newTxIds,     setNewTxIds]     = useState(new Set());
  const prevCount = useRef(0);

  useEffect(() => {
    load();
    const iv = setInterval(load, 5000);
    return () => clearInterval(iv);
  }, [business.id]);

  async function load() {
    const [txList, opexList] = await Promise.all([
      window.db.transactions.where('businessId').equals(business.id).reverse().sortBy('date'),
      window.db.opex_entries.where('businessId').equals(business.id).toArray(),
    ]);
    if (txList.length > prevCount.current && prevCount.current > 0) {
      const added = new Set(txList.slice(0, txList.length - prevCount.current).map((t) => t.id));
      setNewTxIds(added);
      setTimeout(() => setNewTxIds(new Set()), 3500);
    }
    prevCount.current = txList.length;
    setTransactions(txList);
    setOpex(opexList);
  }

  const now        = Date.now();
  const todayStart = (() => { const d = new Date(); d.setHours(0,0,0,0); return d.getTime(); })();
  const weekStart  = now - 7 * 24 * 60 * 60 * 1000;
  const monthStart = (() => { const d = new Date(); d.setDate(1); d.setHours(0,0,0,0); return d.getTime(); })();

  const inRange = (ts) => {
    if (filter === 'today') return ts >= todayStart;
    if (filter === 'week')  return ts >= weekStart;
    if (filter === 'month') return ts >= monthStart;
    return true;
  };

  const filteredTx   = transactions.filter((t) => inRange(t.date));
  const filteredOpex = opex.filter((e) => inRange(e.date));

  const omset      = filteredTx.reduce((s, t) => s + t.total, 0);
  const totalHPP   = filteredTx.reduce((s, t) => s + t.totalHPP, 0);
  const gross      = omset - totalHPP;
  const totalOPEX  = filteredOpex.reduce((s, e) => s + e.amount, 0);
  const net        = gross - totalOPEX;
  const margin     = omset > 0 ? ((net / omset) * 100).toFixed(1) : '0.0';
  const txCount    = filteredTx.length;

  const SUMMARY = [
    { label: 'Omset',       val: formatRp(omset),  cls: 'text-green-700',  bg: 'bg-green-50'  },
    { label: 'Total HPP',   val: formatRp(totalHPP), cls: 'text-orange-600', bg: 'bg-orange-50' },
    { label: 'Laba Kotor',  val: formatRp(gross),  cls: 'text-blue-700',   bg: 'bg-blue-50'   },
    { label: 'Total OPEX',  val: formatRp(totalOPEX), cls: 'text-red-600',  bg: 'bg-red-50'    },
    { label: 'Laba Bersih', val: formatRp(net),    cls: net >= 0 ? 'text-green-700' : 'text-red-700', bg: net >= 0 ? 'bg-green-50' : 'bg-red-50' },
    { label: 'Margin Bersih', val: margin + '%',   cls: parseFloat(margin) >= 10 ? 'text-green-700' : 'text-red-600', bg: 'bg-gray-50' },
    { label: 'Jml Transaksi', val: txCount + ' tx', cls: 'text-gray-800',  bg: 'bg-gray-50'   },
  ];

  const FILTERS = [['today','Hari Ini'],['week','7 Hari'],['month','Bulan Ini'],['all','Semua']];

  return (
    <div className="p-5 page-enter">
      <div className="flex items-center justify-between mb-5">
        <div className="flex items-center gap-3">
          <h1 className="text-xl font-black text-gray-800">Laporan Keuangan</h1>
          <span className="flex items-center gap-1.5 text-xs text-green-600 font-semibold
                           bg-green-50 px-2.5 py-1 rounded-full border border-green-100">
            <span className="w-1.5 h-1.5 bg-green-500 rounded-full live-dot inline-block" />
            Live (5 detik)
          </span>
        </div>
        <div className="flex gap-1.5">
          {FILTERS.map(([v, l]) => (
            <button
              key={v}
              onClick={() => setFilter(v)}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition-all
                          ${filter === v
                            ? 'bg-green-600 text-white shadow-sm'
                            : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'}`}
            >{l}</button>
          ))}
        </div>
      </div>

      {/* Summary cards */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        {SUMMARY.map((s) => (
          <div key={s.label} className={`${s.bg} rounded-2xl p-4`}>
            <p className="text-xs text-gray-500 mb-1">{s.label}</p>
            <p className={`font-black text-lg leading-tight ${s.cls}`}>{s.val}</p>
          </div>
        ))}
      </div>

      {/* Transaction table */}
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="px-5 py-3 border-b border-gray-50 flex items-center justify-between">
          <h2 className="font-bold text-gray-800 text-sm">Riwayat Transaksi</h2>
          <span className="text-xs text-gray-400">{filteredTx.length} transaksi</span>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50">
              <tr>
                {['Waktu','No. Transaksi','Omset','HPP','Laba Kotor','Pembayaran'].map((h) => (
                  <th key={h}
                      className="px-4 py-3 text-left text-xs font-semibold text-gray-500 whitespace-nowrap">
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-50">
              {filteredTx.length === 0
                ? (
                  <tr>
                    <td colSpan="6">
                      <Empty icon="📊" label="Belum ada transaksi dalam periode ini." />
                    </td>
                  </tr>
                )
                : filteredTx.map((tx) => (
                  <tr
                    key={tx.id}
                    className={`hover:bg-gray-50/50 ${newTxIds.has(tx.id) ? 'tx-highlight-new' : ''}`}
                  >
                    <td className="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                      {formatDate(tx.date)}
                    </td>
                    <td className="px-4 py-3 font-mono text-xs text-gray-500">
                      #{tx.id.slice(-8).toUpperCase()}
                    </td>
                    <td className="px-4 py-3 font-bold text-green-700">{formatRp(tx.total)}</td>
                    <td className="px-4 py-3 text-orange-600">{formatRp(tx.totalHPP)}</td>
                    <td className="px-4 py-3 text-blue-700 font-semibold">{formatRp(tx.grossProfit)}</td>
                    <td className="px-4 py-3">
                      <span className="px-2 py-0.5 bg-gray-100 text-gray-600
                                       text-xs rounded-full font-medium">
                        {tx.paymentMethod}
                      </span>
                    </td>
                  </tr>
                ))
              }
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   AI DIAGNOSTIC MODULE
───────────────────────────────────────────────────────────── */
function AIDiagnosticModule({ business }) {
  const [result,  setResult]  = useState(null);
  const [loading, setLoading] = useState(false);

  async function runDiagnostic() {
    setLoading(true);
    setResult(null);
    await new Promise((r) => setTimeout(r, 1500));

    const [transactions, opexList, ingredients, wasteList] = await Promise.all([
      window.db.transactions.where('businessId').equals(business.id).toArray(),
      window.db.opex_entries.where('businessId').equals(business.id).toArray(),
      window.db.ingredients.where('businessId').equals(business.id).toArray(),
      window.db.waste_entries.where('businessId').equals(business.id).toArray(),
    ]);

    const omset      = transactions.reduce((s, t) => s + t.total, 0);
    const hpp        = transactions.reduce((s, t) => s + t.totalHPP, 0);
    const gross      = omset - hpp;
    const totalOPEX  = opexList.reduce((s, e) => s + e.amount, 0);
    const net        = gross - totalOPEX;
    const grossMarginPct = omset > 0 ? (gross / omset) * 100 : 0;
    const netMarginPct   = omset > 0 ? (net   / omset) * 100 : 0;
    const opexRatio      = omset > 0 ? (totalOPEX / omset) * 100 : 0;
    const wasteValue     = wasteList.reduce((s, w) => s + w.valueLost, 0);
    const wasteRatio     = hpp > 0 ? (wasteValue / hpp) * 100 : 0;
    const txCount        = transactions.length;
    const lowStock       = ingredients.filter((i) => i.minStock > 0 && i.stock <= i.minStock);
    const zeroStock      = ingredients.filter((i) => i.stock <= 0);

    let score       = 100;
    const issues    = [];
    const actions   = [];

    if (txCount === 0) {
      score = 0;
      issues.push({ level: 'critical', msg: 'Tidak ada data transaksi sama sekali. Mulai gunakan modul Kasir.' });
    } else {
      /* ── Margin Kotor ── */
      if (grossMarginPct < 20) {
        score -= 28;
        issues.push({ level: 'critical', msg: `Margin kotor sangat rendah: ${grossMarginPct.toFixed(1)}%. Target minimal 30%.` });
        actions.push('🔴 Naikkan harga jual atau negosiasikan harga bahan ke supplier. Cek resep apakah ada bahan boros.');
      } else if (grossMarginPct < 35) {
        score -= 12;
        issues.push({ level: 'warning', msg: `Margin kotor cukup rendah: ${grossMarginPct.toFixed(1)}%. Target ideal ≥ 35%.` });
        actions.push('🟡 Evaluasi penetapan harga. Pertimbangkan mengurangi porsi atau cari bahan alternatif lebih ekonomis.');
      } else {
        actions.push(`✅ Margin kotor sehat: ${grossMarginPct.toFixed(1)}%. Pertahankan!`);
      }

      /* ── OPEX Ratio ── */
      if (opexRatio > 45) {
        score -= 22;
        issues.push({ level: 'critical', msg: `OPEX sangat tinggi: ${opexRatio.toFixed(1)}% dari omset. Batas aman ≤ 25%.` });
        actions.push('🔴 Lakukan audit menyeluruh: hemat listrik/air, renegosiasi sewa, evaluasi struktur gaji. Pangkas biaya promosi yang tidak efektif.');
      } else if (opexRatio > 25) {
        score -= 10;
        issues.push({ level: 'warning', msg: `OPEX agak tinggi: ${opexRatio.toFixed(1)}%. Awasi pembengkakan.` });
        actions.push('🟡 Identifikasi pos OPEX terbesar dan cari efisiensi. Target turunkan 5% bulan ini.');
      } else if (totalOPEX > 0) {
        actions.push(`✅ Rasio OPEX terkendali: ${opexRatio.toFixed(1)}%.`);
      }

      /* ── Profitabilitas Bersih ── */
      if (net < 0) {
        score -= 20;
        issues.push({ level: 'critical', msg: `Bisnis MERUGI: ${formatRp(Math.abs(net))}. Laba bersih negatif!` });
        actions.push('🔴 DARURAT: Hentikan sementara pengeluaran non-esensial. Fokus naikkan volume penjualan dan potong OPEX segera.');
      } else if (netMarginPct < 8) {
        score -= 10;
        issues.push({ level: 'warning', msg: `Laba bersih tipis: ${netMarginPct.toFixed(1)}% dari omset. Target ≥ 10%.` });
        actions.push('🟡 Cari celah efisiensi di dua front: naikkan harga 5–10% atau kurangi OPEX. Uji mana yang lebih mudah diterima pasar.');
      } else {
        actions.push(`✅ Profitabilitas bersih: ${netMarginPct.toFixed(1)}%. Bisnis menguntungkan!`);
      }

      /* ── Waste ── */
      if (wasteRatio > 20) {
        score -= 15;
        issues.push({ level: 'critical', msg: `Waste sangat tinggi: ${wasteRatio.toFixed(1)}% dari HPP (senilai ${formatRp(wasteValue)}).` });
        actions.push('🔴 Terapkan FIFO (First In First Out) ketat. Kecilkan batch pembelian bahan mudah basi. Optimalkan porsi resep.');
      } else if (wasteRatio > 8) {
        score -= 8;
        issues.push({ level: 'warning', msg: `Waste cukup tinggi: ${wasteRatio.toFixed(1)}% dari HPP (${formatRp(wasteValue)}).` });
        actions.push('🟡 Monitor rotasi stok harian. Pertimbangkan promosi "habis stok" untuk bahan mendekati expired.');
      } else if (wasteValue > 0) {
        actions.push(`✅ Waste terkendali: ${wasteRatio.toFixed(1)}% dari HPP.`);
      }

      /* ── Stok ── */
      if (zeroStock.length > 0) {
        score -= 10;
        issues.push({ level: 'critical', msg: `${zeroStock.length} bahan habis stok: ${zeroStock.map((i) => i.name).join(', ')}.` });
        actions.push('🔴 Lakukan pembelian bahan segera untuk menghindari kehabisan menu andalan.');
      }
      if (lowStock.length > 0) {
        score -= 5;
        issues.push({ level: 'warning', msg: `${lowStock.length} bahan mendekati habis: ${lowStock.map((i) => i.name).join(', ')}.` });
        actions.push('🟡 Jadwalkan pembelian dalam 1–2 hari ke depan untuk bahan yang stoknya menipis.');
      }

      /* ── Data coverage ── */
      if (txCount < 5) {
        score -= 5;
        issues.push({ level: 'info', msg: `Data transaksi masih sedikit (${txCount} tx). Akurasi diagnostik akan meningkat seiring waktu.` });
      }
    }

    score = Math.max(0, Math.min(100, Math.round(score)));

    setResult({
      score, issues, actions,
      stats: { omset, hpp, gross, net, grossMarginPct, netMarginPct, opexRatio, txCount, wasteValue, wasteRatio },
    });
    setLoading(false);
  }

  const scoreColor = result
    ? (result.score >= 75 ? '#16a34a' : result.score >= 50 ? '#d97706' : '#dc2626')
    : '#d1d5db';

  const STATUS_LABEL = result
    ? (result.score >= 75 ? '💪 Bisnis Sehat' : result.score >= 50 ? '⚠️ Perlu Perhatian' : '🚨 Kondisi Kritis')
    : '';

  const CIRCUMFERENCE = 2 * Math.PI * 72;
  const dashOffset    = result ? CIRCUMFERENCE * (1 - result.score / 100) : CIRCUMFERENCE;

  const LEVEL_STYLE = {
    critical: 'bg-red-50 border-red-200 text-red-800',
    warning:  'bg-yellow-50 border-yellow-200 text-yellow-800',
    info:     'bg-blue-50 border-blue-200 text-blue-700',
  };
  const LEVEL_LABEL = { critical: '🔴 Kritis', warning: '🟡 Peringatan', info: '🔵 Info' };

  return (
    <div className="p-5 page-enter">
      <div className="mb-5">
        <h1 className="text-xl font-black text-gray-800">AI Health Diagnostic</h1>
        <p className="text-sm text-gray-500 mt-0.5">
          Analisis otomatis kesehatan bisnis berdasarkan data transaksi, HPP, OPEX, gudang, dan waste.
        </p>
      </div>

      {/* Score ring */}
      <div className="flex flex-col items-center py-6">
        <svg width="190" height="190" viewBox="0 0 190 190">
          <circle cx="95" cy="95" r="72" fill="none" stroke="#e5e7eb" strokeWidth="14" />
          <circle
            cx="95" cy="95" r="72" fill="none"
            stroke={scoreColor} strokeWidth="14" strokeLinecap="round"
            strokeDasharray={CIRCUMFERENCE}
            strokeDashoffset={loading ? CIRCUMFERENCE : dashOffset}
            transform="rotate(-90 95 95)"
            style={{ transition: 'stroke-dashoffset 1.3s cubic-bezier(.4,0,.2,1), stroke 0.5s' }}
          />
          <text x="95" y="88" textAnchor="middle" fontSize="36"
                fontWeight="900" fill={scoreColor} fontFamily="system-ui, sans-serif">
            {loading ? '…' : result ? result.score : '—'}
          </text>
          <text x="95" y="110" textAnchor="middle" fontSize="11"
                fill="#9ca3af" fontFamily="system-ui, sans-serif">
            AI Health Score
          </text>
        </svg>

        {result && (
          <p className={`text-lg font-black -mt-2 mb-1
                         ${result.score >= 75 ? 'text-green-700' : result.score >= 50 ? 'text-yellow-700' : 'text-red-700'}`}>
            {STATUS_LABEL}
          </p>
        )}

        <button
          onClick={runDiagnostic} disabled={loading}
          className="mt-4 px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-500
                     text-white font-black rounded-2xl text-sm shadow-xl
                     hover:opacity-90 transition-opacity disabled:opacity-60
                     flex items-center gap-2"
        >
          {loading && <Spinner small />}
          {loading ? 'Menganalisis data...' : '🤖 Jalankan AI Diagnostic'}
        </button>
      </div>

      {result && (
        <div className="space-y-5 max-w-2xl mx-auto pb-6">
          {/* Stats */}
          <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
            {[
              ['Total Transaksi',  result.stats.txCount + ' tx',                       'text-gray-800',  'bg-gray-50'],
              ['Omset',           formatRp(result.stats.omset),                        'text-green-700', 'bg-green-50'],
              ['Laba Bersih',     formatRp(result.stats.net),                          result.stats.net >= 0 ? 'text-green-700' : 'text-red-700', result.stats.net >= 0 ? 'bg-green-50' : 'bg-red-50'],
              ['Margin Kotor',    result.stats.grossMarginPct.toFixed(1) + '%',        result.stats.grossMarginPct >= 35 ? 'text-green-700' : result.stats.grossMarginPct >= 20 ? 'text-yellow-700' : 'text-red-700', 'bg-gray-50'],
              ['Rasio OPEX',      result.stats.opexRatio.toFixed(1) + '%',             result.stats.opexRatio <= 25 ? 'text-green-700' : 'text-red-700', 'bg-gray-50'],
              ['Kerugian Waste',  formatRp(result.stats.wasteValue),                   'text-orange-600', 'bg-orange-50'],
            ].map(([l, v, cls, bg]) => (
              <div key={l} className={`${bg} rounded-2xl p-4`}>
                <p className="text-xs text-gray-400 mb-1">{l}</p>
                <p className={`font-black text-base leading-tight ${cls}`}>{v}</p>
              </div>
            ))}
          </div>

          {/* Issues */}
          {result.issues.length > 0 && (
            <div>
              <h3 className="font-bold text-gray-800 mb-2.5">📋 Temuan Diagnostik</h3>
              <div className="space-y-2">
                {result.issues.map((issue, i) => (
                  <div
                    key={i}
                    className={`flex items-start gap-3 px-4 py-3 rounded-xl border text-sm
                                ${LEVEL_STYLE[issue.level]}`}
                  >
                    <span className="font-bold text-xs whitespace-nowrap mt-0.5 flex-shrink-0">
                      {LEVEL_LABEL[issue.level]}
                    </span>
                    <span>{issue.msg}</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Actions */}
          {result.actions.length > 0 && (
            <div>
              <h3 className="font-bold text-gray-800 mb-2.5">🎯 Rekomendasi Aksi Konkret</h3>
              <div className="space-y-2">
                {result.actions.map((a, i) => (
                  <div
                    key={i}
                    className="flex items-start gap-3 px-4 py-3 bg-white rounded-xl
                               border border-gray-100 shadow-sm text-sm text-gray-700"
                  >
                    <span className="text-gray-300 mt-0.5 flex-shrink-0">→</span>
                    <span>{a}</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          {result.issues.length === 0 && (
            <div className="bg-green-50 border border-green-200 rounded-2xl px-5 py-4 text-center">
              <p className="text-green-700 font-bold">🎉 Semua indikator dalam kondisi baik!</p>
              <p className="text-green-600 text-sm mt-1">Bisnis Anda berjalan dengan sehat. Terus tingkatkan!</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
}

/* ─────────────────────────────────────────────────────────────
   ROOT APP COMPONENT
   Mengelola session persistence tanpa Library routing.
   Sesi disimpan di localStorage; refresh/tutup browser
   TIDAK akan melogout pengguna.
───────────────────────────────────────────────────────────── */
function App() {
  const [currentUser,     setCurrentUser]     = useState(null);
  const [currentBusiness, setCurrentBusiness] = useState(null);
  const [ready,           setReady]           = useState(false);

  /* ── Restore session saat pertama load ── */
  useEffect(() => {
    try {
      const session = window.lsGet('session');
      if (session && session.user) {
        setCurrentUser(session.user);
        if (session.business) setCurrentBusiness(session.business);
      }
    } catch { /* ignore */ }
    setReady(true);
  }, []);

  function handleLogin(user) {
    setCurrentUser(user);
    saveSession(user, null);
  }

  function handleSelectBusiness(business) {
    setCurrentBusiness(business);
    saveSession(currentUser, business);
  }

  function handleLogout() {
    clearSession();
    setCurrentUser(null);
    setCurrentBusiness(null);
  }

  function handleSwitchBusiness() {
    setCurrentBusiness(null);
    saveSession(currentUser, null);
  }

  /* ── Loading screen ── */
  if (!ready) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-green-50">
        <div className="w-12 h-12 border-4 border-green-500 border-t-transparent
                        rounded-full spinner mb-4" />
        <p className="text-green-700 font-bold text-sm">Memuat KasirKu…</p>
      </div>
    );
  }

  if (!currentUser) {
    return <AuthScreen onLogin={handleLogin} />;
  }

  if (!currentBusiness) {
    return (
      <BusinessSelector
        user={currentUser}
        onSelect={handleSelectBusiness}
        onLogout={handleLogout}
      />
    );
  }

  return (
    <MainApp
      user={currentUser}
      business={currentBusiness}
      onLogout={handleLogout}
      onSwitchBusiness={handleSwitchBusiness}
    />
  );
}

/* ─────────────────────────────────────────────────────────────
   MOUNT – React 18 createRoot
───────────────────────────────────────────────────────────── */
const _root = ReactDOM.createRoot(document.getElementById('root'));
_root.render(<App />);
