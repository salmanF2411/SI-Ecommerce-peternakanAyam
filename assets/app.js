/**
 * Rumah Pitik - Application Script
 */
document.addEventListener('DOMContentLoaded', () => {
  // Mobile menu toggle
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.site-header nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => nav.classList.toggle('open'));
  }

  // Auto-dismiss server flash alerts
  const alert = document.querySelector('.alert');
  if (alert) {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.4s, transform 0.4s';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-8px)';
      setTimeout(() => alert.remove(), 400);
    }, 4200);
  }

  // Detail page quantity controls (+ / -)
  document.querySelectorAll('.detail-qty-control').forEach(wrap => {
    const input = wrap.querySelector('.qty-detail-input');
    const minusBtn = wrap.querySelector('[data-action="minus"]');
    const plusBtn = wrap.querySelector('[data-action="plus"]');
    if (!input || !minusBtn || !plusBtn) return;

    const min = parseInt(input.getAttribute('min'), 10) || 1;
    const max = parseInt(input.getAttribute('max'), 10) || 999;

    function syncState() {
      const val = parseInt(input.value, 10) || min;
      minusBtn.disabled = val <= min;
      plusBtn.disabled = val >= max;
    }

    minusBtn.addEventListener('click', () => {
      let val = parseInt(input.value, 10) || min;
      if (val > min) {
        input.value = val - 1;
        syncState();
      }
    });

    plusBtn.addEventListener('click', () => {
      let val = parseInt(input.value, 10) || min;
      if (val < max) {
        input.value = val + 1;
        syncState();
      }
    });

    input.addEventListener('change', () => {
      let val = parseInt(input.value, 10);
      if (isNaN(val) || val < min) val = min;
      if (val > max) val = max;
      input.value = val;
      syncState();
    });

    syncState();
  });

  // Modern Toast Notification
  window.showToast = function(message, type = 'success', actionUrl = '', actionText = 'Lihat Keranjang →') {
    let container = document.getElementById('rp-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'rp-toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `rp-toast rp-toast-${type}`;

    let iconChar = '✓';
    if (type === 'warning') iconChar = '!';
    if (type === 'danger') iconChar = '✕';

    let actionHtml = '';
    if (actionUrl) {
      actionHtml = `<a href="${actionUrl}" class="rp-toast-link">${actionText}</a>`;
    }

    toast.innerHTML = `
      <div class="rp-toast-content">
        <div class="rp-toast-icon">${iconChar}</div>
        <div class="rp-toast-text">
          <span>${message}</span>
          ${actionHtml}
        </div>
      </div>
      <button type="button" class="rp-toast-close" aria-label="Tutup">&times;</button>
    `;

    container.appendChild(toast);

    function removeToast() {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(40px) scale(0.95)';
      setTimeout(() => toast.remove(), 300);
    }

    const closeBtn = toast.querySelector('.rp-toast-close');
    if (closeBtn) closeBtn.addEventListener('click', removeToast);

    setTimeout(removeToast, 4500);
  };

  // Add to Cart AJAX Interceptor
  document.addEventListener('submit', function(e) {
    const form = e.target;
    const actionInput = form.querySelector('input[name="action"]');
    if (!actionInput || actionInput.value !== 'add_cart') return;

    e.preventDefault();

    const submitBtn = form.querySelector('button[type="submit"], .btn-add-cart, button.primary');
    const originalContent = submitBtn ? submitBtn.innerHTML : '';

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span>Menambahkan...</span>';
    }

    const formData = new FormData(form);

    fetch('index.php', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: formData
    })
      .then(res => {
        if (!res.ok && res.status !== 400 && res.status !== 401 && res.status !== 403) {
          throw new Error('HTTP ' + res.status);
        }
        return res.json();
      })
      .then(data => {
        if (data.ok) {
          // Update navbar cart badge count
          const badges = document.querySelectorAll('.cart-link span, .badge, .cart-badge');
          badges.forEach(badge => {
            badge.textContent = data.cartCount;
            badge.classList.remove('cart-bump');
            void badge.offsetWidth; // force reflow for animation
            badge.classList.add('cart-bump');
          });

          // Temporary button feedback
          if (submitBtn) {
            submitBtn.innerHTML = '<span>✓ Masuk Keranjang</span>';
            submitBtn.classList.add('btn-added');
            setTimeout(() => {
              submitBtn.disabled = false;
              submitBtn.innerHTML = originalContent;
              submitBtn.classList.remove('btn-added');
            }, 1600);
          }

          // Show floating toast notification with direct link to cart
          showToast(data.message || 'Produk berhasil masuk ke keranjang.', 'success', '?page=cart', 'Lihat Keranjang →');
        } else {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalContent;
          }

          if (data.requires_login) {
            showToast(data.message || 'Silakan login terlebih dahulu.', 'warning', data.redirect || '?page=login', 'Masuk Akun');
            if (data.redirect) {
              setTimeout(() => {
                window.location.href = data.redirect;
              }, 1400);
            }
          } else {
            showToast(data.message || 'Gagal menambahkan produk.', 'danger');
          }
        }
      })
      .catch(err => {
        console.error('Add to cart error:', err);
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalContent;
        }
        showToast('Terjadi gangguan jaringan. Silakan coba lagi.', 'danger');
      });
  });
});
