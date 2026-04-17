<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Organisasi Siswa — MAN 1 Bangka</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>

  <?php include 'navbar.php'; ?>

  <section class="page-hero">
    <div class="breadcrumb">
      <a href="../index.html">Beranda</a> <i class="fas fa-chevron-right"></i>
      <span style="color: var(--gold)">Organisasi Siswa</span>
    </div>
    <h1><i class="fas fa-users"></i> Organisasi Siswa</h1>
    <p>
      Informasi lengkap organisasi siswa MAN 1 Bangka — visi misi, struktur pengurus,
      dan program kerja.
    </p>
  </section>

  <section style="padding: 3rem clamp(1rem, 5vw, 4rem); max-width: 1200px; margin: 0 auto;">

    <!-- Loading state -->
    <div id="org-loading" style="text-align:center; padding: 3rem 0; color: var(--gray-400);">
      <i class="fas fa-spinner fa-spin" style="font-size:2rem; margin-bottom:1rem; display:block;"></i>
      Memuat data organisasi...
    </div>

    <!-- Empty state -->
    <div id="org-empty" style="display:none; text-align:center; padding: 3rem 0; color: var(--gray-400);">
      <i class="fas fa-users" style="font-size:3rem; margin-bottom:1rem; display:block; opacity:.3;"></i>
      <p>Belum ada organisasi yang terdaftar.</p>
    </div>

    <!-- Semua organisasi akan di-render di sini -->
    <div id="org-list"></div>

  </section>

  <?php include 'footer.php'; ?>
  <script src="../assets/js/main.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      loadSemuaOrganisasi();
    });

    function esc(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    async function loadSemuaOrganisasi() {
      const loading = document.getElementById('org-loading');
      const empty   = document.getElementById('org-empty');
      const list    = document.getElementById('org-list');

      try {
        const res = await apiGet('organisasi', 'list');

        loading.style.display = 'none';

        if (res.status !== 'success' || !res.data || !res.data.length) {
          empty.style.display = 'block';
          return;
        }

        const icons = ['fa-star','fa-users','fa-flag','fa-book-open','fa-music',
                       'fa-futbol','fa-palette','fa-hand-holding-heart','fa-mosque','fa-shield-alt'];

        res.data.forEach((org, idx) => {
          const section = document.createElement('div');
          section.style.cssText = 'margin-bottom: 3rem;';

          // --- Visi ---
          const visiHtml = org.visi
            ? `<div class="form-card" style="padding:1.25rem; margin-bottom:1rem;">
                <h4 style="color:var(--gold-dark);font-size:.9rem;margin-bottom:.75rem;">
                  <i class="fas fa-eye"></i> Visi
                </h4>
                <p style="font-size:.88rem;">${esc(org.visi)}</p>
               </div>`
            : '<p style="color:var(--gray-400);font-size:.88rem;padding:1rem 0;">Visi belum diisi.</p>';

          // --- Misi ---
          const misiItems = org.misi
            ? org.misi.split(';').map(m => m.trim()).filter(Boolean)
                .map(m => `<li>${esc(m)}</li>`).join('')
            : '';
          const misiHtml = misiItems
            ? `<div class="form-card" style="padding:1.25rem;">
                <h4 style="color:var(--gold-dark);font-size:.9rem;margin-bottom:.75rem;">
                  <i class="fas fa-bullseye"></i> Misi
                </h4>
                <ul style="font-size:.88rem;padding-left:1.2rem;display:flex;flex-direction:column;gap:6px;">
                  ${misiItems}
                </ul>
               </div>`
            : '<p style="color:var(--gray-400);font-size:.88rem;padding:.5rem 0;">Misi belum diisi.</p>';

          // --- Gambar logo ---
          const gambarHtml = org.gambar
            ? `<img src="../php/uploads/${esc(org.gambar)}" alt="${esc(org.nama)}"
                 style="width:80px;height:80px;object-fit:contain;border-radius:12px;
                        background:var(--cream);padding:8px;border:1px solid var(--gray-200);
                        margin-bottom:1rem; flex-shrink:0;" />`
            : `<div style="width:80px;height:80px;border-radius:12px;background:var(--green-dark);
                           display:flex;align-items:center;justify-content:center;margin-bottom:1rem;flex-shrink:0;">
                 <i class="fas ${icons[idx % icons.length]}" style="color:var(--gold);font-size:2rem;"></i>
               </div>`;

          // --- Anggota ---
          let anggotaHtml = '';
          if (org.anggota && org.anggota.length) {
            const memberIcons = ['fa-star','fa-user-friends','fa-file-alt','fa-wallet','fa-user'];
            anggotaHtml = org.anggota.map((a, i) => `
              <div class="kontak-card" style="padding:1rem;">
                <div class="kontak-avatar">
                  <i class="fas ${memberIcons[i % memberIcons.length]}"></i>
                </div>
                <div class="kontak-info">
                  <h4>${esc(a.nama)}</h4>
                  <div class="jabatan">${esc(a.jabatan)}</div>
                  ${a.kelas ? `<div class="contact-row"><i class="fas fa-graduation-cap"></i>${esc(a.kelas)}</div>` : ''}
                </div>
              </div>`).join('');
          } else {
            anggotaHtml = '<p style="color:var(--gray-400);font-size:.85rem;text-align:center;padding:1rem 0;">Belum ada anggota terdaftar.</p>';
          }

          // --- Program Kerja ---
          let prokerHtml = '';
          if (org.program_kerja && org.program_kerja.length) {
            prokerHtml = org.program_kerja.map(p => `
              <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem 0;border-bottom:1px solid var(--gray-100);">
                <span class="badge badge-${p.status === 'selesai' ? 'green' : p.status === 'berjalan' ? 'gold' : 'gray'}">
                  ${esc(p.status)}
                </span>
                <div>
                  <div style="font-weight:600;font-size:.9rem;">${esc(p.nama_program)}</div>
                  <div style="font-size:.78rem;color:var(--gray-500);">Semester ${esc(p.semester)}</div>
                </div>
              </div>`).join('');
          } else {
            prokerHtml = '<p style="color:var(--gray-400);font-size:.85rem;text-align:center;padding:1rem 0;">Belum ada program kerja.</p>';
          }

          // --- Tab ID unik per organisasi ---
          const tabVisi    = `tab-${idx}-visi`;
          const tabAnggota = `tab-${idx}-anggota`;
          const tabProker  = `tab-${idx}-proker`;

          section.innerHTML = `
            <div style="display:flex;align-items:flex-start;gap:1.25rem;margin-bottom:1.5rem;">
              ${gambarHtml}
              <div>
                <h2 style="font-size:1.4rem;color:var(--green-dark);margin-bottom:.25rem;">${esc(org.nama)}</h2>
                ${org.deskripsi ? `<p style="color:var(--gray-500);font-size:.9rem;">${esc(org.deskripsi)}</p>` : ''}
              </div>
            </div>

            <div class="tabs">
              <div class="tabs__nav">
                <button class="tab-btn active" data-tab="${tabVisi}">📄 Visi &amp; Misi</button>
                <button class="tab-btn" data-tab="${tabAnggota}">👥 Pengurus</button>
                <button class="tab-btn" data-tab="${tabProker}">📋 Program Kerja</button>
              </div>

              <div id="${tabVisi}" class="tab-panel active">
                ${visiHtml}
                ${misiHtml}
              </div>

              <div id="${tabAnggota}" class="tab-panel">
                <div style="display:flex;flex-direction:column;gap:1rem;margin-top:.5rem;">
                  ${anggotaHtml}
                </div>
              </div>

              <div id="${tabProker}" class="tab-panel">
                <div style="margin-top:.5rem;">
                  ${prokerHtml}
                </div>
              </div>
            </div>

            ${idx < res.data.length - 1 ? '<hr style="margin-top:2.5rem;border:none;border-top:2px solid var(--gray-100);" />' : ''}
          `;

          list.appendChild(section);
        });

        // Re-init tabs karena elemen dibuat secara dinamis
        if (typeof initTabs === 'function') initTabs();
        if (typeof initReveal === 'function') initReveal();

      } catch (e) {
        loading.style.display = 'none';
        list.innerHTML = '<p style="text-align:center;color:var(--gray-400);padding:3rem 0;">Gagal memuat data organisasi.</p>';
      }
    }
  </script>
</body>

</html>
