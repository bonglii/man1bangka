<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Testimoni Siswa — MAN 1 Bangka</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>

  <?php include 'navbar.php'; ?>

  <section class="page-hero">
    <div class="breadcrumb"><a href="../index.html">Beranda</a> <i class="fas fa-chevron-right"></i> <span style="color:var(--gold);">Testimoni Siswa</span></div>
    <h1><i class="far fa-comment-dots"></i> Testimoni Siswa</h1>
    <p>Pengalaman dan cerita nyata dari siswa MAN 1 Bangka tentang kegiatan yang telah mereka ikuti.</p>
  </section>

  <section class="testimoni-section" style="padding:4rem clamp(1rem,5vw,4rem);">
    <div class="section-header reveal">
      <div class="section-tag">💬 Testimoni</div>
      <h2>Suara Siswa MAN 1 Bangka</h2>
      <p>Pengalaman dan cerita nyata dari siswa yang telah merasakan kegiatan seru di MAN 1 Bangka.</p>
      <div class="section-divider"></div>
    </div>
    <div class="testimoni-slider" style="overflow:hidden;max-width:1200px;margin:0 auto;">
      <div class="testimoni-track" id="testimoni-track">
        <div class="testimoni-card">
          <div class="quote">"</div>
          <p class="text">Memuat testimoni...</p>
        </div>
      </div>
    </div>
    <div class="slider-controls">
      <button class="slider-btn prev"><i class="fas fa-chevron-left"></i></button>
      <div class="slider-dots"><span class="slider-dot active"></span></div>
      <button class="slider-btn next"><i class="fas fa-chevron-right"></i></button>
    </div>
  </section>

  <section style="padding:3rem clamp(1rem,5vw,4rem);background:var(--cream);">
    <div style="max-width:700px;margin:0 auto;">
      <div class="section-header reveal" style="margin-bottom:2rem;">
        <div class="section-tag">✍️ Bagikan</div>
        <h2>Bagikan Pengalamanmu</h2>
        <p>Ceritakan pengalaman seru dan inspirasimu di MAN 1 Bangka!</p>
        <div class="section-divider"></div>
      </div>
      <div class="form-card reveal">
        <form id="form-testimoni" autocomplete="off">
          <div class="form-grid">
            <div class="form-group">
              <label>Nama Siswa <span>*</span></label>
              <input class="form-control" name="nama_siswa" required placeholder="Nama lengkapmu" />
            </div>
            <div class="form-group">
              <label>Kelas</label>
              <select class="form-control" name="kelas">
                <option value="">-- Pilih Kelas --</option>
                <option>10A</option>
                <option>10B</option>
                <option>10C</option>
                <option>10D</option>
                <option>10E</option>
                <option>10F</option>
                <option>11A</option>
                <option>11B</option>
                <option>11C</option>
                <option>11D</option>
                <option>11E</option>
                <option>11F</option>
                <option>12A</option>
                <option>12B</option>
                <option>12C</option>
                <option>12D</option>
                <option>12E</option>
                <option>12F</option>
              </select>
            </div>
            <div class="form-group">
              <label>Jenis Kegiatan</label>
              <select class="form-control" name="jenis_kegiatan">
                <option value="ekskul">Ekstrakurikuler</option>
                <option value="lomba">Lomba / Kompetisi</option>
                <option value="seminar">Seminar / Pelatihan</option>
                <option value="organisasi">Organisasi (OSIS)</option>
                <option value="lainnya">Lainnya</option>
              </select>
            </div>
            <div class="form-group">
              <label>Nama Kegiatan</label>
              <input class="form-control" name="nama_kegiatan" placeholder="Nama kegiatan yang diikuti" />
            </div>
            <div class="form-group full">
              <label>Ceritakan Pengalamanmu <span>*</span></label>
              <textarea class="form-control" name="isi" rows="5" required placeholder="Ceritakan pengalaman seru, kesan, atau inspirasi yang kamu dapat..."></textarea>
            </div>
            <div class="form-group full">
              <label>Rating</label>
              <input type="hidden" name="rating" id="rating-value" value="5" />
              <div class="rating-input" id="rating-stars">
                <i class="fas fa-star active" data-val="1"></i>
                <i class="fas fa-star active" data-val="2"></i>
                <i class="fas fa-star active" data-val="3"></i>
                <i class="fas fa-star active" data-val="4"></i>
                <i class="fas fa-star active" data-val="5"></i>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:1rem;width:100%;">
            <i class="fas fa-paper-plane"></i> Kirim Testimoni
          </button>
        </form>
      </div>
    </div>
  </section>
  <?php include 'footer.php'; ?>
  <script src="../assets/js/main.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // loadTestimoni() & initReveal() & initForms() already called by main.js DOMContentLoaded
      // Only add page-specific star rating logic here

      const stars = document.querySelectorAll('#rating-stars .fa-star');
      const ratingInput = document.getElementById('rating-value');

      function updateStars(val) {
        stars.forEach((s, i) => s.classList.toggle('active', i < val));
        ratingInput.value = val;
      }

      stars.forEach((star) => {
        star.addEventListener('mouseenter', () => {
          const val = parseInt(star.getAttribute('data-val'));
          stars.forEach((s, i) => s.classList.toggle('active', i < val));
        });
        star.addEventListener('mouseleave', () => {
          updateStars(parseInt(ratingInput.value) || 5);
        });
        star.addEventListener('click', () => {
          updateStars(parseInt(star.getAttribute('data-val')));
        });
      });

      // Expose resetStars for use after form.reset() in initForms
      window._resetTestimoniStars = () => updateStars(5);
    });
  </script>
</body>

</html>