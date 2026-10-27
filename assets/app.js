/* ============================================================
   TokoKu — JavaScript global
   Berisi helper umum yang dipakai lintas halaman:
   - formatRupiah / money  : format angka ke Rupiah
   - showToast             : notifikasi kecil di pojok layar
   - confirmDelete         : konfirmasi sebelum hapus data
   - Kontrol sidebar mobile (buka / tutup via overlay)
   - Toggle lihat password di halaman login
   ============================================================ */

'use strict';

/* ---------- Format Rupiah ---------- */
function money(number) {
  return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(number) || 0));
}
var formatRupiah = money; // alias kompatibilitas

/* ---------- Toast notifikasi ---------- */
function showToast(message) {
  var toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(window.__toastTimer);
  window.__toastTimer = setTimeout(function () {
    toast.classList.remove('show');
  }, 2300);
}

/* ---------- Konfirmasi hapus ---------- */
function confirmDelete(message) {
  return confirm(message || 'Hapus data ini?');
}

/* ---------- Sidebar mobile ---------- */
(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    var sidebar = document.getElementById('sidebar');
    var toggle  = document.getElementById('sidebarToggle');
    var close   = document.getElementById('sidebarClose');
    var overlay = document.getElementById('sidebarOverlay');
    if (!sidebar) return;

    function openSidebar() {
      sidebar.classList.add('open');
      if (overlay) overlay.classList.add('show');
    }
    function closeSidebar() {
      sidebar.classList.remove('open');
      if (overlay) overlay.classList.remove('show');
    }

    if (toggle)  toggle.addEventListener('click', openSidebar);
    if (close)   close.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Tutup sidebar mobile setiap pindah halaman
    sidebar.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', closeSidebar);
    });
  });
})();

/* ---------- Toggle password (halaman login) ---------- */
(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    var toggleBtn = document.getElementById('togglePassword');
    var password  = document.getElementById('loginPassword');
    if (!toggleBtn || !password) return;

    toggleBtn.addEventListener('click', function () {
      var hidden = password.type === 'password';
      password.type = hidden ? 'text' : 'password';
      toggleBtn.innerHTML = hidden
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7.5 11-7.5S23 12 23 12s-4 7.5-11 7.5S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>';
    });
  });
})();

/* ---------- Theme mode (light / dark) ---------- */
(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    var toggleBtn = document.getElementById('themeToggle');

    function applyTheme(theme) {
      var safeTheme = theme === 'dark' ? 'dark' : 'light';
      document.body.setAttribute('data-theme', safeTheme);
      document.documentElement.setAttribute('data-theme', safeTheme);

      if (toggleBtn) {
        toggleBtn.textContent = safeTheme === 'dark' ? 'Light' : 'Dark';
        toggleBtn.setAttribute('aria-label', safeTheme === 'dark' ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
      }

      try {
        localStorage.setItem('theme', safeTheme);
      } catch (e) {}
    }

    try {
      var storedTheme = localStorage.getItem('theme');
      applyTheme(storedTheme || 'light');
    } catch (e) {
      applyTheme('light');
    }

    if (toggleBtn) {
      toggleBtn.addEventListener('click', function () {
        var nextTheme = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
      });
    }
  });
})();
