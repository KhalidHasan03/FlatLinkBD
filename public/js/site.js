(function () {
    'use strict';

    function onReady(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    onReady(function () {
        initSiteNav();
        initSearchTabs();
        initGallery();
        initFlash();
        initReveal();
        initDashSidebar();
        initDashNav();
        initAreaFilter();
    });

    /* ---------- Mobile site navigation ---------- */
    function initSiteNav() {
        var toggle = document.getElementById('navToggle');
        var nav = document.getElementById('siteNav');
        if (!toggle || !nav) return;

        toggle.addEventListener('click', function () {
            nav.classList.toggle('is-open');
            toggle.classList.toggle('is-active');
        });

        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) {
                nav.classList.remove('is-open');
                toggle.classList.remove('is-active');
            }
        });
    }

    /* ---------- Home hero search purpose tabs ---------- */
    function initSearchTabs() {
        var tabsRoot = document.querySelector('[data-tabs]');
        var hidden = document.getElementById('searchPurpose');
        if (!tabsRoot) return;

        var tabs = tabsRoot.querySelectorAll('[data-purpose]');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('is-active'); });
                tab.classList.add('is-active');
                if (hidden) hidden.value = tab.getAttribute('data-purpose');
            });
        });
    }

    /* ---------- Property detail gallery ---------- */
    function initGallery() {
        var root = document.querySelector('[data-gallery]');
        var main = document.getElementById('galleryMain');
        if (!root || !main) return;

        var thumbs = root.querySelectorAll('[data-full]');
        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                var full = thumb.getAttribute('data-full');
                if (!full) return;
                main.style.opacity = '0';
                var img = new Image();
                img.onload = function () {
                    main.src = full;
                    main.style.opacity = '1';
                };
                img.src = full;
                thumbs.forEach(function (t) { t.classList.remove('is-active'); });
                thumb.classList.add('is-active');
            });
        });
    }

    /* ---------- Flash dismiss ---------- */
    function initFlash() {
        document.querySelectorAll('[data-dismiss]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var alert = btn.closest('.alert');
                if (!alert) return;
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-6px)';
                alert.style.transition = 'opacity .2s, transform .2s';
                setTimeout(function () { alert.remove(); }, 220);
            });
        });

        document.querySelectorAll('[data-dismissible]').forEach(function (alert) {
            setTimeout(function () {
                if (!alert.isConnected) return;
                alert.style.opacity = '0';
                alert.style.transition = 'opacity .4s';
                setTimeout(function () { alert.remove(); }, 420);
            }, 6000);
        });
    }

    /* ---------- Scroll reveal ---------- */
    function initReveal() {
        var items = document.querySelectorAll('.reveal');
        if (!items.length) return;

        if (!('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        items.forEach(function (el) { observer.observe(el); });
    }

    /* ---------- Dashboard sidebar ---------- */
    function initDashSidebar() {
        var toggle = document.getElementById('dashToggle');
        var sidebar = document.getElementById('dashSidebar');
        var overlay = document.getElementById('dashOverlay');
        if (!toggle || !sidebar) return;

        function close() {
            sidebar.classList.remove('is-open');
            if (overlay) overlay.classList.remove('is-open');
        }

        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('is-open');
            if (overlay) overlay.classList.toggle('is-open');
        });

        if (overlay) overlay.addEventListener('click', close);

        sidebar.addEventListener('click', function (e) {
            if (e.target.closest('a')) close();
        });
    }

    /* ---------- Dashboard anchor nav + scrollspy ---------- */
    function initDashNav() {
        var links = Array.prototype.slice.call(document.querySelectorAll('.dash-nav .dash-link[href^="#"]'));
        if (!links.length) return;

        var sections = links
            .map(function (link) { return document.getElementById(link.getAttribute('href').slice(1)); })
            .filter(Boolean);

        function setActive(id) {
            links.forEach(function (link) {
                link.classList.toggle('is-active', link.getAttribute('href') === '#' + id);
            });
        }

        links.forEach(function (link) {
            link.addEventListener('click', function (e) {
                var id = link.getAttribute('href').slice(1);
                var target = document.getElementById(id);
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setActive(id);
                if (history.replaceState) history.replaceState(null, '', '#' + id);
            });
        });

        if (!('IntersectionObserver' in window)) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) setActive(entry.target.id);
            });
        }, { rootMargin: '-40% 0px -50% 0px', threshold: 0 });

        sections.forEach(function (section) { observer.observe(section); });
    }

    /* ---------- Seller city -> area filtering ---------- */
    function initAreaFilter() {
        var citySelect = document.getElementById('sellerCity');
        var areaSelect = document.getElementById('sellerArea');
        if (!citySelect || !areaSelect) return;

        var allOptions = Array.prototype.slice.call(areaSelect.querySelectorAll('option[data-city]'));

        function filter() {
            var city = citySelect.value;
            var current = areaSelect.value;

            allOptions.forEach(function (option) {
                var match = !city || option.getAttribute('data-city') === city;
                option.hidden = !match;
                option.disabled = !match;
            });

            if (current) {
                var selected = allOptions.find(function (o) { return o.value === current; });
                if (selected && selected.hidden) areaSelect.value = '';
            }
        }

        citySelect.addEventListener('change', filter);
        filter();
    }
})();
