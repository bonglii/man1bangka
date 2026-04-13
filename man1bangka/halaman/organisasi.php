<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Organisasi Siswa — MAN 1 Bangka</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
      Informasi lengkap OSIS MAN 1 Bangka — visi misi, struktur organisasi,
      dan program kerja.
    </p>
  </section>

  <section
    style="
        padding: 3rem clamp(1rem, 5vw, 4rem);
        max-width: 1200px;
        margin: 0 auto;
      ">
    <div class="tabs">
      <div class="tabs__nav">
        <button class="tab-btn active" data-tab="tab-osis">🏫 OSIS</button>
        <button class="tab-btn" data-tab="tab-struktur">👥 Struktur</button>
        <button class="tab-btn" data-tab="tab-proker">
          📋 Program Kerja
        </button>
      </div>
      <div id="tab-osis" class="tab-panel active">
        <div
          style="
              display: grid;
              grid-template-columns: 1fr 1fr;
              gap: 2rem;
              align-items: start;
            "
          class="org-layout">
          <div class="reveal">
            <h2 style="margin-bottom: 1rem">
              Organisasi Siswa Intra Sekolah
            </h2>
            <p style="margin-bottom: 1rem">
              OSIS MAN 1 Bangka adalah organisasi resmi yang menjadi wadah
              pengembangan kepemimpinan, kreativitas, dan karakter siswa.
            </p>
            <p style="margin-bottom: 1.5rem">
              Melalui berbagai program kerja yang inovatif, OSIS berperan
              aktif dalam menciptakan lingkungan sekolah yang kondusif,
              inspiratif, dan penuh semangat.
            </p>
            <div style="display: flex; flex-direction: column; gap: 1rem">
              <div class="form-card" style="padding: 1.25rem">
                <h4
                  style="
                      color: var(--gold-dark);
                      font-size: 0.9rem;
                      margin-bottom: 0.75rem;
                    ">
                  <i class="fas fa-eye"></i> Visi
                </h4>
                <p style="font-size: 0.88rem">
                  <span id="org-visi">Terwujudnya OSIS MAN 1 Bangka yang aktif, kreatif,
                    berprestasi, dan berkarakter Islami dalam membangun generasi
                    emas bangsa.</span>
                </p>
              </div>
              <div class="form-card" style="padding: 1.25rem">
                <h4
                  style="
                      color: var(--gold-dark);
                      font-size: 0.9rem;
                      margin-bottom: 0.75rem;
                    ">
                  <i class="fas fa-bullseye"></i> Misi
                </h4>
                <ul
                  style="
                      font-size: 0.88rem;
                      padding-left: 1.2rem;
                      display: flex;
                      flex-direction: column;
                      gap: 6px;
                    ">
                  <li>
                    Mengembangkan potensi siswa melalui kegiatan yang
                    terstruktur dan bermakna
                  </li>
                  <li>
                    Membangun komunikasi yang baik antara siswa dan pihak
                    sekolah
                  </li>
                  <li>
                    Menumbuhkan semangat berprestasi di bidang akademik dan
                    non-akademik
                  </li>
                  <li>
                    Memperkuat nilai-nilai keislaman dalam setiap kegiatan
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="reveal">
            <div class="form-card">
              <h3
                style="
                    text-align: center;
                    margin-bottom: 1.5rem;
                    font-size: 1.1rem;
                  ">
                Pengurus OSIS 2024/2025
              </h3>
              <div style="display: flex; flex-direction: column; gap: 1rem">
                <div class="kontak-card" style="padding: 1rem">
                  <div class="kontak-avatar"><i class="fas fa-star"></i></div>
                  <div class="kontak-info">
                    <h4>Muhammad Farhan</h4>
                    <div class="jabatan">Ketua OSIS</div>
                    <div class="contact-row">
                      <i class="fas fa-graduation-cap"></i>XII IPS 1
                    </div>
                  </div>
                </div>
                <div class="kontak-card" style="padding: 1rem">
                  <div class="kontak-avatar">
                    <i class="fas fa-user-friends"></i>
                  </div>
                  <div class="kontak-info">
                    <h4>Aisyah Putri Ramadhani</h4>
                    <div class="jabatan">Wakil Ketua OSIS</div>
                    <div class="contact-row">
                      <i class="fas fa-graduation-cap"></i>XI IPA 1
                    </div>
                  </div>
                </div>
                <div class="kontak-card" style="padding: 1rem">
                  <div class="kontak-avatar">
                    <i class="fas fa-file-alt"></i>
                  </div>
                  <div class="kontak-info">
                    <h4>Rizky Aditya Pratama</h4>
                    <div class="jabatan">Sekretaris</div>
                    <div class="contact-row">
                      <i class="fas fa-graduation-cap"></i>XI IPA 3
                    </div>
                  </div>
                </div>
                <div class="kontak-card" style="padding: 1rem">
                  <div class="kontak-avatar">
                    <i class="fas fa-wallet"></i>
                  </div>
                  <div class="kontak-info">
                    <h4>Dinda Permata Sari</h4>
                    <div class="jabatan">Bendahara</div>
                    <div class="contact-row">
                      <i class="fas fa-graduation-cap"></i>XI IPA 2
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="tab-struktur" class="tab-panel">
        <div class="section-header" style="margin-bottom: 2rem">
          <h2>Struktur Organisasi OSIS</h2>
          <p>MAN 1 Bangka Periode 2024/2025</p>
        </div>
        <div
          style="
              background: var(--white);
              border-radius: var(--radius-lg);
              padding: 2rem;
              box-shadow: var(--shadow-sm);
              overflow-x: auto;
            ">
          <div style="text-align: center; min-width: 600px">
            <div
              style="
                  display: inline-block;
                  background: var(--green-dark);
                  color: var(--gold);
                  padding: 14px 28px;
                  border-radius: 10px;
                  font-weight: 700;
                  font-size: 0.95rem;
                  margin-bottom: 2rem;
                ">
              PEMBINA OSIS<br /><span style="font-size: 0.78rem; opacity: 0.8">Bpk. Drs. H. Syamsul Bahri</span>
            </div>
            <div
              style="
                  border-left: 2px solid var(--gold);
                  height: 30px;
                  margin: 0 auto;
                  width: 2px;
                "></div>
            <div
              style="
                  display: inline-block;
                  background: var(--gold);
                  color: var(--green-dark);
                  padding: 14px 28px;
                  border-radius: 10px;
                  font-weight: 700;
                  font-size: 0.95rem;
                  margin-bottom: 2rem;
                ">
              KETUA OSIS<br /><span style="font-size: 0.78rem">Muhammad Farhan</span>
            </div>
            <div
              style="
                  display: flex;
                  justify-content: center;
                  gap: 3rem;
                  flex-wrap: wrap;
                  margin-top: 1rem;
                ">
              <div style="text-align: center">
                <div
                  style="
                      border-left: 2px solid var(--gold);
                      height: 20px;
                      margin: 0 auto;
                      width: 2px;
                    "></div>
                <div
                  style="
                      background: var(--gray-100);
                      border: 1px solid var(--gray-200);
                      padding: 10px 20px;
                      border-radius: 8px;
                      font-size: 0.85rem;
                      font-weight: 600;
                    ">
                  Wakil Ketua
                </div>
              </div>
              <div style="text-align: center">
                <div
                  style="
                      border-left: 2px solid var(--gold);
                      height: 20px;
                      margin: 0 auto;
                      width: 2px;
                    "></div>
                <div
                  style="
                      background: var(--gray-100);
                      border: 1px solid var(--gray-200);
                      padding: 10px 20px;
                      border-radius: 8px;
                      font-size: 0.85rem;
                      font-weight: 600;
                    ">
                  Sekretaris
                </div>
              </div>
              <div style="text-align: center">
                <div
                  style="
                      border-left: 2px solid var(--gold);
                      height: 20px;
                      margin: 0 auto;
                      width: 2px;
                    "></div>
                <div
                  style="
                      background: var(--gray-100);
                      border: 1px solid var(--gray-200);
                      padding: 10px 20px;
                      border-radius: 8px;
                      font-size: 0.85rem;
                      font-weight: 600;
                    ">
                  Bendahara
                </div>
              </div>
            </div>
            <div
              style="
                  margin-top: 2rem;
                  display: flex;
                  justify-content: center;
                  gap: 1rem;
                  flex-wrap: wrap;
                ">
              <div
                style="
                    background: var(--cream);
                    border: 1px solid var(--gray-200);
                    padding: 10px 16px;
                    border-radius: 8px;
                    font-size: 0.82rem;
                    font-weight: 600;
                    text-align: center;
                  ">
                Bid. Ketaqwaan
              </div>
              <div
                style="
                    background: var(--cream);
                    border: 1px solid var(--gray-200);
                    padding: 10px 16px;
                    border-radius: 8px;
                    font-size: 0.82rem;
                    font-weight: 600;
                    text-align: center;
                  ">
                Bid. Akademik
              </div>
              <div
                style="
                    background: var(--cream);
                    border: 1px solid var(--gray-200);
                    padding: 10px 16px;
                    border-radius: 8px;
                    font-size: 0.82rem;
                    font-weight: 600;
                    text-align: center;
                  ">
                Bid. Olahraga
              </div>
              <div
                style="
                    background: var(--cream);
                    border: 1px solid var(--gray-200);
                    padding: 10px 16px;
                    border-radius: 8px;
                    font-size: 0.82rem;
                    font-weight: 600;
                    text-align: center;
                  ">
                Bid. Seni & Budaya
              </div>
              <div
                style="
                    background: var(--cream);
                    border: 1px solid var(--gray-200);
                    padding: 10px 16px;
                    border-radius: 8px;
                    font-size: 0.82rem;
                    font-weight: 600;
                    text-align: center;
                  ">
                Bid. Kewirausahaan
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="tab-proker" class="tab-panel">
        <!-- Data dinamis dari API ditambahkan di sini oleh loadOrganisasi() -->
        <div id="org-proker-dynamic" style="margin-bottom:1.5rem;"></div>
        <div class="section-header" style="margin-bottom: 2rem">
          <h2>Program Kerja OSIS 2024/2025</h2>
        </div>
        <div
          style="
              display: grid;
              grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
              gap: 1.25rem;
            ">
          <div class="card reveal">
            <div class="card__body">
              <span class="card__tag tag-kegiatan">Semester Ganjil</span>
              <h3 class="card__title">Masa Orientasi Siswa Baru</h3>
              <p class="card__text">
                Program pengenalan lingkungan sekolah bagi siswa baru kelas X.
              </p>
              <div class="card__meta">
                <span><i class="far fa-calendar-alt"></i> Juli 2024</span><span class="badge badge-green">Selesai ✓</span>
              </div>
            </div>
          </div>
          <div class="card reveal">
            <div class="card__body">
              <span class="card__tag tag-kegiatan">Semester Ganjil</span>
              <h3 class="card__title">Peringatan HUT RI</h3>
              <p class="card__text">
                Serangkaian lomba dan kegiatan dalam rangka memperingati Hari
                Kemerdekaan RI.
              </p>
              <div class="card__meta">
                <span><i class="far fa-calendar-alt"></i> Agustus 2024</span><span class="badge badge-green">Selesai ✓</span>
              </div>
            </div>
          </div>
          <div class="card reveal">
            <div class="card__body">
              <span class="card__tag tag-lomba">Semester Genap</span>
              <h3 class="card__title">Olimpiade Internal Sekolah</h3>
              <p class="card__text">
                Kompetisi akademik dan non-akademik antar kelas untuk melatih
                semangat kompetisi.
              </p>
              <div class="card__meta">
                <span><i class="far fa-calendar-alt"></i> Maret 2025</span><span class="badge badge-blue">Berjalan</span>
              </div>
            </div>
          </div>
          <div class="card reveal">
            <div class="card__body">
              <span class="card__tag tag-kegiatan">Semester Genap</span>
              <h3 class="card__title">Pentas Seni Akhir Tahun</h3>
              <p class="card__text">
                Penampilan seni tari, musik, dan drama dari siswa-siswi MAN 1
                Bangka.
              </p>
              <div class="card__meta">
                <span><i class="far fa-calendar-alt"></i> Juni 2025</span><span class="badge badge-gold">Rencana</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php include 'footer.php'; ?>
  <script src="../assets/js/main.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // initTabs() and initReveal() already called by main.js
      loadOrganisasi();
    });

    // Load data organisasi dari API — perbarui elemen dinamis
    async function loadOrganisasi() {
      try {
        const res = await apiGet('organisasi', 'list');
        if (res.status !== 'success' || !res.data.length) return;

        const org = res.data[0]; // OSIS adalah organisasi utama (id=1)

        // Update visi jika tersedia dari DB
        const visiEl = document.getElementById('org-visi');
        if (visiEl && org.visi) visiEl.textContent = org.visi;

        // Update misi jika tersedia dari DB
        const misiEl = document.getElementById('org-misi');
        if (misiEl && org.misi) {
          misiEl.innerHTML = org.misi.split(';')
            .map(m => m.trim()).filter(Boolean)
            .map(m => `<li>${esc(m)}</li>`).join('');
        }

        // Update anggota organisasi secara dinamis
        const anggotaEl = document.getElementById('org-anggota-dynamic');
        if (anggotaEl && org.anggota && org.anggota.length) {
          anggotaEl.innerHTML = org.anggota.map(a => `
              <div class="card reveal" style="padding:1rem;">
                <div style="font-weight:700;color:var(--green-dark);">${esc(a.nama)}</div>
                <div style="font-size:.85rem;color:var(--gold-dark);margin:.2rem 0;">${esc(a.jabatan)}</div>
                ${a.kelas ? `<div style="font-size:.8rem;color:var(--gray-500);">${esc(a.kelas)}</div>` : ''}
              </div>`).join('');
        }

        // Update program kerja
        const prokerEl = document.getElementById('org-proker-dynamic');
        if (prokerEl && org.program_kerja && org.program_kerja.length) {
          prokerEl.innerHTML = org.program_kerja.map(p => `
              <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem 0;border-bottom:1px solid var(--gray-100);">
                <span class="badge badge-${p.status === 'selesai' ? 'green' : p.status === 'berjalan' ? 'gold' : 'gray'}">${esc(p.status)}</span>
                <div>
                  <div style="font-weight:600;font-size:.9rem;">${esc(p.nama_program)}</div>
                  <div style="font-size:.78rem;color:var(--gray-500);">Semester ${esc(p.semester)}</div>
                </div>
              </div>`).join('');
        }
      } catch (e) {
        // Fallback ke konten statik — tidak perlu tindakan
      }
    }
  </script>
</body>

</html>