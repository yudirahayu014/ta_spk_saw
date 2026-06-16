/**
 * assets/js/app.js
 * Logika JavaScript ringan untuk UI SPK.
 */

// Sidebar toggle (mobile)
document.addEventListener('DOMContentLoaded', () => {
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');

  if (toggle && sidebar) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', e => {
      if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  // Auto-hide flash message setelah 4 detik
  const flash = document.getElementById('flash-message');
  if (flash) setTimeout(() => flash.remove(), 4000);

  // Konfirmasi hapus
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm || 'Yakin ingin menghapus data ini?')) {
        e.preventDefault();
      }
    });
  });
});

// Fungsi cetak halaman hasil (tanpa elemen yang tidak perlu)
function printHasil() {
  window.print();
}