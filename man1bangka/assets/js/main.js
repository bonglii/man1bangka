/* ============================================================
   MAN 1 BANGKA — Main JavaScript
   ============================================================ */

// Auto-detect API path based on page depth
const _depth = (window.location.pathname.match(/\//g) || []).length - 1;
const API_BASE = (_depth <= 1 ? '' : '../') + 'php/api.php';

// ============================================================
// SECURITY: HTML ESCAPE
// Semua data dari API wajib melewati esc() sebelum dimasukkan
// ke innerHTML untuk mencegah XSS (Cross-Site Scripting).
// ============================================================
function esc(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// ============================================================
// UTILITY FUNCTIONS
// ============================================================
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

function apiGet(module, action = 'list', params = {}) {
  const qs = new URLSearchParams({ module, action, ...params }).toString();
  return fetch(`${API_BASE}?${qs}`).then(r => r.json()).catch(() => ({ status: 'error', data: [] }));
}

function apiPost(module, action, data) {
  data.append ? null : void 0;
  const fd = data instanceof FormData ? data : (() => { const f = new FormData(); Object.entries(data).forEach(([k, v]) => f.append(k, v)); return f; })();
  return fetch(`${API_BASE}?module=${module}&action=${action}`, { method: 'POST', body: fd })
    .then(r => r.json()).catch(() => ({ status: 'error', message: 'Gagal terhubung ke server' }));
}

function showAlert(msg, type = 'success') {
  const el = document.createElement('div');
  el.className = `alert-toast alert-${type}`;
  el.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
  el.style.cssText = `position:fixed;top:90px;right:20px;z-index:9999;padding:14px 22px;border-radius:10px;
    background:${type === 'success' ? '#1a6b3c' : '#dc2626'};color:#fff;font-size:.88rem;font-weight:600;
    box-shadow:0 8px 24px rgba(0,0,0,.18);display:flex;align-items:center;gap:8px;
    animation:fadeInUp .4s ease;max-width:340px;`;
  document.body.appendChild(el);
  setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(() => el.remove(), 300); }, 3500);
}

function formatDate(dateStr) {
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const d = new Date(dateStr);
  if (isNaN(d)) return dateStr;
  return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

// ============================================================
// NAVBAR
// ============================================================
function initNavbar() {
  const navbar = $('.navbar');
  const hamburger = $('.hamburger');
  const mobileNav = $('.mobile-nav');

  function closeMobileNav() {
    hamburger?.classList.remove('open');
    mobileNav?.classList.remove('open');
    document.body.style.overflow = '';
  }

  window.addEventListener('scroll', () => {
    navbar?.classList.toggle('scrolled', window.scrollY > 50);
    $('#scrollTop')?.classList.toggle('show', window.scrollY > 400);
  });

  hamburger?.addEventListener('click', () => {
    hamburger.classList.toggle('open');
    mobileNav?.classList.toggle('open');
    document.body.style.overflow = mobileNav?.classList.contains('open') ? 'hidden' : '';
  });

  // Close mobile nav when any link is tapped
  $$('.mobile-nav a').forEach(a => a.addEventListener('click', closeMobileNav));

  // Close mobile nav when tapping outside (on the page overlay)
  document.addEventListener('click', (e) => {
    if (
      mobileNav?.classList.contains('open') &&
      !mobileNav.contains(e.target) &&
      !hamburger?.contains(e.target)
    ) {
      closeMobileNav();
    }
  });

  // Active link
  const current = location.pathname.split('/').pop() || 'index.php';
  $$('.navbar__nav a, .mobile-nav a').forEach(a => {
    const href = a.getAttribute('href')?.split('/').pop();
    if (href === current) a.classList.add('active');
  });

  // Scroll to top
  $('#scrollTop')?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

// ============================================================
// REVEAL ON SCROLL
// ============================================================
function initReveal() {
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
  $$('.reveal').forEach((el, i) => {
    el.style.transitionDelay = `${i * 0.05}s`;
    obs.observe(el);
  });
}

// ============================================================
// TABS
// ============================================================
function initTabs(container = document) {
  $$('.tab-btn', container).forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.tab;
      const parent = btn.closest('.tabs') || btn.closest('section');
      $$('.tab-btn', parent || container).forEach(b => b.classList.remove('active'));
      $$('.tab-panel', parent || container).forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      $(`#${target}`, parent || container)?.classList.add('active');
    });
  });
}

