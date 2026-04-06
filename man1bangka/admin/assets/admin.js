/* ============================================================
   Admin Panel JS v4 — MAN 1 Bangka
   Changelog v4:
     - Fix: stray 's' syntax error pada tab switcher (baris 12 lama)
     - Feat: Modern Toast Notification menggantikan showMsg inline
     - Feat: Global Page Loading Bar (top progress bar)
     - Feat: Modern Confirm Dialog menggantikan browser confirm()
     - Fix: semua confirm() diintercept dan pakai modal modern
   ============================================================ */

/* ============================================================
   TAB SWITCHING
   ============================================================ */
document.querySelectorAll('.admin-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    const grp = btn.closest('[data-tab-group]') || btn.closest('.admin-card') || document;
    grp.querySelectorAll('.admin-tab').forEach(b => b.classList.remove('active'));
    grp.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(btn.dataset.tab)?.classList.add('active');
  });
}); // <-- baris 12 lama ada 's' ekstra di sini — sudah dihapus

/* ============================================================
   MOBILE SIDEBAR TOGGLE
   ============================================================ */
const sidebar = document.getElementById('adminSidebar');
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
  sidebar?.classList.toggle('open');
});
document.addEventListener('click', e => {
  if (sidebar?.classList.contains('open') &&
    !sidebar.contains(e.target) &&
    !document.getElementById('sidebarToggle')?.contains(e.target)) {
    sidebar.classList.remove('open');
  }
});

/* ============================================================
   DRAG & DROP UPLOAD ZONE
   ============================================================ */
document.querySelectorAll('.upload-zone').forEach(zone => {
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
  zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag-over');
    const input = zone.querySelector('input[type=file]');
    if (input) { input.files = e.dataTransfer.files; input.dispatchEvent(new Event('change')); }
  });
  zone.addEventListener('click', e => {
    if (e.target.tagName !== 'INPUT') zone.querySelector('input[type=file]')?.click();
  });
});

/* ============================================================
   FILE PREVIEW
   ============================================================ */
function previewFiles(input, previewId) {
  const prev = document.getElementById(previewId);
  if (!prev) return;
  prev.innerHTML = '';
  [...input.files].forEach((file) => {
    const item = document.createElement('div');
    item.className = 'preview-item';
    const url = URL.createObjectURL(file);
    item.innerHTML = file.type.startsWith('video')
      ? `<video src="${url}" muted></video>`
      : `<img src="${url}" alt="${file.name}"/>`;
    const rm = document.createElement('button');
    rm.className = 'preview-remove'; rm.innerHTML = '✕';
    rm.onclick = e => { e.stopPropagation(); item.remove(); };
    item.appendChild(rm);
    prev.appendChild(item);
  });
}

/* ============================================================
   MODAL HELPERS
   ============================================================ */
function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
document.querySelectorAll('.modal-backdrop').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
document.querySelectorAll('.modal-close').forEach(btn => {
  btn.addEventListener('click', () => btn.closest('.modal-backdrop')?.classList.remove('open'));
});

/* ============================================================
   NUMBER FORMAT
   ============================================================ */
function fmtNum(n) { return Number(n).toLocaleString('id-ID'); }

/* ============================================================
   MODERN TOAST NOTIFICATION
   Menggantikan showMsg() lama yang hanya menampilkan alert inline.
   Toast muncul di pojok kanan bawah, auto-dismiss 3.5 detik,
   bisa ditutup manual, dan mendukung stack (multiple toasts).
   
   Tipe: 'ok' | 'err' | 'warn' | 'info'
   Penggunaan:
     showToast('Data berhasil disimpan!', 'ok')
     showToast('Gagal menghapus!', 'err')
   ============================================================ */
