/* ── app.js — Rumah Pitik ── */

document.addEventListener('DOMContentLoaded', () => {

    /* ── Sticky header shadow ── */
    const header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 10);
        }, { passive: true });
    }

    /* ── Mobile menu toggle ── */
    const toggle = document.getElementById('menu-toggle');
    const nav    = document.getElementById('site-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', nav.classList.contains('open'));
        });
        // close on outside click
        document.addEventListener('click', (e) => {
            if (!toggle.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('open');
            }
        });
    }

    /* ── Auto-dismiss flash alerts ── */
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s ease, transform .4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(20px)';
            setTimeout(() => alert.remove(), 400);
        }, 3500);
    }

    /* ── Quantity input: ± buttons ── */
    document.querySelectorAll('.qty-wrapper').forEach(wrapper => {
        const input = wrapper.querySelector('input[type=number]');
        const btnMinus = wrapper.querySelector('[data-action=minus]');
        const btnPlus  = wrapper.querySelector('[data-action=plus]');
        if (!input) return;

        const update = (delta) => {
            let val = parseInt(input.value) + delta;
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 9999;
            input.value = Math.min(max, Math.max(min, val));
        };

        btnMinus?.addEventListener('click', () => update(-1));
        btnPlus?.addEventListener('click',  () => update(+1));
    });

    /* ── Confirm delete/deactivate ── */
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });

    /* ── Image preview for file uploads ── */
    const fileInput = document.getElementById('product-image-file');
    const preview   = document.getElementById('image-preview');
    if (fileInput && preview) {
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }

    /* ── Active sidebar link ── */
    const sideLinks = document.querySelectorAll('.sidebar nav a');
    const current   = window.location.href;
    sideLinks.forEach(link => {
        if (link.href === current || (link.href !== window.location.origin + '/' && current.startsWith(link.href))) {
            link.classList.add('active');
        }
    });

    /* ── Highlight active nav link ── */
    document.querySelectorAll('.site-nav a').forEach(link => {
        const params    = new URLSearchParams(link.search);
        const curParams = new URLSearchParams(window.location.search);
        if (params.get('page') && params.get('page') === curParams.get('page')) {
            link.classList.add('active');
        }
    });

    /* ── Report date range toggle ── */
    const periodSelect = document.getElementById('report-period');
    const dateRange    = document.getElementById('date-range');
    if (periodSelect && dateRange) {
        periodSelect.addEventListener('change', () => {
            dateRange.style.display = periodSelect.value === 'custom' ? 'flex' : 'none';
        });
    }

    /* ── Modern Toast Notification ── */
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

    /* ── AJAX Add to Cart Interception ── */
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
                const badges = document.querySelectorAll('.cart-link span, .badge, .cart-badge');
                badges.forEach(badge => {
                    badge.textContent = data.cartCount;
                    badge.classList.remove('cart-bump');
                    void badge.offsetWidth;
                    badge.classList.add('cart-bump');
                });

                if (submitBtn) {
                    submitBtn.innerHTML = '<span>✓ Masuk Keranjang</span>';
                    submitBtn.classList.add('btn-added');
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                        submitBtn.classList.remove('btn-added');
                    }, 1600);
                }

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