// ============================================================
// FILTER BUTTONS (Prestasi, etc.)
// ============================================================
function initFilter() {
  $$('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const parent = btn.closest('[data-filter-group]') || btn.closest('section');
      $$('.filter-btn', parent).forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      const cards = $$('[data-cat]', parent);
      cards.forEach(card => {
        card.style.display = (filter === 'all' || card.dataset.cat === filter) ? '' : 'none';
      });
    });
  });
}

// ============================================================
// COUNTER ANIMATION
// ============================================================
function animateCounter(el) {
  const target = parseInt(el.dataset.target || el.textContent.replace(/\D/g, ''));
  const suffix = el.dataset.suffix || '';
  let current = 0;
  const duration = 2000; const step = duration / 60;
  const inc = target / (duration / step);
  const timer = setInterval(() => {
    current = Math.min(current + inc, target);
    el.textContent = Math.floor(current).toLocaleString('id-ID') + suffix;
    if (current >= target) clearInterval(timer);
  }, step);
}
function initCounters() {
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { animateCounter(e.target); obs.unobserve(e.target); } });
  }, { threshold: 0.5 });
  $$('[data-counter]').forEach(el => obs.observe(el));
}

// ============================================================
// TESTIMONI SLIDER
// ============================================================
// Track slider interval so it can be cleared on re-init
let _sliderInterval = null;

function initSlider() {
  const track = $('.testimoni-track');
  if (!track) return;
  const cards = $$('.testimoni-card', track);
  if (!cards.length) return;
  let current = 0;
  const visible = window.innerWidth > 1024 ? 3 : window.innerWidth > 640 ? 2 : 1;
  const maxIdx = Math.max(0, cards.length - visible);

  function goTo(idx) {
    current = Math.max(0, Math.min(idx, maxIdx));
    const cardW = cards[0].offsetWidth + 24;
    track.style.transform = `translateX(-${current * cardW}px)`;
  }

  // Remove duplicate listeners by replacing buttons with clones
  const prevBtn = $('.slider-btn.prev');
  const nextBtn = $('.slider-btn.next');
  if (prevBtn) {
    const p = prevBtn.cloneNode(true);
    prevBtn.parentNode.replaceChild(p, prevBtn);
    p.addEventListener('click', () => goTo(current - 1));
  }
  if (nextBtn) {
    const n = nextBtn.cloneNode(true);
    nextBtn.parentNode.replaceChild(n, nextBtn);
    n.addEventListener('click', () => goTo(current + 1));
  }

  // Clear previous interval before starting new one
  if (_sliderInterval) clearInterval(_sliderInterval);
  _sliderInterval = setInterval(() => goTo(current >= maxIdx ? 0 : current + 1), 5000);
}

// ============================================================
// LOAD PENGUMUMAN (index.html)
// ============================================================
async function loadPengumumanHighlight() {
  const container = $('#highlight-container');
  if (!container) return;
  const res = await apiGet('pengumuman', 'highlight');
  if (res.status === 'success' && res.data.length) {
    container.innerHTML = res.data.map(p => `
      <div class="highlight-card reveal">
        <span class="card__tag tag-${p.kategori}">${p.kategori}</span>
        <h3>${esc(p.judul)}</h3>
        <p>${esc(p.isi.substring(0, 120))}...</p>
        <div class="date"><i class="far fa-calendar-alt"></i> ${p.tanggal_publish_format}</div>
      </div>
    `).join('');
    initReveal();
  } else {
    container.innerHTML = `<div class="empty-state"><i class="far fa-bell-slash"></i><p>Belum ada pengumuman.</p></div>`;
  }
}

// ============================================================
// LOAD AGENDA UPCOMING (index.html)
// ============================================================
async function loadAgendaUpcoming() {
  const container = $('#agenda-upcoming');
  if (!container) return;
  const res = await apiGet('agenda', 'upcoming');
  if (res.status === 'success' && res.data.length) {
    container.innerHTML = res.data.map(a => {
      const d = new Date(a.tanggal_mulai);
      const day = d.getDate();
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const mon = months[d.getMonth()];
      return `
        <div class="agenda-item reveal">
          <div class="agenda-date"><div class="day">${day}</div><div class="month">${mon}</div></div>
          <div class="agenda-info">
            <h4>${esc(a.judul)}</h4>
            <p>${esc(a.deskripsi || '')}</p>
            <div class="agenda-meta">
              ${a.lokasi ? `<span><i class="fas fa-map-marker-alt"></i>${esc(a.lokasi)}</span>` : ''}
              <span class="badge badge-green">${a.kategori}</span>
            </div>
          </div>
        </div>`;
    }).join('');
    initReveal();
  } else {
    container.innerHTML = `<div class="empty-state"><i class="far fa-calendar"></i><p>Tidak ada agenda mendatang.</p></div>`;
  }
}

