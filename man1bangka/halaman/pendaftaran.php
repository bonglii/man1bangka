<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pendaftaran — MAN 1 Bangka</title>
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
      <span style="color: var(--gold)">Pendaftaran</span>
    </div>
    <h1><i class="fas fa-pen"></i> Pendaftaran Kegiatan</h1>
    <p>
      Daftar ekstrakurikuler dan lomba di MAN 1 Bangka. Isi formulir berikut
      dengan lengkap.
    </p>
  </section>

  <section style="padding: 3rem clamp(1rem, 5vw, 4rem)">
    <div class="section-header reveal">
      <div class="section-tag">📝 Pendaftaran</div>
      <h2>Daftar Kegiatan</h2>
      <p>
        Ikuti berbagai kegiatan seru di MAN 1 Bangka. Pilih jenis pendaftaran
        yang kamu butuhkan.
      </p>
      <div class="section-divider"></div>
    </div>
    <div class="tabs" style="max-width: 900px; margin: 0 auto">
      <div class="tabs__nav">
        <button class="tab-btn active" data-tab="daftar-ekskul-tab">
          ⭐ Daftar Ekskul
        </button>
        <button class="tab-btn" data-tab="daftar-lomba-tab">
          🏆 Daftar Lomba
        </button>
      </div>
      <div id="daftar-ekskul-tab" class="tab-panel active">
        <div class="form-card reveal">
          <h3 style="margin-bottom: 1.5rem">
            <i class="fas fa-star" style="color: var(--gold-dark)"></i>
            Formulir Pendaftaran Ekstrakurikuler
          </h3>
          <form id="form-daftar-ekskul" autocomplete="off">
            <input type="hidden" name="ekstrakurikuler_id" value="" />
            <div class="form-grid">
              <div class="form-group">
                <label>Nama Lengkap <span>*</span></label>
                <input
                  class="form-control"
                  name="nama_siswa"
                  required
                  placeholder="Nama lengkap sesuai rapor" />
              </div>
              <div class="form-group">
                <label>Kelas <span>*</span></label>
                <select class="form-control" name="kelas" required>
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
                <label>NIS <span>*</span></label>
                <input
                  class="form-control"
                  name="nis"
                  required
                  placeholder="Nomor Induk Siswa" />
              </div>
              <div class="form-group">
                <label>Ekstrakurikuler yang Dipilih <span>*</span></label>
                <select
                  class="form-control"
                  id="select-ekskul"
                  name="ekskul_nama"
                  required
                  onchange="setEkskulId(this)">
                  <option value="">-- Memuat data ekskul... --</option>
                </select>
              </div>
              <div class="form-group">
                <label>No. HP / WhatsApp</label>
                <input
                  class="form-control"
                  name="no_hp"
                  placeholder="08xxxxxxxxxx"
                  type="tel" />
              </div>
              <div class="form-group">
                <label>Email</label>
                <input
                  class="form-control"
                  name="email"
                  placeholder="email@example.com"
                  type="email" />
              </div>
              <div class="form-group full">
                <label>Alasan Bergabung</label>
                <textarea
                  class="form-control"
                  name="alasan"
                  rows="4"
                  placeholder="Ceritakan alasan dan motivasimu bergabung di ekskul ini..."></textarea>
              </div>
            </div>
            <div
              style="
                  margin-top: 1.5rem;
                  display: flex;
                  gap: 1rem;
                  flex-wrap: wrap;
                ">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> Kirim Pendaftaran
              </button>
              <button
                type="reset"
                class="btn btn-outline"
                style="border-color: var(--gray-200); color: var(--gray-600)">
                Reset
              </button>
            </div>
          </form>
        </div>
      </div>
      <div id="daftar-lomba-tab" class="tab-panel">
        <div class="form-card reveal">
          <h3 style="margin-bottom: 1.5rem">
            <i class="fas fa-trophy" style="color: var(--gold-dark)"></i>
            Formulir Pendaftaran Lomba
          </h3>
          <form onsubmit="submitLomba(event)" autocomplete="off">
            <div class="form-grid">
              <div class="form-group">
                <label>Nama Lengkap <span>*</span></label>
                <input
                  class="form-control"
                  name="nama"
                  required
                  placeholder="Nama lengkap" />
              </div>
              <div class="form-group">
                <label>Kelas <span>*</span></label>
                <select class="form-control" name="kelas" required>
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
                <label>NIS <span>*</span></label>
                <input
                  class="form-control"
                  name="nis"
                  required
                  placeholder="Nomor Induk Siswa" />
              </div>
              <div class="form-group">
                <label>Nama Lomba <span>*</span></label>
                <input
                  class="form-control"
                  name="nama_lomba"
                  required
                  placeholder="Nama lomba yang diikuti" />
              </div>
              <div class="form-group">
                <label>Kategori / Tingkat Lomba</label>
                <select class="form-control" name="tingkat">
                  <option value="">-- Pilih Kategori --</option>
                  <option>Matematika</option>
                  <option>Fisika</option>
                  <option>Kimia</option>
                  <option>Biologi</option>
                  <option>Informatika</option>
                  <option>Bahasa Indonesia</option>
                  <option>Bahasa Inggris</option>
                  <option>Seni</option>
                  <option>Olahraga</option>
                  <option>Lainnya</option>
                </select>
              </div>
              <div class="form-group">
                <label>No. HP</label>
                <input
                  class="form-control"
                  name="no_hp"
                  placeholder="08xxxxxxxxxx"
                  type="tel" />
              </div>
            </div>
            <div style="margin-top: 1.5rem">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> Kirim Pendaftaran
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
  <?php include 'footer.php'; ?>
  <script src="../assets/js/main.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // initTabs(), initReveal(), initForms() already called by main.js
      // Load ekskul options dynamically from API
      (async () => {
        const sel = document.getElementById('select-ekskul');
        if (!sel) return;
        try {
          const res = await apiGet('ekskul', 'list');
          if (res.status === 'success' && res.data.length) {
            sel.innerHTML = '<option value="">-- Pilih Ekskul --</option>' +
              res.data.map(e => `<option value="${e.id}">${e.nama}</option>`).join('');
          } else {
            sel.innerHTML = '<option value="">-- Gagal memuat ekskul --</option>';
          }
        } catch (err) {
          sel.innerHTML = '<option value="">-- Gagal memuat ekskul --</option>';
        }
      })();
    });

    function setEkskulId(sel) {
      document.querySelector('[name="ekstrakurikuler_id"]').value = sel.value;
    }

    function submitLomba(e) {
      e.preventDefault();
      const form = e.target;
      const btn = form.querySelector('[type=submit]');
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftar...';

      const fd = new FormData(form);
      fetch('../php/api.php?module=pendaftaran_lomba', {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(res => {
          if (res.status === 'success') {
            showAlert(res.message || 'Pendaftaran berhasil dikirim!');
            form.reset();
          } else {
            showAlert(res.message || 'Gagal mendaftar.', 'error');
          }
        })
        .catch(() => showAlert('Gagal terhubung ke server.', 'error'))
        .finally(() => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-check"></i> Kirim Pendaftaran';
        });
    }
  </script>
</body>

</html>