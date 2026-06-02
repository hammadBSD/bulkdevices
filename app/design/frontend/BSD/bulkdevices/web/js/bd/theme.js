(function () {
    'use strict';

    var navToggle = document.getElementById('bd-nav-toggle');
    var mainNav = document.getElementById('bd-main-nav');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            var open = mainNav.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    var megaTrigger = document.getElementById('bd-megamenu-trigger');
    var megaPanel = document.getElementById('bd-megamenu-panel');
    var megaRoot = document.getElementById('bd-megamenu');
    if (megaTrigger && megaPanel && megaRoot) {
        megaTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = !megaPanel.hidden;
            megaPanel.hidden = open;
            megaRoot.classList.toggle('is-open', !open);
            megaTrigger.setAttribute('aria-expanded', open ? 'false' : 'true');
        });

        document.addEventListener('click', function (e) {
            if (!megaRoot.contains(e.target)) {
                megaPanel.hidden = true;
                megaRoot.classList.remove('is-open');
                megaTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    var sidebarItems = document.querySelectorAll('.bd-megamenu__sidebar-item');
    var panels = document.querySelectorAll('.bd-megamenu__panel');
    sidebarItems.forEach(function (item) {
        item.addEventListener('mouseenter', function () {
            var panelId = item.getAttribute('data-panel');
            sidebarItems.forEach(function (el) { el.classList.remove('is-active'); });
            item.classList.add('is-active');
            panels.forEach(function (panel) {
                var active = panel.getAttribute('data-panel') === panelId;
                panel.classList.toggle('is-active', active);
                panel.hidden = !active;
            });
        });
    });
})();