// ============================================================
// LOAD PRESTASI (index.html highlight)
// ============================================================
async function loadPrestasiHighlight() {
  const container = $('#prestasi-highlight');
  if (!container) return;
  const res = await apiGet('prestasi');
  if (res.status === 'success' && res.data.length) {
    const medals = { nasional: '🥇', provinsi: '🥈', kabupaten: '🥉', sekolah: '⭐' };
    container.innerHTML = res.data.slice(0, 4).map(p => `
      <div class="prestasi-card ${p.tingkat} reveal" data-cat="${p.tingkat}">
        <div class="prestasi-medali">${medals[p.tingkat] || '⭐'}</div>
        <h4>${esc(p.judul)}</h4>
        <div class="prestasi-siswa">${esc(p.siswa)} — ${esc(p.kelas)}</div>
        <div class="prestasi-tags">
          <span class="badge badge-${p.tingkat === 'nasional' ? 'red' : p.tingkat === 'provinsi' ? 'gold' : 'blue'}">${esc(p.tingkat)}</span>
          <span class="badge badge-green">${esc(p.posisi)}</span>
        </div>
        <div class="prestasi-year">${p.tahun}</div>
      </div>
    `).join('');
    initReveal();
  }
}

// ============================================================
// LOAD EKSTRAKURIKULER
// ============================================================
async function loadEkskul(containerId = 'ekskul-container', filter = '') {
  const container = $(`#${containerId}`);
  if (!container) return;
  container.innerHTML = `<div class="loading"><div class="spinner"></div></div>`;
  const params = filter ? { kategori: filter } : {};
  const res = await apiGet('ekskul', 'list', params);
  const icons = { olahraga: '⚽', seni: '🎨', akademik: '📚', keagamaan: '🕌', teknologi: '💻', lainnya: '🌟' };
  if (res.status === 'success' && res.data.length) {
    container.innerHTML = res.data.map(e => `
      <div class="ekskul-card reveal" data-cat="${e.kategori}">
        <div class="ekskul-card__header">
          <div class="ekskul-card__icon">${icons[e.kategori] || '🌟'}</div>
          <h3 class="ekskul-card__title">${esc(e.nama)}</h3>
          <span class="badge badge-gold">${e.kategori}</span>
        </div>
        <div class="ekskul-card__body">
          <p style="font-size:.87rem;margin-bottom:1rem;">${esc(e.deskripsi || '')}</p>
          <div class="ekskul-card__row"><i class="fas fa-user-tie"></i><span>${esc(e.nama_pembina || '-')}</span></div>
          <div class="ekskul-card__row"><i class="far fa-clock"></i><span>${esc(e.jadwal || '-')}</span></div>
          <div class="ekskul-card__row"><i class="fas fa-map-marker-alt"></i><span>${esc(e.tempat || '-')}</span></div>
        </div>
        <div class="ekskul-card__footer">
          <button class="btn btn-green btn-sm" onclick="openDaftarModal(${e.id}, '${esc(e.nama).replace(/'/g, "\\'")}')">
            <i class="fas fa-pen"></i> Daftar Sekarang
          </button>
        </div>
      </div>
    `).join('');
    initReveal();
  } else {
    container.innerHTML = `<div class="empty-state"><i class="fas fa-users"></i><p>Data tidak tersedia.</p></div>`;
  }
}

// ============================================================
// LOAD PRESTASI (full page)
// ============================================================
async function loadPrestasi(containerId = 'prestasi-container', filter = '') {
  const container = $(`#${containerId}`);
  if (!container) return;
  container.innerHTML = `<div class="loading"><div class="spinner"></div></div>`;
  const params = filter ? { tingkat: filter } : {};
  const res = await apiGet('prestasi', 'list', params);
  const medals = { nasional: '🥇', provinsi: '🥈', kabupaten: '🥉', sekolah: '⭐', internasional: '🏆' };
  if (res.status === 'success' && res.data.length) {
    container.className = 'prestasi-grid';
    container.innerHTML = res.data.map(p => `
      <div class="prestasi-card ${p.tingkat} reveal" data-cat="${p.tingkat}">
        <div class="prestasi-medali">${medals[p.tingkat] || '⭐'}</div>
        <h4>${esc(p.judul)}</h4>
        <div class="prestasi-siswa"><i class="fas fa-user"></i> ${esc(p.siswa)}</div>
        <div style="font-size:.8rem;color:var(--gray-400);margin-bottom:.5rem;">${esc(p.penyelenggara || '')}</div>
        <div class="prestasi-tags">
          <span class="badge badge-${p.tingkat === 'nasional' ? 'red' : 'blue'}">${esc(p.tingkat)}</span>
          <span class="badge badge-green">${esc(p.posisi)}</span>
          <span class="badge badge-gold">${p.jenis}</span>
        </div>
        <div class="prestasi-year">${p.tahun}</div>
      </div>
    `).join('');
    initReveal();
  } else {
    container.innerHTML = `<div class="empty-state col-full"><i class="fas fa-trophy"></i><p>Belum ada data prestasi.</p></div>`;
  }
}

