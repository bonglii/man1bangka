<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Karya Siswa — MAN 1 Bangka</title>
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
      <span style="color: var(--gold)">Karya Siswa</span>
    </div>
    <h1><i class="fas fa-palette"></i> Karya Siswa</h1>
    <p>
      Menampilkan potensi, kreativitas, dan kemampuan terbaik siswa MAN 1
      Bangka kepada dunia.
    </p>
  </section>

  <section style="padding: 3rem clamp(1rem, 5vw, 4rem)">
    <div style="max-width: 1200px; margin: 0 auto">
      <div class="section-header reveal">
        <div class="section-tag">🎨 Karya</div>
        <h2>Karya Siswa MAN 1 Bangka</h2>
        <p>
          Berbagai hasil kreativitas, penelitian, dan karya terbaik dari
          siswa-siswi kami.
        </p>
        <div class="section-divider"></div>
      </div>

      <div
        style="
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            justify-content: center;
          "
        id="karya-filter">
        <button
          class="filter-btn active"
          data-filter="all"
          onclick="filterKarya(this, '')">
          Semua
        </button>
        <button
          class="filter-btn"
          data-filter="artikel"
          onclick="filterKarya(this, 'artikel')">
          📝 Artikel
        </button>
        <button
          class="filter-btn"
          data-filter="karya_ilmiah"
          onclick="filterKarya(this, 'karya_ilmiah')">
          🔬 Karya Ilmiah
        </button>
        <button
          class="filter-btn"
          data-filter="poster"
          onclick="filterKarya(this, 'poster')">
          🖼️ Poster
        </button>
        <button
          class="filter-btn"
          data-filter="video"
          onclick="filterKarya(this, 'video')">
          🎬 Video
        </button>
        <button
          class="filter-btn"
          data-filter="puisi"
          onclick="filterKarya(this, 'puisi')">
          ✍️ Puisi
        </button>
        <button
          class="filter-btn"
          data-filter="lainnya"
          onclick="filterKarya(this, 'lainnya')">
          ⭐ Lainnya
        </button>
      </div>

      <div
        id="karya-container"
        style="
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
          ">
        <div class="loading" style="grid-column: 1/-1">
          <div class="spinner"></div>
        </div>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>
  <script src="../assets/js/main.js"></script>
  <script>
    let allKarya = [];
    const jenisIcon = {
      artikel: "📝",
      karya_ilmiah: "🔬",
      poster: "🖼️",
      video: "🎬",
      puisi: "✍️",
      lainnya: "⭐",
    };
    const jenisLabel = {
      artikel: "Artikel",
      karya_ilmiah: "Karya Ilmiah",
      poster: "Poster",
      video: "Video",
      puisi: "Puisi",
      lainnya: "Lainnya",
    };

    async function loadKarya() {
      const container = document.getElementById("karya-container");
      container.innerHTML =
        '<div class="loading" style="grid-column:1/-1"><div class="spinner"></div></div>';
      const res = await apiGet("karya", "list");
      allKarya = res.status === "success" ? res.data : [];
      renderKarya(allKarya);
    }

    function renderKarya(data) {
      const container = document.getElementById("karya-container");
      if (!data.length) {
        container.innerHTML =
          '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-palette"></i><p>Belum ada karya yang dipublikasikan.</p></div>';
        return;
      }
      container.innerHTML = data
        .map((k) => {
          const icon = jenisIcon[k.jenis] || "⭐";
          const label = jenisLabel[k.jenis] || k.jenis;
          const siswa = k.siswa || k.penulis || "Siswa";
          return `
      <div class="card reveal" data-cat="${k.jenis}">
        <div class="card__img">
          ${
            k.url_file && k.url_file.match(/\.(jpg|jpeg|png|webp)$/i)
              ? `<img src="../${k.url_file}" alt="${k.judul}" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.innerHTML='<div class=card__img-placeholder>${icon}</div>'">`
              : `<div class="card__img-placeholder">${icon}</div>`
          }
        </div>
        <div class="card__body">
          <span class="card__tag tag-kegiatan">${label}</span>
          <h3 class="card__title">${k.judul}</h3>
          <p class="card__text">${(k.deskripsi || "").substring(0, 140)}${(k.deskripsi || "").length > 140 ? "..." : ""}</p>
          <div class="card__meta">
            <span><i class="fas fa-user"></i> ${siswa}${k.kelas ? " — " + k.kelas : ""}</span>
          </div>
        </div>
        <div class="card__footer">
          ${k.penghargaan ? `<span class="badge badge-gold"><i class="fas fa-award"></i> ${k.penghargaan}</span>` : "<span></span>"}
          ${k.url_file ? `<a href="../${k.url_file}" target="_blank" class="btn btn-green btn-sm"><i class="fas fa-eye"></i> Lihat</a>` : '<span class="badge" style="background:#e0e0e0;color:#666;">Segera Hadir</span>'}
        </div>
      </div>`;
        })
        .join("");
      initReveal();
    }

    function filterKarya(btn, jenis) {
      document
        .querySelectorAll("#karya-filter .filter-btn")
        .forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      renderKarya(
        jenis ? allKarya.filter((k) => k.jenis === jenis) : allKarya,
      );
    }

    document.addEventListener("DOMContentLoaded", () => {
      // initNavbar() and initReveal() already called by main.js
      loadKarya();
    });
  </script>
</body>

</html>