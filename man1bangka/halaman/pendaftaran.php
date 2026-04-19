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
                <label>Pilih Lomba <span>*</span></label>
                <select
                  class="form-control"
                  name="nama_lomba"
                  id="select-lomba"
                  required
                  onchange="onLombaChange(this)">
                  <option value="">-- Memuat daftar lomba... --</option>
                </select>
              </div>
              <div class="form-group" id="lomba-info" style="display:none;grid-column:1/-1;">
                <div style="padding:.85rem 1rem;border-radius:10px;background:var(--gray-100);border:1px solid var(--gray-200);font-size:.85rem;line-height:1.65;">
                  <div id="lomba-info-body"></div>
                </div>
              </div>
              <div class="form-group">
                <label>Kategori / Tingkat Lomba</label>
                <select class="form-control" name="tingkat" id="select-tingkat">
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

      // Load lomba options dynamically from API (hanya yang masih buka pendaftaran)
      window._lombaData = {};
      (async () => {
        const sel = document.getElementById('select-lomba');
        if (!sel) return;
        try {
          const res = await apiGet('lomba', 'list', { only_open: 1 });
          if (res.status === 'success' && res.data.length) {
            // Simpan data lengkap di memory untuk di-lookup saat change
            res.data.forEach(l => window._lombaData[l.nama] = l);
            sel.innerHTML = '<option value="">-- Pilih Lomba --</option>' +
              res.data.map(l => {
                // Escape both value attribute dan display text supaya nama dengan karakter < > & " tidak merusak rendering
                const escHtml = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                return `<option value="${escHtml(l.nama)}">${escHtml(l.nama)}</option>`;
              }).join('');

            // Pre-select dari query string ?lomba=xxx (dari tombol "Daftar" di halaman lomba)
            const qs = new URLSearchParams(location.search);
            const preLomba = qs.get('lomba');
            if (preLomba && window._lombaData[preLomba]) {
              sel.value = preLomba;
              onLombaChange(sel);
              // Scroll ke tab pendaftaran lomba
              const lombaTabBtn = document.querySelector('[data-tab="daftar-lomba-tab"]');
              if (lombaTabBtn) lombaTabBtn.click();
            }
          } else {
            sel.innerHTML = '<option value="">-- Belum ada lomba yang buka pendaftaran --</option>';
          }
        } catch (err) {
          sel.innerHTML = '<option value="">-- Gagal memuat daftar lomba --</option>';
        }
      })();
    });

    function setEkskulId(sel) {
      document.querySelector('[name="ekstrakurikuler_id"]').value = sel.value;
    }

    // Dipanggil saat user pilih lomba di dropdown — tampilkan info card & auto-map tingkat
    function onLombaChange(sel) {
      const info     = document.getElementById('lomba-info');
      const infoBody = document.getElementById('lomba-info-body');
      const tSel     = document.getElementById('select-tingkat');
      const l = window._lombaData[sel.value];

      if (!l) {
        if (info) info.style.display = 'none';
        return;
      }

      const fmt = (d) => {
        if (!d) return '-';
        const dt = new Date(d);
        if (isNaN(dt)) return d;
        return dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
      };
      const fmtBiaya = (b) => {
        const n = parseInt(b, 10);
        return (!n || n === 0) ? 'Gratis' : 'Rp ' + n.toLocaleString('id-ID');
      };
      const tingkatIcon = { sekolah:'🏫', kabupaten:'🏘️', provinsi:'🗺️', nasional:'🇮🇩', internasional:'🌏' };

      const rows = [];
      rows.push(`<div><i class="fas fa-layer-group" style="width:18px;color:var(--gold-dark);"></i> <strong>Tingkat:</strong> ${tingkatIcon[l.tingkat] || ''} ${l.tingkat || '-'}</div>`);
      if (l.penyelenggara)  rows.push(`<div><i class="fas fa-building" style="width:18px;color:var(--gold-dark);"></i> <strong>Penyelenggara:</strong> ${l.penyelenggara}</div>`);
      if (l.tempat)         rows.push(`<div><i class="fas fa-map-marker-alt" style="width:18px;color:var(--gold-dark);"></i> <strong>Tempat:</strong> ${l.tempat}</div>`);
      if (l.tanggal_mulai)  {
        const range = l.tanggal_selesai && l.tanggal_selesai !== l.tanggal_mulai
          ? `${fmt(l.tanggal_mulai)} – ${fmt(l.tanggal_selesai)}`
          : fmt(l.tanggal_mulai);
        rows.push(`<div><i class="far fa-calendar" style="width:18px;color:var(--gold-dark);"></i> <strong>Jadwal:</strong> ${range}</div>`);
      }
      if (l.deadline_pendaftaran) rows.push(`<div><i class="far fa-clock" style="width:18px;color:#b91c1c;"></i> <strong>Deadline Pendaftaran:</strong> ${fmt(l.deadline_pendaftaran)}</div>`);
      rows.push(`<div><i class="fas fa-money-bill-wave" style="width:18px;color:var(--gold-dark);"></i> <strong>Biaya:</strong> ${fmtBiaya(l.biaya)}</div>`);
      if (l.kontak_pic)     rows.push(`<div><i class="fas fa-user-circle" style="width:18px;color:var(--gold-dark);"></i> <strong>Kontak PIC:</strong> ${l.kontak_pic}</div>`);
      if (l.deskripsi)      rows.push(`<div style="margin-top:.5rem;padding-top:.5rem;border-top:1px dashed var(--gray-300);font-style:italic;color:var(--gray-600);">${l.deskripsi}</div>`);

      infoBody.innerHTML = rows.join('');
      info.style.display = 'block';

      // Auto-map kategori lomba (master) → tingkat form (kategori bidang lomba)
      // Map sederhana: jika kategori master cocok dengan opsi di dropdown tingkat, select-in
      if (tSel && l.kategori) {
        const mapKat = {
          akademik: 'Lainnya', seni: 'Seni', olahraga: 'Olahraga',
          keagamaan: 'Lainnya', teknologi: 'Informatika', lainnya: 'Lainnya'
        };
        const pilih = mapKat[l.kategori];
        if (pilih) {
          for (const opt of tSel.options) {
            if (opt.value === pilih || opt.text === pilih) { tSel.value = opt.value; break; }
          }
        }
      }
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