// ============================================================
// LOAD PENGUMUMAN (full page)
// ============================================================
async function loadPengumuman(containerId = 'pengumuman-container', filter = '') {
  const container = $(`#${containerId}`);
  if (!container) return;
  container.innerHTML = `<div class="loading"><div class="spinner"></div></div>`;
  const params = { limit: 20 };
  if (filter) params.kategori = filter;
  const res = await apiGet('pengumuman', 'list', params);
  if (res.status === 'success' && res.data.length) {
    container.innerHTML = res.data.map(p => `
      <div class="card reveal" data-cat="${p.kategori}">
        <div class="card__body">
          <span class="card__tag tag-${p.kategori}">${p.kategori}</span>
          <h3 class="card__title">${esc(p.judul)}</h3>
          <p class="card__text">${esc(p.isi.substring(0, 180))}${p.isi.length > 180 ? '...' : ''}</p>
          <div class="card__meta">
            <span><i class="far fa-calendar-alt"></i>${p.tanggal_publish_format}</span>
            ${p.tanggal_berakhir ? `<span><i class="fas fa-hourglass-end"></i>Berakhir: ${formatDate(p.tanggal_berakhir)}</span>` : ''}
          </div>
        </div>
        <div class="card__footer">
          ${p.is_highlight ? '<span class="badge badge-gold"><i class="fas fa-star"></i> Highlight</span>' : '<span></span>'}
          <button class="btn btn-green btn-sm" onclick="showDetail('${esc(p.judul).replace(/'/g, "\\'")}','${esc(p.isi).replace(/'/g, "\\'")}')">Selengkapnya</button>
        </div>
      </div>
    `).join('');
    initReveal();
  } else {
    container.innerHTML = `<div class="empty-state"><i class="far fa-bell-slash"></i><p>Belum ada pengumuman.</p></div>`;
  }
}

// ============================================================
// LOAD TESTIMONI
// ============================================================
async function loadTestimoni() {
  const track = $('.testimoni-track');
  if (!track) return;
  const res = await apiGet('testimoni');
  if (res.status === 'success' && res.data.length) {
    const stars = n => '★'.repeat(n) + '☆'.repeat(5 - n);
    track.innerHTML = res.data.map(t => `
      <div class="testimoni-card">
        <div class="quote">"</div>
        <p class="text">${esc(t.isi)}</p>
        <div class="author">
          <div class="avatar">${esc(t.nama_siswa[0])}</div>
          <div class="author-info">
            <span>${esc(t.nama_siswa)}</span>
            <span>${esc(t.kelas)} — ${esc(t.nama_kegiatan || t.jenis_kegiatan)}</span>
            <div class="stars">${stars(t.rating)}</div>
          </div>
        </div>
      </div>
    `).join('');
    initSlider();
  }
}

// ============================================================
// LOAD KONTAK PEMBINA
// ============================================================
async function loadKontak() {
  const container = $('#kontak-container');
  if (!container) return;
  const res = await apiGet('pembina');
  if (res.status === 'success' && res.data.length) {
    container.innerHTML = res.data.map(k => `
      <div class="kontak-card reveal">
        <div class="kontak-avatar">
          ${k.foto
        ? `<img src="../php/uploads/${k.foto}" alt="${esc(k.nama)}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.style.display='none';this.nextElementSibling.style.display='block'"/><i class="fas fa-user-tie" style="display:none;"></i>`
        : `<i class="fas fa-user-tie"></i>`}
        </div>
        <div class="kontak-info">
          <h4>${esc(k.nama)}</h4>
          <div class="jabatan">${esc(k.jabatan || '')}</div>
          ${k.bidang ? `<div class="contact-row"><i class="fas fa-briefcase"></i>${esc(k.bidang)}</div>` : ''}
          ${k.email ? `<div class="contact-row"><i class="fas fa-envelope"></i>${esc(k.email)}</div>` : ''}
          ${k.no_hp ? `<div class="contact-row"><i class="fas fa-phone"></i>${esc(k.no_hp)}</div>` : ''}
        </div>
      </div>
    `).join('');
    initReveal();
  } else {
    container.innerHTML = `<div class="empty-state"><i class="fas fa-address-book"></i><p>Data pembina belum tersedia.</p></div>`;
  }
}

