<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Agenda Kegiatan — MAN 1 Bangka</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    /* ---- AGENDA PAGE STYLES ---- */
    .agenda-wrap {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2.5rem clamp(1rem, 5vw, 3rem);
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 2rem;
      align-items: start;
    }

    @media (max-width: 900px) {
      .agenda-wrap {
        grid-template-columns: 1fr;
      }
    }

    /* Month header */
    .month-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      gap: 0.75rem;
    }

    .month-title {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--green-dark);
      letter-spacing: -0.3px;
    }

    .month-nav {
      display: flex;
      gap: 0.4rem;
    }

    .month-nav button {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: var(--green-dark);
      color: #fff;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      transition: 0.2s;
    }

    .month-nav button:hover {
      background: var(--green-mid);
      transform: scale(1.08);
    }

    /* Agenda cards */
    .agenda-card {
      background: #fff;
      border-radius: 14px;
      padding: 0;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(0, 0, 0, 0.06);
      overflow: hidden;
      margin-bottom: 1rem;
      display: flex;
      transition: 0.2s;
      animation: fadeInUp 0.35s ease both;
    }

    .agenda-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    /* Color accent bar */
    .agenda-accent {
      width: 6px;
      flex-shrink: 0;
      border-radius: 0;
      background: var(--green-mid);
    }

    /* Date box */
    .agenda-datebox {
      flex-shrink: 0;
      width: 72px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 1.2rem 0.5rem;
      background: var(--cream);
      border-right: 1px solid rgba(0, 0, 0, 0.06);
    }

    .agenda-datebox .ad-day {
      font-size: 1.9rem;
      font-weight: 900;
      line-height: 1;
      color: var(--green-dark);
      font-family: var(--font-display, serif);
    }

    .agenda-datebox .ad-mon {
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--green-mid);
      letter-spacing: 1px;
      margin-top: 2px;
    }

    .agenda-datebox .ad-year {
      font-size: 0.6rem;
      color: #999;
      margin-top: 1px;
    }

    /* Content */
    .agenda-content {
      flex: 1;
      padding: 1.1rem 1.25rem;
      min-width: 0;
    }

    .agenda-content h4 {
      font-size: 1rem;
      font-weight: 700;
      color: #1a1a2e;
      margin-bottom: 0.35rem;
      line-height: 1.35;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }

    .agenda-content .agenda-desc {
      font-size: 0.82rem;
      color: #666;
      margin-bottom: 0.65rem;
      line-height: 1.55;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }

    .agenda-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.35rem;
      align-items: center;
    }

    .agenda-tag {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      font-size: 0.72rem;
      font-weight: 600;
      padding: 0.22rem 0.6rem;
      border-radius: 99px;
      white-space: nowrap;
    }

    .tag-kategori {
      background: rgba(11, 61, 46, 0.1);
      color: var(--green-mid);
    }

    .tag-lokasi {
      background: rgba(37, 99, 235, 0.1);
      color: #2563eb;
    }

    .tag-selesai {
      background: #dcfce7;
      color: #166534;
    }

    .tag-tanggal2 {
      background: rgba(201, 168, 76, 0.12);
      color: var(--gold-dark);
    }

    /* Status badge right side */
    .agenda-status {
      flex-shrink: 0;
      padding: 1rem 0.85rem;
      display: flex;
      align-items: flex-start;
      justify-content: flex-end;
    }

    .status-pill {
      font-size: 0.68rem;
      font-weight: 700;
      padding: 0.2rem 0.6rem;
      border-radius: 99px;
      white-space: nowrap;
    }

    .status-selesai {
      background: #dcfce7;
      color: #166534;
    }

    .status-berjalan {
      background: #dbeafe;
      color: #1e40af;
    }

    .status-akan {
      background: #fef9c3;
      color: #92400e;
    }

    /* Sidebar info */
    .agenda-sidebar .info-card {
      background: #fff;
      border-radius: 14px;
      padding: 1.4rem;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
      position: sticky;
      top: 90px;
      border: 1px solid rgba(0, 0, 0, 0.06);
    }

    .info-card h3 {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--green-dark);
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid #f0f0f0;
    }

    .info-contact {
      background: var(--cream);
      border-radius: 10px;
      padding: 1rem;
      margin-bottom: 1.25rem;
    }

    .info-contact .ic-name {
      font-weight: 700;
      font-size: 0.88rem;
      color: var(--green-dark);
    }

    .info-contact .ic-role {
      font-size: 0.74rem;
      color: var(--gold-dark);
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .info-contact .ic-row {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.78rem;
      color: #555;
      margin-top: 0.3rem;
    }

    .info-contact .ic-row i {
      color: var(--green-mid);
      width: 14px;
    }

    .legend-list {
      display: flex;
      flex-direction: column;
      gap: 0.55rem;
      margin-top: 0.75rem;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      font-size: 0.8rem;
      font-weight: 500;
      color: #444;
    }

    .legend-dot {
      width: 12px;
      height: 12px;
      border-radius: 3px;
      flex-shrink: 0;
    }

    /* Stats row */
    .agenda-stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.6rem;
      margin-bottom: 1.25rem;
    }

    .astat {
      background: var(--cream);
      border-radius: 10px;
      padding: 0.75rem;
      text-align: center;
    }

    .astat .as-num {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--green-dark);
    }

    .astat .as-lbl {
      font-size: 0.65rem;
      color: #888;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Empty */
    .agenda-empty {
      text-align: center;
      padding: 3rem 1rem;
      color: #aaa;
    }

    .agenda-empty i {
      font-size: 3rem;
      margin-bottom: 1rem;
      display: block;
    }

    .agenda-empty p {
      font-size: 0.9rem;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(16px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>

<body>

  <?php include 'navbar.php'; ?>


  <section class="page-hero">
    <div class="breadcrumb">
      <a href="../index.html">Beranda</a> <i class="fas fa-chevron-right"></i>
      <span style="color: var(--gold)">Agenda Kegiatan</span>
    </div>
    <h1><i class="far fa-calendar-alt"></i> Agenda Kegiatan</h1>
    <p>
      Jadwal lengkap kegiatan siswa MAN 1 Bangka — lomba, ekskul, seminar, dan
      kegiatan kelas.
    </p>
  </section>

  <div class="agenda-wrap">
    <!-- LEFT: Agenda List -->
    <div>
      <!-- Month nav -->
      <div class="month-header">
        <h2 class="month-title" id="agenda-month-title">Agenda Bulan Ini</h2>
        <div class="month-nav">
          <button onclick="prevMonth()" title="Bulan sebelumnya">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button onclick="nextMonth()" title="Bulan berikutnya">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="agenda-stats" id="agenda-stats" style="display: none">
        <div class="astat">
          <div class="as-num" id="stat-total">0</div>
          <div class="as-lbl">Total</div>
        </div>
        <div class="astat">
          <div class="as-num" id="stat-akan">0</div>
          <div class="as-lbl">Akan Datang</div>
        </div>
        <div class="astat">
          <div class="as-num" id="stat-selesai">0</div>
          <div class="as-lbl">Selesai</div>
        </div>
      </div>

      <!-- List -->
      <div id="agenda-list">
        <div class="loading">
          <div class="spinner"></div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Sidebar -->
    <div class="agenda-sidebar">
      <div class="info-card">
        <h3>
          <i class="fas fa-info-circle" style="color: var(--gold-dark)"></i>
          Info Agenda
        </h3>
        <p
          style="
              font-size: 0.8rem;
              color: #666;
              margin-bottom: 0.85rem;
              line-height: 1.6;
            ">
          Jadwal kegiatan ditetapkan oleh pihak sekolah. Untuk informasi lebih
          lanjut, hubungi:
        </p>
        <div class="info-contact">
          <div class="ic-name">Waka Kesiswaan</div>
          <div class="ic-role">Koordinator Kegiatan Siswa</div>
          <div class="ic-row">
            <i class="fas fa-phone"></i>(0717) 123-4567
          </div>
          <div class="ic-row">
            <i class="fas fa-envelope"></i>kesiswaan@man1bangka.sch.id
          </div>
        </div>

        <h4
          style="
              font-size: 0.82rem;
              font-weight: 700;
              color: var(--green-dark);
              margin-bottom: 0.5rem;
            ">
          <i
            class="fas fa-palette"
            style="margin-right: 0.35rem; color: var(--gold-dark)"></i>Kategori Warna
        </h4>
        <div class="legend-list">
          <div class="legend-item">
            <div class="legend-dot" style="background: #1a6b3c"></div>
            Lomba &amp; Kompetisi
          </div>
          <div class="legend-item">
            <div class="legend-dot" style="background: #c9a84c"></div>
            Ekstrakurikuler
          </div>
          <div class="legend-item">
            <div class="legend-dot" style="background: #2563eb"></div>
            Organisasi / OSIS
          </div>
          <div class="legend-item">
            <div class="legend-dot" style="background: #7c3aed"></div>
            Seminar / Workshop
          </div>
          <div class="legend-item">
            <div class="legend-dot" style="background: #0d9488"></div>
            Keagamaan
          </div>
          <div class="legend-item">
            <div class="legend-dot" style="background: #6b7280"></div>
            Kegiatan Umum
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="logo-wrap">
          <div class="logo-icon">M1B</div>
          <div class="logo-text">
            <span>MAN 1 Bangka</span><span>WEBSITE KEGIATAN SISWA</span>
          </div>
        </div>
        <p>Portal resmi kegiatan siswa Madrasah Aliyah Negeri 1 Bangka.</p>
        <div class="footer-socials">
          <a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-youtube"></i></a><a href="#"><i class="fab fa-facebook-f"></i></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Menu Utama</h4>
        <ul>
          <li>
            <a href="../index.html"><i class="fas fa-chevron-right"></i> Beranda</a>
          </li>
          <li>
            <a href="pengumuman.php"><i class="fas fa-chevron-right"></i> Pengumuman</a>
          </li>
          <li>
            <a href="agenda.php"><i class="fas fa-chevron-right"></i> Agenda</a>
          </li>
          <li>
            <a href="ekstrakurikuler.php"><i class="fas fa-chevron-right"></i> Ekstrakurikuler</a>
          </li>
          <li>
            <a href="prestasi.php"><i class="fas fa-chevron-right"></i> Prestasi</a>
          </li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Informasi</h4>
        <ul>
          <li>
            <a href="dokumentasi.php"><i class="fas fa-chevron-right"></i> Dokumentasi</a>
          </li>
          <li>
            <a href="arsip.php"><i class="fas fa-chevron-right"></i> Arsip</a>
          </li>
          <li>
            <a href="karya-siswa.php"><i class="fas fa-chevron-right"></i> Karya Siswa</a>
          </li>
          <li>
            <a href="testimoni.php"><i class="fas fa-chevron-right"></i> Testimoni</a>
          </li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Kontak</h4>
        <ul>
          <li>
            <a href="#"><i class="fas fa-map-marker-alt"></i> Jl. Raya Bangka, Babel</a>
          </li>
          <li>
            <a href="tel:07171234567"><i class="fas fa-phone"></i> (0717) 123-4567</a>
          </li>
          <li>
            <a href="mailto:info@man1bangka.sch.id"><i class="fas fa-envelope"></i> info@man1bangka.sch.id</a>
          </li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <div>© 2026 <span>Man1Bangka</span>. All rights reserved.</div>
      <div>Dikembangkan oleh <span>Estefania</span></div>
    </div>
  </footer>
  <button id="scrollTop" aria-label="Scroll ke atas">
    <i class="fas fa-arrow-up"></i>
  </button>
  <script src="../assets/js/main.js"></script>
  <script>
    const MONTHS = [
      "Januari",
      "Februari",
      "Maret",
      "April",
      "Mei",
      "Juni",
      "Juli",
      "Agustus",
      "September",
      "Oktober",
      "November",
      "Desember",
    ];
    const MONTHS_SHORT = [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "Mei",
      "Jun",
      "Jul",
      "Agu",
      "Sep",
      "Okt",
      "Nov",
      "Des",
    ];
    const KATEG_COLOR = {
      lomba: "#1a6b3c",
      ekskul: "#C9A84C",
      organisasi: "#2563eb",
      seminar: "#7c3aed",
      keagamaan: "#0d9488",
      umum: "#6b7280",
      kelas: "#ea580c",
      lainnya: "#9ca3af",
    };
    const KATEG_ICON = {
      lomba: "fa-trophy",
      ekskul: "fa-star",
      organisasi: "fa-users",
      seminar: "fa-chalkboard-teacher",
      keagamaan: "fa-mosque",
      umum: "fa-calendar-check",
      kelas: "fa-book",
      lainnya: "fa-calendar",
    };

    let curMonth = new Date().getMonth() + 1;
    let curYear = new Date().getFullYear();

    function getStatus(a) {
      if (a.is_selesai == 1) return "selesai";
      const now = new Date();
      const tglMulai = new Date(a.tanggal_mulai);
      const tglSelesai = a.tanggal_selesai ?
        new Date(a.tanggal_selesai) :
        tglMulai;
      if (now >= tglMulai && now <= tglSelesai) return "berjalan";
      if (now > tglSelesai) return "selesai";
      return "akan";
    }

    function statusLabel(s) {
      return ({
        selesai: "✓ Selesai",
        berjalan: "⚡ Berlangsung",
        akan: "📅 Akan Datang",
      } [s] || "");
    }

    async function loadAgenda() {
      const container = document.getElementById("agenda-list");
      document.getElementById("agenda-month-title").textContent =
        `Agenda ${MONTHS[curMonth - 1]} ${curYear}`;
      container.innerHTML =
        '<div class="loading"><div class="spinner"></div></div>';
      document.getElementById("agenda-stats").style.display = "none";

      const res = await apiGet("agenda", "list", {
        bulan: curMonth,
        tahun: curYear,
      });

      if (res.status === "success" && res.data.length) {
        const data = res.data;
        const total = data.length;
        const akan = data.filter((a) => getStatus(a) === "akan").length;
        const selesai = data.filter((a) => getStatus(a) === "selesai").length;

        // Stats
        document.getElementById("stat-total").textContent = total;
        document.getElementById("stat-akan").textContent = akan;
        document.getElementById("stat-selesai").textContent = selesai;
        document.getElementById("agenda-stats").style.display = "grid";

        container.innerHTML = data
          .map((a, idx) => {
            const d = new Date(a.tanggal_mulai);
            const day = d.getDate();
            const mon = MONTHS_SHORT[d.getMonth()];
            const year = d.getFullYear();
            const warna = a.warna || KATEG_COLOR[a.kategori] || "#1a6b3c";
            const icon = KATEG_ICON[a.kategori] || "fa-calendar";
            const status = getStatus(a);

            // Date range
            let dateRange = "";
            if (a.tanggal_selesai && a.tanggal_selesai !== a.tanggal_mulai) {
              const d2 = new Date(a.tanggal_selesai);
              dateRange = `<span class="agenda-tag tag-tanggal2">
          <i class="fas fa-calendar-check" style="font-size:.6rem;"></i>
          Hingga ${d2.getDate()} ${MONTHS_SHORT[d2.getMonth()]} ${d2.getFullYear()}
        </span>`;
            }

            return `<div class="agenda-card" style="animation-delay:${idx * 0.05}s">
        <div class="agenda-accent" style="background:${warna};"></div>
        <div class="agenda-datebox">
          <div class="ad-day">${day}</div>
          <div class="ad-mon">${mon}</div>
          <div class="ad-year">${year}</div>
        </div>
        <div class="agenda-content">
          <h4>${a.judul}</h4>
          ${a.deskripsi ? `<div class="agenda-desc">${a.deskripsi}</div>` : ""}
          <div class="agenda-tags">
            <span class="agenda-tag tag-kategori">
              <i class="fas ${icon}" style="font-size:.65rem;"></i>${a.kategori}
            </span>
            ${
              a.lokasi
                ? `<span class="agenda-tag tag-lokasi">
              <i class="fas fa-map-marker-alt" style="font-size:.65rem;"></i>${a.lokasi}
            </span>`
                : ""
            }
            ${dateRange}
            ${status === "selesai" ? '<span class="agenda-tag tag-selesai"><i class="fas fa-check" style="font-size:.6rem;"></i>Selesai</span>' : ""}
          </div>
        </div>
        <div class="agenda-status">
          <span class="status-pill status-${status}">${statusLabel(status)}</span>
        </div>
      </div>`;
          })
          .join("");
      } else {
        container.innerHTML = `
      <div class="agenda-empty">
        <i class="far fa-calendar-times"></i>
        <p><strong>Tidak ada agenda pada ${MONTHS[curMonth - 1]} ${curYear}</strong></p>
        <p style="font-size:.82rem;margin-top:.35rem;color:#bbb;">Coba navigasi ke bulan lain menggunakan tombol di atas.</p>
      </div>`;
      }
    }

    function prevMonth() {
      curMonth--;
      if (curMonth < 1) {
        curMonth = 12;
        curYear--;
      }
      loadAgenda();
    }

    function nextMonth() {
      curMonth++;
      if (curMonth > 12) {
        curMonth = 1;
        curYear++;
      }
      loadAgenda();
    }

    document.addEventListener("DOMContentLoaded", () => {
      initNavbar();
      initReveal();
      loadAgenda();
    });
  </script>
</body>

</html>