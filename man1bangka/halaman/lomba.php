<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lomba — MAN 1 Bangka</title>
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
      <span style="color: var(--gold)">Lomba</span>
    </div>
    <h1><i class="fas fa-medal"></i> Lomba</h1>
    <p>
      Daftar lomba yang sedang dan akan berlangsung. Asah kemampuan dan raih prestasi
      untuk MAN 1 Bangka!
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
        <button class="tab-btn active" onclick="filterLomba('')">Semua</button>
        <button class="tab-btn" onclick="filterLomba('akademik')">📚 Akademik</button>
        <button class="tab-btn" onclick="filterLomba('seni')">🎨 Seni</button>
        <button class="tab-btn" onclick="filterLomba('olahraga')">⚽ Olahraga</button>
        <button class="tab-btn" onclick="filterLomba('keagamaan')">🕌 Keagamaan</button>
        <button class="tab-btn" onclick="filterLomba('teknologi')">💻 Teknologi</button>
      </div>
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" id="search-lomba" placeholder="Cari lomba..." />
      </div>
    </div>
    <div class="cards-grid" id="lomba-container">
      <div class="loading">
        <div class="spinner"></div>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>
  <script src="../assets/js/main.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      loadLomba("lomba-container");
      initSearch("#search-lomba", ".ekskul-card");
      document.querySelectorAll(".tab-btn").forEach((b) =>
        b.addEventListener("click", () => {
          document
            .querySelectorAll(".tab-btn")
            .forEach((x) => x.classList.remove("active"));
          b.classList.add("active");
        }),
      );
    });

    function filterLomba(kat) {
      loadLomba("lomba-container", kat);
    }
  </script>
</body>

</html>