(function () {
  // Buat container toast sekali — letakkan di body
  let toastContainer = document.getElementById('__toast_container__');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = '__toast_container__';
    toastContainer.style.cssText = `
      position:fixed; bottom:24px; right:24px;
      z-index:99999; display:flex; flex-direction:column;
      gap:10px; pointer-events:none;
    `;
    document.body.appendChild(toastContainer);
  }

  const ICONS = {
    ok:   { icon: '✓', bg: '#f0fdf4', border: '#86efac', color: '#15803d', bar: '#22c55e' },
    err:  { icon: '✕', bg: '#fef2f2', border: '#fca5a5', color: '#b91c1c', bar: '#ef4444' },
    warn: { icon: '!', bg: '#fffbeb', border: '#fde68a', color: '#92400e', bar: '#f59e0b' },
    info: { icon: 'i', bg: '#eff6ff', border: '#93c5fd', color: '#1d4ed8', bar: '#3b82f6' },
  };

  window.showToast = function (msg, type = 'ok', duration = 3500) {
    const cfg = ICONS[type] || ICONS.ok;

    const toast = document.createElement('div');
    toast.style.cssText = `
      display:flex; align-items:center; gap:12px;
      background:${cfg.bg}; border:1.5px solid ${cfg.border};
      border-radius:14px; padding:14px 16px;
      min-width:260px; max-width:360px;
      box-shadow:0 8px 32px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
      pointer-events:all; position:relative; overflow:hidden;
      transform:translateX(120%); opacity:0;
      transition:transform 0.38s cubic-bezier(0.34,1.4,0.64,1), opacity 0.3s ease;
    `;

    toast.innerHTML = `
      <div style="
        width:32px; height:32px; border-radius:50%; flex-shrink:0;
        background:${cfg.color}; color:#fff;
        display:flex; align-items:center; justify-content:center;
        font-weight:800; font-size:14px;
      ">${cfg.icon}</div>
      <span style="flex:1; font-size:13.5px; font-weight:600; color:${cfg.color}; line-height:1.4;">${msg}</span>
      <button onclick="this.closest('div[data-toast]').remove()" style="
        background:none; border:none; cursor:pointer;
        color:${cfg.color}; opacity:0.5; font-size:16px; padding:2px 4px;
        flex-shrink:0; line-height:1;
      ">✕</button>
      <div style="
        position:absolute; bottom:0; left:0; height:3px;
        background:${cfg.bar}; border-radius:0 0 0 14px;
        width:100%; transform-origin:left;
        animation:toastBar ${duration}ms linear forwards;
      "></div>
    `;
    toast.setAttribute('data-toast', '1');
    toastContainer.appendChild(toast);

    // Slide in
    requestAnimationFrame(() => {
      toast.style.transform = 'translateX(0)';
      toast.style.opacity = '1';
    });

    // Auto-remove
    setTimeout(() => {
      toast.style.transform = 'translateX(120%)';
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 380);
    }, duration);
  };

  // Backward compat: showMsg(elId, msg, type) sekarang juga trigger toast
  window.showMsg = function (elId, msg, type = 'ok') {
    // Tetap isi element inline jika ada (untuk quick-msg di dashboard)
    const el = document.getElementById(elId);
    if (el) {
      const icons = { ok: 'fa-check-circle', err: 'fa-times-circle', warn: 'fa-exclamation-triangle', info: 'fa-info-circle' };
      el.innerHTML = `<div class="alert alert-${type}"><i class="fas ${icons[type] || icons.ok}"></i>${msg}</div>`;
      if (type === 'ok') setTimeout(() => el.innerHTML = '', 4000);
    }
    // Juga tampilkan sebagai toast
    showToast(msg, type);
  };

  // Inject keyframe untuk progress bar toast
  if (!document.getElementById('__toast_style__')) {
    const s = document.createElement('style');
    s.id = '__toast_style__';
    s.textContent = `
      @keyframes toastBar {
        from { transform: scaleX(1); }
        to   { transform: scaleX(0); }
      }
    `;
    document.head.appendChild(s);
  }
})();

/* ============================================================
   MODERN CONFIRM DIALOG
   Menggantikan browser confirm() yang jelek dan memblokir thread.
   Intercept semua onsubmit="return confirm(...)" secara global,
   plus fungsi confirmDel() untuk pemanggilan manual via JS.

   Penggunaan JS:
     confirmDel('Hapus data ini?').then(ok => { if (ok) doDelete(); });
   Atau:
     confirmDel('Pesan').then(ok => { if (ok) form.submit(); });
   ============================================================ */