// ============================================================
// MODAL DAFTAR EKSKUL
// ============================================================
function openDaftarModal(ekskulId, ekskulNama) {
  const modal = $('#modal-daftar');
  if (!modal) return;
  $('#modal-ekskul-nama').textContent = ekskulNama;
  $('input[name="ekstrakurikuler_id"]', modal).value = ekskulId;
  modal.classList.add('open');
}

// ============================================================
// SHOW DETAIL MODAL
// ============================================================
function showDetail(judul, isi) {
  const modal = $('#modal-detail');
  if (!modal) return;
  $('#modal-detail-title').textContent = judul;
  $('#modal-detail-body').textContent = isi;
  modal.classList.add('open');
}

// ============================================================
// FORM HANDLERS
// ============================================================
function initForms() {
  // Testimoni form
  const formTestimoni = $('#form-testimoni');
  formTestimoni?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = formTestimoni.querySelector('[type=submit]');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    const fd = new FormData(formTestimoni);
    const res = await apiPost('testimoni', 'tambah', fd);
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Testimoni';
    if (res.status === 'success') {
      showAlert('Testimoni berhasil dikirim!');
      formTestimoni.reset();
      // Reset rating stars visual back to 5
      const ratingInput = document.getElementById('rating-value');
      if (ratingInput) ratingInput.value = 5;
      document.querySelectorAll('#rating-stars .fa-star').forEach((s, i) => s.classList.toggle('active', i < 5));
    } else {
      showAlert(res.message || 'Gagal mengirim testimoni', 'error');
    }
  });

  // Daftar Ekskul form
  const formEkskul = $('#form-daftar-ekskul');
  formEkskul?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = formEkskul.querySelector('[type=submit]');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftar...';
    const fd = new FormData(formEkskul);
    const res = await apiPost('daftar_ekskul', '', fd);
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Daftar Sekarang';
    if (res.status === 'success') {
      showAlert(res.message);
      formEkskul.reset();
      $('#modal-daftar')?.classList.remove('open');
    } else {
      showAlert(res.message || 'Gagal mendaftar', 'error');
    }
  });

  // Close modals
  $$('.modal__close, .modal-overlay').forEach(el => {
    el.addEventListener('click', (e) => {
      if (e.target === el) el.closest('.modal-overlay')?.classList.remove('open');
    });
  });
  $$('.modal').forEach(m => m.addEventListener('click', e => e.stopPropagation()));
}

// ============================================================
// SEARCH
// ============================================================
function initSearch(inputSel, cardsSel) {
  const input = $(inputSel);
  if (!input) return;
  input.addEventListener('input', () => {
    const q = input.value.toLowerCase();
    $$(cardsSel).forEach(card => {
      card.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

// ============================================================
// HERO PARALLAX (subtle)
// ============================================================
function initParallax() {
  const hero = $('.hero__pattern');
  if (!hero) return;
  window.addEventListener('scroll', () => {
    hero.style.transform = `translateY(${window.scrollY * 0.15}px)`;
  }, { passive: true });
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  initNavbar();
  initReveal();
  initTabs();
  initFilter();
  initCounters();
  initForms();
  initParallax();

  // Page-specific loaders
  const page = location.pathname.split('/').pop() || 'index.php';

  if (page === 'index.php' || page === 'index.html' || page === '') {
    loadPengumumanHighlight();
    loadAgendaUpcoming();
    loadPrestasiHighlight();
    loadTestimoni();
  }
  if (page === 'pengumuman.php') loadPengumuman();
  if (page === 'ekstrakurikuler.php') loadEkskul();
  if (page === 'prestasi.php') loadPrestasi();
  if (page === 'testimoni.php') { loadTestimoni(); }
  if (page === 'kontak.php') loadKontak();
});

// Expose globals
window.openDaftarModal = openDaftarModal;
window.showDetail = showDetail;
window.loadEkskul = loadEkskul;
window.loadPrestasi = loadPrestasi;
window.loadPengumuman = loadPengumuman;
