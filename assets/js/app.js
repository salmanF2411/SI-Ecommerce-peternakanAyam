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

});