(function () {
  // Inject modal HTML ke body (satu kali)
  if (!document.getElementById('__confirm_modal__')) {
    const el = document.createElement('div');
    el.innerHTML = `
      <div id="__confirm_modal__" style="
        display:none; position:fixed; inset:0;
        background:rgba(7,30,19,0.45); backdrop-filter:blur(4px);
        z-index:999999; align-items:center; justify-content:center;
      ">
        <div id="__confirm_box__" style="
          background:#fff; border-radius:20px;
          padding:36px 32px 28px; max-width:380px; width:90%;
          text-align:center; position:relative;
          box-shadow:0 24px 64px rgba(0,0,0,0.2);
          transform:scale(0.88) translateY(20px); opacity:0;
          transition:transform 0.35s cubic-bezier(0.34,1.4,0.64,1), opacity 0.28s ease;
        ">
          <div id="__confirm_icon__" style="font-size:2.8rem; margin-bottom:14px;">⚠️</div>
          <div id="__confirm_title__" style="font-size:16px; font-weight:800; color:#111827; margin-bottom:8px;">Konfirmasi</div>
          <div id="__confirm_msg__" style="font-size:13.5px; color:#6b7280; line-height:1.6; margin-bottom:24px;"></div>
          <div style="display:flex; gap:10px; justify-content:center;">
            <button id="__confirm_cancel__" style="
              flex:1; padding:11px 0; border-radius:10px;
              border:1.5px solid #e5e7eb; background:#f9fafb;
              font-size:14px; font-weight:600; cursor:pointer;
              color:#374151; transition:all 0.18s ease;
            ">Batal</button>
            <button id="__confirm_ok__" style="
              flex:1; padding:11px 0; border-radius:10px;
              border:none; background:linear-gradient(135deg,#dc2626,#b91c1c);
              font-size:14px; font-weight:700; cursor:pointer;
              color:#fff; box-shadow:0 4px 14px rgba(220,38,38,0.35);
              transition:all 0.18s ease;
            ">Ya, Hapus</button>
          </div>
        </div>
      </div>`;
    document.body.appendChild(el.firstElementChild);

    // Hover effects
    const okBtn = document.getElementById('__confirm_ok__');
    const cancelBtn = document.getElementById('__confirm_cancel__');
    okBtn.onmouseover = () => okBtn.style.transform = 'scale(1.03)';
    okBtn.onmouseout  = () => okBtn.style.transform = 'scale(1)';
    cancelBtn.onmouseover = () => cancelBtn.style.background = '#f3f4f6';
    cancelBtn.onmouseout  = () => cancelBtn.style.background = '#f9fafb';
  }

  const overlay = document.getElementById('__confirm_modal__');
  const box     = document.getElementById('__confirm_box__');
  const msgEl   = document.getElementById('__confirm_msg__');
  const titleEl = document.getElementById('__confirm_title__');
  const iconEl  = document.getElementById('__confirm_icon__');
  const okBtn   = document.getElementById('__confirm_ok__');
  const cancelBtn = document.getElementById('__confirm_cancel__');

  let _resolve = null;

  function showConfirm(msg = 'Yakin ingin menghapus data ini?', opts = {}) {
    msgEl.textContent   = msg;
    titleEl.textContent = opts.title  || 'Konfirmasi Hapus';
    iconEl.textContent  = opts.icon   || '🗑️';
    okBtn.textContent   = opts.okText || 'Ya, Hapus';
    okBtn.style.background = opts.okBg || 'linear-gradient(135deg,#dc2626,#b91c1c)';
    okBtn.style.boxShadow  = opts.okShadow || '0 4px 14px rgba(220,38,38,0.35)';

    overlay.style.display = 'flex';
    requestAnimationFrame(() => {
      box.style.transform = 'scale(1) translateY(0)';
      box.style.opacity   = '1';
    });

    return new Promise(resolve => { _resolve = resolve; });
  }

  function closeConfirm(result) {
    box.style.transform = 'scale(0.9) translateY(16px)';
    box.style.opacity   = '0';
    setTimeout(() => { overlay.style.display = 'none'; }, 300);
    if (_resolve) { _resolve(result); _resolve = null; }
  }

  okBtn.addEventListener('click',     () => closeConfirm(true));
  cancelBtn.addEventListener('click', () => closeConfirm(false));
  overlay.addEventListener('click', e => { if (e.target === overlay) closeConfirm(false); });

  // Expose globally
  window.confirmDel = function (msg, opts) { return showConfirm(msg, opts); };
  window._showConfirm = showConfirm;

  /* ----------------------------------------------------------
     Intercept semua form yang pakai onsubmit="return confirm(...)"
     Ganti dengan modal modern secara otomatis.
  ---------------------------------------------------------- */
  function interceptConfirmForms() {
    document.querySelectorAll('form[onsubmit]').forEach(form => {
      const raw = form.getAttribute('onsubmit');
      // Hanya intercept form yang menggunakan return confirm(...)
      if (!raw || !raw.includes('confirm(')) return;
      if (form.dataset.confirmIntercepted) return; // jangan intercept dua kali
      form.dataset.confirmIntercepted = '1';

      // Ekstrak pesan dari confirm('...')
      const match = raw.match(/confirm\(['"](.*?)['"]\)/);
      const confirmMsg = match ? match[1] : 'Yakin ingin melanjutkan?';

      // Hapus onsubmit lama, ganti dengan modern confirm
      form.removeAttribute('onsubmit');
      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const ok = await showConfirm(confirmMsg);
        if (ok) {
          form.removeEventListener('submit', arguments.callee);
          form.submit();
        }
      });
    });
  }

  // Jalankan sekarang dan setiap kali konten halaman diganti (SPA)
  interceptConfirmForms();
  window._interceptConfirmForms = interceptConfirmForms;
})();

/* ============================================================
   PAGE LOADING BAR
   Progress bar tipis di atas halaman yang muncul saat:
   - Navigasi SPA (antar halaman admin)
   - Form submit yang butuh waktu
   Tidak perlu dipanggil manual — otomatis lewat SPA navigation.
   ============================================================ */
(function () {
  // Buat elemen loading bar
  const bar = document.createElement('div');
  bar.id = '__page_loader__';
  bar.style.cssText = `
    position:fixed; top:0; left:0; height:3px; width:0%;
    background:linear-gradient(90deg, #1a6b3c, #4ade80, #1a6b3c);
    background-size:200% 100%;
    z-index:999998; border-radius:0 2px 2px 0;
    transition:width 0.25s ease, opacity 0.3s ease;
    opacity:0; pointer-events:none;
    box-shadow:0 0 8px rgba(26,107,60,0.6);
  `;
  document.body.appendChild(bar);

  // Inject animasi shimmer
  if (!document.getElementById('__loader_style__')) {
    const s = document.createElement('style');
    s.id = '__loader_style__';
    s.textContent = `
      @keyframes loaderShimmer {
        0%   { background-position:200% 0; }
        100% { background-position:-200% 0; }
      }
      #__page_loader__.running {
        animation: loaderShimmer 1.2s linear infinite;
      }
    `;
    document.head.appendChild(s);
  }

  let _timer = null;

  window.pageLoaderStart = function () {
    clearTimeout(_timer);
    bar.style.transition = 'width 0.25s ease, opacity 0.2s ease';
    bar.style.opacity = '1';
    bar.style.width = '0%';
    bar.classList.add('running');
    // Animasi maju cepat ke 70% lalu pelan
    requestAnimationFrame(() => {
      bar.style.width = '30%';
      setTimeout(() => { bar.style.width = '65%'; }, 250);
      setTimeout(() => { bar.style.width = '80%'; }, 600);
    });
  };

  window.pageLoaderDone = function () {
    bar.style.transition = 'width 0.2s ease, opacity 0.4s ease 0.2s';
    bar.style.width = '100%';
    _timer = setTimeout(() => {
      bar.style.opacity = '0';
      bar.classList.remove('running');
      setTimeout(() => { bar.style.width = '0%'; }, 300);
    }, 250);
  };

  window.pageLoaderFail = function () {
    bar.style.background = '#ef4444';
    bar.style.width = '100%';
    _timer = setTimeout(() => {
      bar.style.opacity = '0';
      setTimeout(() => {
        bar.style.width = '0%';
        bar.style.background = 'linear-gradient(90deg, #1a6b3c, #4ade80, #1a6b3c)';
        bar.style.backgroundSize = '200% 100%';
      }, 300);
    }, 400);
  };
})();

/* ============================================================
   SPA-STYLE NAVIGATION
   Intercepts sidebar nav clicks, fetches the target page,
   swaps .page-content + updates topbar title & URL.
   Diperkuat dengan loading bar dan toast on error.
   ============================================================ */
(function () {
  // Halaman dengan file upload kompleks + inline scripts bergantung pada scope global
  // wajib full reload (bukan SPA swap) agar upload preview & validasi file berfungsi.
  const FULL_RELOAD_PAGES = ['media.php', 'karya.php', 'prestasi.php'];

  function isSameOrigin(href) {
    try { return new URL(href, location.href).origin === location.origin; }
    catch { return false; }
  }

  document.addEventListener('click', async function (e) {
    const link = e.target.closest('.nav-link');
    if (!link) return;
    const href = link.getAttribute('href');
    if (!href || href.startsWith('http') || href.includes('logout') || href.includes('../')) return;
    if (FULL_RELOAD_PAGES.some(p => href.includes(p))) {
      const currentPage = location.pathname.split('/').pop();
      if (currentPage === href || location.href.includes(href)) e.preventDefault();
      return;
    }
    if (!isSameOrigin(href)) return;

    e.preventDefault();

    // Tampilkan loading bar
    pageLoaderStart();

    const main = document.querySelector('.page-content');
    if (!main) return;
    main.style.opacity = '0.45';
    main.style.pointerEvents = 'none';

    try {
      const res = await fetch(href, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('fetch failed');
      const html = await res.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const newContent      = doc.querySelector('.page-content');
      const newTitle        = doc.querySelector('.topbar-title');
      const newBread        = doc.querySelector('.topbar-breadcrumb');
      const newTopbarRight  = doc.querySelector('.topbar-right');

      if (newContent) {
        // Hapus modal SPA dari halaman sebelumnya
        document.querySelectorAll('.modal-backdrop[data-spa-modal]').forEach(m => m.remove());

        // Swap konten
        main.innerHTML = newContent.innerHTML;
        main.style.opacity = '1';
        main.style.pointerEvents = '';
        main.style.animation = 'none';
        main.offsetHeight; // reflow
        main.style.animation = 'fadeInUp .32s ease both';

        // Inject modal yang berada di luar .page-content
        doc.querySelectorAll('body > .modal-backdrop').forEach(m => {
          const clone = document.importNode(m, true);
          clone.setAttribute('data-spa-modal', '1');
          document.body.appendChild(clone);
        });

        // Update topbar
        if (newTitle)      document.querySelector('.topbar-title').innerHTML      = newTitle.innerHTML;
        if (newBread)      document.querySelector('.topbar-breadcrumb').innerHTML = newBread.innerHTML;
        if (newTopbarRight) {
          const cur = document.querySelector('.topbar-right');
          if (cur) cur.innerHTML = newTopbarRight.innerHTML;
        }

        // Update link aktif
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        history.pushState({ href }, '', href);
        reinitPageScripts();

        document.querySelector('.admin-main').scrollTo({ top: 0, behavior: 'smooth' });

        // Selesai — sembunyikan loading bar
        pageLoaderDone();
      } else {
        pageLoaderFail();
        location.href = href;
      }
    } catch (err) {
      main.style.opacity = '1';
      main.style.pointerEvents = '';
      pageLoaderFail();
      showToast('Gagal memuat halaman, mencoba navigasi biasa...', 'warn');
      setTimeout(() => { location.href = href; }, 600);
    }
  });

  window.addEventListener('popstate', e => {
    if (e.state?.href) location.href = e.state.href;
  });

  function reinitPageScripts() {
    // Re-init tabs
    document.querySelectorAll('.admin-tab').forEach(btn => {
      btn.addEventListener('click', function () {
        const grp = this.closest('[data-tab-group]') || this.closest('.admin-card') || document;
        grp.querySelectorAll('.admin-tab').forEach(b => b.classList.remove('active'));
        grp.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab)?.classList.add('active');
      });
    });
    // Re-init modals
    document.querySelectorAll('.modal-backdrop').forEach(m => {
      m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
    });
    document.querySelectorAll('.modal-close').forEach(btn => {
      btn.addEventListener('click', () => btn.closest('.modal-backdrop')?.classList.remove('open'));
    });
    // Re-init upload zones
    document.querySelectorAll('.upload-zone').forEach(zone => {
      zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
      zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
      zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('drag-over');
        const input = zone.querySelector('input[type=file]');
        if (input) { input.files = e.dataTransfer.files; input.dispatchEvent(new Event('change')); }
      });
      zone.addEventListener('click', e => {
        if (e.target.tagName !== 'INPUT') zone.querySelector('input[type=file]')?.click();
      });
    });
    // Re-init confirm intercept untuk form-form di halaman baru
    if (window._interceptConfirmForms) window._interceptConfirmForms();
    // Re-run inline scripts dari halaman yang dimuat.
    // Menggunakan new Function() agar berjalan di scope global (window),
    // bukan scope closure seperti eval() — sehingga variabel & fungsi
    // yang didefinisikan di dalam script bisa diakses oleh event handler.
    document.querySelectorAll('.page-content script').forEach(s => {
      try {
        // eslint-disable-next-line no-new-func
        new Function(s.textContent)();
      } catch (e) {
        console.warn('[SPA] Script reinit warning:', e.message);
      }
    });
  }
})();
