<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dokumentasi — MAN 1 Bangka</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <style>
      .galeri-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
      }
      .galeri-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 4/3;
        background: #1a1a2e;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }
      .galeri-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: 0.3s;
      }
      .galeri-item video {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      .galeri-item:hover img {
        transform: scale(1.05);
      }
      .galeri-overlay {
        position: absolute;
        inset: 0;
        background: rgba(11, 61, 46, 0.75);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: 0.3s;
        gap: 0.4rem;
        padding: 0.75rem;
      }
      .galeri-item:hover .galeri-overlay {
        opacity: 1;
      }
      .galeri-overlay i {
        font-size: 1.8rem;
        color: #fff;
      }
      .galeri-overlay .g-title {
        color: #fff;
        font-size: 0.78rem;
        text-align: center;
        font-weight: 600;
        line-height: 1.3;
      }
      .galeri-overlay .g-date {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.7rem;
      }
      .galeri-overlay .g-cat {
        background: var(--gold);
        color: var(--green);
        font-size: 0.68rem;
        padding: 0.15rem 0.45rem;
        border-radius: 4px;
        font-weight: 700;
      }
      .video-play-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--green);
        font-size: 1.1rem;
        pointer-events: none;
      }
      .filter-strip {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
        justify-content: center;
      }
      .filter-chip {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        border: 1.5px solid var(--green-light-border, #c8d8cc);
        background: #fff;
        color: var(--green);
        font-size: 0.82rem;
        cursor: pointer;
        transition: 0.2s;
        font-weight: 500;
      }
      .filter-chip.active,
      .filter-chip:hover {
        background: var(--green);
        color: #fff;
        border-color: var(--green);
      }
      /* Lightbox */
      .lightbox {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: 0.25s;
      }
      .lightbox.open {
        opacity: 1;
        pointer-events: all;
      }
      .lightbox-inner {
        position: relative;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
      }
      .lightbox-inner img,
      .lightbox-inner video {
        max-width: 90vw;
        max-height: 78vh;
        border-radius: 10px;
        object-fit: contain;
      }
      .lightbox-caption {
        color: #fff;
        text-align: center;
        font-size: 0.88rem;
        max-width: 600px;
      }
      .lightbox-close {
        position: fixed;
        top: 1.5rem;
        right: 1.5rem;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #fff;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        font-size: 1.2rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .lightbox-back {
        position: fixed;
        top: 1.5rem;
        left: 1.5rem;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #fff;
        padding: 0.5rem 1.1rem;
        border-radius: 30px;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        transition: 0.2s;
      }
      .lightbox-back:hover {
        background: rgba(255, 255, 255, 0.28);
      }
      .lightbox-nav {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #fff;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        font-size: 1.1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .lightbox-nav.prev {
        left: 1rem;
      }
      .lightbox-nav.next {
        right: 1rem;
      }
      @media (max-width: 600px) {
        .galeri-grid {
          grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
      }
    </style>
  </head>
  <body>
    
    <?php include 'navbar.php'; ?>


    <section class="page-hero">
      <div class="breadcrumb">
        <a href="../index.html">Beranda</a> <i class="fas fa-chevron-right"></i>
        <span style="color: var(--gold)">Dokumentasi</span>
      </div>
      <h1><i class="far fa-images"></i> Dokumentasi Kegiatan</h1>
      <p>Galeri foto dan video kegiatan siswa MAN 1 Bangka.</p>
    </section>

    <section style="padding: 3rem clamp(1rem, 5vw, 4rem)">
      <div style="max-width: 1200px; margin: 0 auto">
        <div class="section-header reveal">
          <div class="section-tag">📸 Galeri</div>
          <h2>Dokumentasi Kegiatan</h2>
          <p>
            Kenangan dari berbagai kegiatan siswa MAN 1 Bangka yang telah
            diupload oleh admin.
          </p>
          <div class="section-divider"></div>
        </div>

        <!-- Tab Foto / Video -->
        <div class="tabs" style="margin-bottom: 1.5rem">
          <div class="tabs__nav">
            <button class="tab-btn active" data-tab="tab-foto">📷 Foto</button>
            <button class="tab-btn" data-tab="tab-video">🎬 Video</button>
          </div>

          <!-- TAB FOTO -->
          <div id="tab-foto" class="tab-panel active">
            <div class="filter-strip" id="foto-filter">
              <button class="filter-chip active" data-kat="">Semua</button>
              <button class="filter-chip" data-kat="kegiatan">Kegiatan</button>
              <button class="filter-chip" data-kat="ekskul">Ekskul</button>
              <button class="filter-chip" data-kat="lomba">Lomba</button>
              <button class="filter-chip" data-kat="organisasi">OSIS</button>
              <button class="filter-chip" data-kat="keagamaan">
                Keagamaan
              </button>
            </div>
            <div class="galeri-grid" id="foto-grid">
              <div class="loading"><div class="spinner"></div></div>
            </div>
          </div>

          <!-- TAB VIDEO -->
          <div id="tab-video" class="tab-panel">
            <div class="galeri-grid" id="video-grid">
              <div class="loading"><div class="spinner"></div></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- LIGHTBOX -->
    <div class="lightbox" id="lightbox" onclick="closeLightbox()">
      <button
        class="lightbox-back"
        onclick="
          event.stopPropagation();
          closeLightbox();
        "
      >
        <i class="fas fa-arrow-left"></i> Kembali
      </button>
      <button class="lightbox-close" onclick="closeLightbox()">
        <i class="fas fa-times"></i>
      </button>
      <button
        class="lightbox-nav prev"
        onclick="
          event.stopPropagation();
          shiftLightbox(-1);
        "
      >
        <i class="fas fa-chevron-left"></i>
      </button>
      <button
        class="lightbox-nav next"
        onclick="
          event.stopPropagation();
          shiftLightbox(1);
        "
      >
        <i class="fas fa-chevron-right"></i>
      </button>
      <div class="lightbox-inner" onclick="event.stopPropagation()">
        <div id="lightbox-media"></div>
        <div class="lightbox-caption" id="lightbox-caption"></div>
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
            <a href="#"><i class="fab fa-instagram"></i></a
            ><a href="#"><i class="fab fa-youtube"></i></a
            ><a href="#"><i class="fab fa-facebook-f"></i></a>
          </div>
        </div>
        <div class="footer-col">
          <h4>Menu Utama</h4>
          <ul>
            <li>
              <a href="../index.html"
                ><i class="fas fa-chevron-right"></i> Beranda</a
              >
            </li>
            <li>
              <a href="pengumuman.php"
                ><i class="fas fa-chevron-right"></i> Pengumuman</a
              >
            </li>
            <li>
              <a href="agenda.php"
                ><i class="fas fa-chevron-right"></i> Agenda</a
              >
            </li>
            <li>
              <a href="ekstrakurikuler.php"
                ><i class="fas fa-chevron-right"></i> Ekstrakurikuler</a
              >
            </li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Informasi</h4>
          <ul>
            <li>
              <a href="prestasi.php"
                ><i class="fas fa-chevron-right"></i> Prestasi</a
              >
            </li>
            <li>
              <a href="karya-siswa.php"
                ><i class="fas fa-chevron-right"></i> Karya Siswa</a
              >
            </li>
            <li>
              <a href="arsip.php"
                ><i class="fas fa-chevron-right"></i> Arsip</a
              >
            </li>
            <li>
              <a href="kontak.php"
                ><i class="fas fa-chevron-right"></i> Kontak</a
              >
            </li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Kontak Sekolah</h4>
          <ul>
            <li>
              <a href="#"
                ><i class="fas fa-map-marker-alt"></i> Jl. Raya Bangka, Babel</a
              >
            </li>
            <li>
              <a href="tel:07171234567"
                ><i class="fas fa-phone"></i> (0717) 123-4567</a
              >
            </li>
            <li>
              <a href="mailto:info@man1bangka.sch.id"
                ><i class="fas fa-envelope"></i> info@man1bangka.sch.id</a
              >
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
      let allFoto = [],
        allVideo = [],
        lbIdx = 0,
        lbData = [];

      async function loadFoto(kat = "") {
        const grid = document.getElementById("foto-grid");
        grid.innerHTML =
          '<div class="loading"><div class="spinner"></div></div>';
        const res = await apiGet("dokumentasi", "list", {
          jenis: "foto",
          kategori: kat,
          limit: 60,
        });
        allFoto = res.status === "success" ? res.data : [];
        renderFoto(allFoto);
      }

      function renderFoto(data) {
        const grid = document.getElementById("foto-grid");
        if (!data.length) {
          grid.innerHTML =
            '<div class="empty-state" style="grid-column:1/-1"><i class="far fa-image"></i><p>Belum ada foto. Admin dapat mengupload melalui panel admin.</p></div>';
          return;
        }
        grid.innerHTML = data
          .map(
            (f, i) => `
    <div class="galeri-item reveal" onclick="openLightbox(${i},'foto')">
      <img src="../${f.url_media}" alt="${f.judul}" loading="lazy" onerror="this.parentElement.style.background='#1a1a2e';this.style.display='none'"/>
      <div class="galeri-overlay">
        <i class="fas fa-search-plus"></i>
        <div class="g-cat">${f.kategori || "Kegiatan"}</div>
        <div class="g-title">${f.judul}</div>
        <div class="g-date">${f.tanggal_format || ""}</div>
      </div>
    </div>
  `,
          )
          .join("");
        initReveal();
      }

      async function loadVideo() {
        const grid = document.getElementById("video-grid");
        grid.innerHTML =
          '<div class="loading"><div class="spinner"></div></div>';
        const res = await apiGet("dokumentasi", "list", {
          jenis: "video",
          limit: 30,
        });
        allVideo = res.status === "success" ? res.data : [];
        if (!allVideo.length) {
          grid.innerHTML =
            '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-video-slash"></i><p>Belum ada video. Admin dapat mengupload melalui panel admin.</p></div>';
          return;
        }
        grid.innerHTML = allVideo
          .map(
            (v, i) => `
    <div class="galeri-item reveal" onclick="openLightbox(${i},'video')">
      <video src="../${v.url_media}" muted preload="metadata"></video>
      <div class="video-play-btn"><i class="fas fa-play"></i></div>
      <div class="galeri-overlay">
        <i class="fas fa-play-circle"></i>
        <div class="g-cat">${v.kategori || "Video"}</div>
        <div class="g-title">${v.judul}</div>
        <div class="g-date">${v.tanggal_format || ""}</div>
      </div>
    </div>
  `,
          )
          .join("");
        initReveal();
      }

      function openLightbox(idx, type) {
        lbData = type === "foto" ? allFoto : allVideo;
        lbIdx = idx;
        showLightboxItem();
        document.getElementById("lightbox").classList.add("open");
        document.body.style.overflow = "hidden";
      }

      function showLightboxItem() {
        const item = lbData[lbIdx];
        const media = document.getElementById("lightbox-media");

        // Stop any existing video
        const prevVid = media.querySelector("video");
        if (prevVid) {
          prevVid.pause();
          prevVid.src = "";
        }
        media.innerHTML = "";

        const isVideo =
          item.jenis === "video" || (item.url_media || "").includes("/video/");

        if (isVideo) {
          // Build video path - try with and without ../
          const src = "../" + item.url_media;

          const vid = document.createElement("video");
          vid.controls = true;
          vid.style.cssText =
            "max-width:90vw;max-height:78vh;border-radius:10px;display:block;";
          vid.style.background = "#000";
          vid.preload = "auto";

          // Loading indicator
          const loadWrap = document.createElement("div");
          loadWrap.id = "vid-loading";
          loadWrap.style.cssText =
            "color:#fff;text-align:center;padding:2rem;font-size:.85rem;opacity:.7;";
          loadWrap.innerHTML =
            '<i class="fas fa-spinner fa-spin" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>Memuat video...';
          media.appendChild(loadWrap);

          vid.addEventListener("canplay", () => {
            loadWrap.remove();
            media.insertBefore(vid, media.firstChild);
            vid.play().catch((e) => {
              // Autoplay blocked - show play button overlay
              const playBtn = document.createElement("button");
              playBtn.style.cssText =
                "position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.9);border:none;font-size:1.5rem;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#0B3D2E;z-index:10;";
              playBtn.innerHTML = '<i class="fas fa-play"></i>';
              playBtn.onclick = () => {
                vid.play();
                playBtn.remove();
              };
              media.style.position = "relative";
              media.appendChild(playBtn);
            });
          });

          vid.addEventListener("error", () => {
            loadWrap.remove();
            media.innerHTML = `
        <div style="color:#fff;text-align:center;padding:2rem;max-width:400px;">
          <i class="fas fa-exclamation-triangle" style="font-size:2.5rem;color:#f97316;margin-bottom:1rem;display:block;"></i>
          <h4 style="margin-bottom:.5rem;">Video Tidak Dapat Diputar</h4>
          <p style="font-size:.82rem;opacity:.7;margin-bottom:1rem;">Format video mungkin tidak didukung browser ini.</p>
          <a href="${src}" download target="_blank"
             style="display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.2rem;background:#C9A84C;color:#0B3D2E;border-radius:8px;text-decoration:none;font-weight:700;font-size:.85rem;">
            <i class="fas fa-download"></i> Unduh Video
          </a>
        </div>`;
          });

          vid.src = src;
          vid.load();
        } else {
          // Photo
          const img = document.createElement("img");
          img.style.cssText =
            "max-width:90vw;max-height:78vh;border-radius:10px;object-fit:contain;display:block;";
          img.alt = item.judul;

          const loadWrap = document.createElement("div");
          loadWrap.style.cssText =
            "color:#fff;text-align:center;padding:2rem;font-size:.85rem;opacity:.7;";
          loadWrap.innerHTML =
            '<i class="fas fa-spinner fa-spin" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>Memuat...';
          media.appendChild(loadWrap);

          img.onload = () => {
            loadWrap.remove();
            media.appendChild(img);
          };
          img.onerror = () => {
            loadWrap.innerHTML =
              '<i class="fas fa-image-slash" style="font-size:2rem;margin-bottom:.5rem;display:block;"></i>Gambar tidak tersedia';
          };
          img.src = "../" + item.url_media;
        }

        document.getElementById("lightbox-caption").innerHTML =
          `<strong>${item.judul}</strong>${
            item.deskripsi
              ? '<br><span style="opacity:.7;font-size:.82rem;">' +
                item.deskripsi +
                "</span>"
              : ""
          }`;
      }

      function closeLightbox() {
        const lb = document.getElementById("lightbox");
        lb.classList.remove("open");
        document.body.style.overflow = "";
        // Properly stop video
        const vid = document
          .getElementById("lightbox-media")
          .querySelector("video");
        if (vid) {
          vid.pause();
          vid.src = "";
        }
        document.getElementById("lightbox-media").innerHTML = "";
      }

      function shiftLightbox(dir) {
        lbIdx = (lbIdx + dir + lbData.length) % lbData.length;
        showLightboxItem();
      }

      // Filter chips
      document.querySelectorAll("#foto-filter .filter-chip").forEach((btn) => {
        btn.addEventListener("click", () => {
          document
            .querySelectorAll("#foto-filter .filter-chip")
            .forEach((b) => b.classList.remove("active"));
          btn.classList.add("active");
          const kat = btn.dataset.kat;
          if (kat === "") {
            renderFoto(allFoto);
          } else {
            renderFoto(allFoto.filter((f) => f.kategori === kat));
          }
        });
      });

      // Keyboard lightbox
      document.addEventListener("keydown", (e) => {
        if (!document.getElementById("lightbox").classList.contains("open"))
          return;
        if (e.key === "Escape") closeLightbox();
        if (e.key === "ArrowLeft") shiftLightbox(-1);
        if (e.key === "ArrowRight") shiftLightbox(1);
      });

      // Load foto tab dulu
      document.addEventListener("DOMContentLoaded", () => {
        initNavbar();
        initReveal();
        loadFoto();
        // Load video when tab clicked
        document
          .querySelector('[data-tab="tab-video"]')
          .addEventListener("click", () => {
            if (!allVideo.length) loadVideo();
          });
        initTabs();
      });
    </script>
  </body>
</html>
