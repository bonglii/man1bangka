<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arsip Kegiatan — MAN 1 Bangka</title>
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
      <span style="color: var(--gold)">Arsip Kegiatan</span>
    </div>
    <h1><i class="fas fa-archive"></i> Arsip Kegiatan</h1>
    <p>
      Laporan dan dokumentasi kegiatan siswa MAN 1 Bangka per semester dan
      tahun ajaran.
    </p>
  </section>

  <section style="padding: 3rem clamp(1rem, 5vw, 4rem)">
    <div style="max-width: 1000px; margin: 0 auto">
      <div class="section-header reveal">
        <div class="section-tag">🗂️ Arsip</div>
        <h2>Arsip & Laporan Kegiatan</h2>
        <p>
          Dokumentasi dan laporan kegiatan yang telah dilaksanakan per
          semester dan per tahun ajaran.
        </p>
        <div class="section-divider"></div>
      </div>

      <!-- Filter -->
      <div
        style="
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            align-items: center;
          ">
        <div style="display: flex; align-items: center; gap: 0.4rem">
          <label
            style="font-size: 0.82rem; font-weight: 600; color: var(--green)">Tahun Ajaran:</label>
          <select
            id="filter-ta"
            class="form-control"
            style="width: auto"
            onchange="applyArsipFilter()">
            <option value="">Semua</option>
            <option value="2024/2025">2024/2025</option>
            <option value="2023/2024">2023/2024</option>
            <option value="2022/2023">2022/2023</option>
          </select>
        </div>
        <div style="display: flex; align-items: center; gap: 0.4rem">
          <label
            style="font-size: 0.82rem; font-weight: 600; color: var(--green)">Semester:</label>
          <select
            id="filter-sem"
            class="form-control"
            style="width: auto"
            onchange="applyArsipFilter()">
            <option value="">Semua</option>
            <option value="ganjil">Ganjil</option>
            <option value="genap">Genap</option>
          </select>
        </div>
      </div>

      <div id="arsip-list">
        <div class="loading">
          <div class="spinner"></div>
        </div>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>
  <script src="../assets/js/main.js"></script>
  <script>
    let allArsip = [];

    async function loadArsip() {
      const container = document.getElementById("arsip-list");
      container.innerHTML =
        '<div class="loading"><div class="spinner"></div></div>';
      const res = await apiGet("arsip", "list");
      allArsip = res.status === "success" ? res.data : [];
      renderArsip(allArsip);
    }

    function applyArsipFilter() {
      const ta = document.getElementById("filter-ta").value;
      const sem = document.getElementById("filter-sem").value;
      let data = allArsip;
      if (ta) data = data.filter((a) => a.tahun_ajaran === ta);
      if (sem) data = data.filter((a) => a.semester === sem);
      renderArsip(data);
    }

    function renderArsip(data) {
      const container = document.getElementById("arsip-list");
      if (!data.length) {
        container.innerHTML =
          '<div class="empty-state"><i class="fas fa-folder-open"></i><p>Tidak ada arsip untuk filter ini.</p></div>';
        return;
      }
      const semIcon = {
        ganjil: "📘",
        genap: "📗"
      };
      container.innerHTML = data
        .map(
          (a) => `
    <div class="agenda-item reveal" style="margin-bottom:.75rem;">
      <div class="agenda-date" style="background:var(--gold-dark);min-width:70px;text-align:center;flex-shrink:0;">
        <div class="day" style="font-size:.95rem;font-weight:800;">${(a.tahun_ajaran || "").split("/")[0] || "—"}</div>
        <div class="month" style="font-size:.65rem;">${semIcon[a.semester] || ""} ${a.semester || ""}</div>
      </div>
      <div class="agenda-info" style="flex:1;min-width:0;">
        <h4 style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${a.judul}</h4>
        <p style="font-size:.82rem;color:var(--gray-400,#666);overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">${a.deskripsi || ""}</p>
        <div class="agenda-meta" style="margin-top:.4rem;gap:.5rem;flex-wrap:wrap;">
          <span class="badge badge-green">${a.tahun_ajaran || ""}</span>
          ${a.kategori ? `<span class="badge badge-gold">${a.kategori}</span>` : ""}
          ${a.url_file ? `<a href="../${a.url_file}" target="_blank" class="btn btn-green btn-sm" style="margin-left:auto;"><i class="fas fa-download"></i> Unduh Laporan</a>` : '<span class="badge" style="background:#e0e0e0;color:#888;">Belum ada file</span>'}
        </div>
      </div>
    </div>
  `,
        )
        .join("");
      initReveal();
    }

    document.addEventListener("DOMContentLoaded", () => {
      // initNavbar() and initReveal() already called by main.js
      loadArsip();
    });
  </script>
</body>

</html>