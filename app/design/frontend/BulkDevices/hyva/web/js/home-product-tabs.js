/**
 * Featured Products tab switcher — registered before Alpine inits the section.
 */
(function () {
    'use strict';

    window.initHomePromotionalTabs = function initHomePromotionalTabs(config) {
        config = config || {};

        return {
            activeTab: config.defaultTabId || 'hard-drive',
            loading: false,
            panels: {},
            gridUrl: config.gridUrl || '',
            productCount: config.productCount || 10,
            defaultTabId: config.defaultTabId || 'hard-drive',
            init() {},
            selectTab(tabId, categoryId) {
                if (this.activeTab === tabId) {
                    return;
                }
                this.activeTab = tabId;
                if (tabId === this.defaultTabId || this.panels[tabId]) {
                    return;
                }
                this.loading = true;
                const url = new URL(this.gridUrl, window.location.origin);
                url.searchParams.set('category_id', String(categoryId));
                url.searchParams.set('product_count', String(this.productCount));
                fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Failed to load products');
                        }
                        return response.text();
                    })
                    .then((html) => {
                        this.panels[tabId] = html;
                    })
                    .catch(() => {
                        this.panels[tabId] =
                            '<p class="home-product-tabs__empty">' +
                            (config.errorMessage || 'Unable to load products. Please try again.') +
                            '</p>';
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },
        };
    };
})();
