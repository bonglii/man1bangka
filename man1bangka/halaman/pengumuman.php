<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pengumuman — MAN 1 Bangka</title>
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
      <span style="color: var(--gold)">Pengumuman</span>
    </div>
    <h1><i class="far fa-bell"></i> Pengumuman Kegiatan</h1>
    <p>
      Informasi terbaru seputar lomba, kegiatan sekolah, dan pendaftaran untuk
      siswa MAN 1 Bangka.
    </p>
  </section>

  <section style="padding: 3rem clamp(1rem, 5vw, 4rem)">
    <div
      style="
          display: flex;
          flex-wrap: wrap;
          gap: 1rem;
          align-items: center;
          justify-content: space-between;
          margin-bottom: 2rem;
        ">
      <div
        class="tabs__nav"
        style="
            background: var(--gray-100);
            padding: 6px;
            border-radius: 24px;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
          ">
        <button
          class="tab-btn active"
          data-filter="all"
          onclick="filterPengumuman('all')">
          Semua
        </button>
        <button
          class="tab-btn"
          data-filter="lomba"
          onclick="filterPengumuman('lomba')">
          🏆 Lomba
        </button>
        <button
          class="tab-btn"
          data-filter="kegiatan"
          onclick="filterPengumuman('kegiatan')">
          📅 Kegiatan
        </button>
        <button
          class="tab-btn"
          data-filter="pendaftaran"
          onclick="filterPengumuman('pendaftaran')">
          📝 Pendaftaran
        </button>
      </div>
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input
          type="text"
          id="search-pengumuman"
          placeholder="Cari pengumuman..." />
      </div>
    </div>
    <div class="cards-grid" id="pengumuman-container">
      <div class="loading">
        <div class="spinner"></div>
      </div>
    </div>
  </section>
  <div class="modal-overlay" id="modal-detail">
    <div class="modal">
      <div class="modal__header">
        <h3 id="modal-detail-title">Detail</h3>
        <button
          class="modal__close"
          onclick="
              document.getElementById('modal-detail').classList.remove('open')
            ">
          ×
        </button>
      </div>
      <div class="modal__body">
        <p id="modal-detail-body" style="line-height: 1.9"></p>
      </div>
    </div>
  </div>
  <?php include 'footer.php'; ?>
  <script src="../assets/js/main.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      loadPengumuman("pengumuman-container");
      initSearch("#search-pengumuman", ".card");
      document.querySelectorAll(".tab-btn").forEach((b) =>
        b.addEventListener("click", () => {
          document
            .querySelectorAll(".tab-btn")
            .forEach((x) => x.classList.remove("active"));
          b.classList.add("active");
        }),
      );
    });

    function filterPengumuman(kat) {
      loadPengumuman("pengumuman-container", kat === "all" ? "" : kat);
    }
  </script>
</body>

</html>