/* global axiosRequest, window */

const Homepage = {
    init() {
        const sections = this.detectSections();

        if (!sections.length) {
            return;
        }

        axiosRequest.post('/ajax/homepage', { sections }, {
            onSuccess: (response) => {
                if (sections.includes('sliders')) {
                    document.getElementById('hp-sliders').innerHTML = response.sliders || '';
                }

                if (sections.includes('payments')) {
                    document.getElementById('hp-payments').innerHTML = response.payments || '';
                }

                if (sections.includes('categories')) {
                    document.getElementById('hp-categories').innerHTML = response.categories || '';
                }

                if (sections.includes('campaigns')) {
                    document.getElementById('hp-campaigns').innerHTML = response.campaigns || '';
                }

                if (sections.includes('brands')) {
                    document.getElementById('hp-brands').innerHTML = response.brands || '';
                }

                if (sections.includes('blocks')) {
                    document.getElementById('hp-blocks').innerHTML = response.blocks || '';
                }

                this.initSliders();
            },
        });
    },

    detectSections() {
        return Array.from(document.querySelectorAll('[data-hp]'))
            .map((element) => element.dataset.hp);
    },

    initSliders() {
        if (document.querySelector('.theme-slider')) {
            window.initGeneralSliders?.();
        }
        if (document.querySelector('.category-slider')) {
            window.initCategorySliders?.();
        }
        if (document.querySelector('.campaign-slider')) {
            window.initCampaignSliders?.();
        }
        if (document.querySelector('.brand-slider')) {
            window.initBrandSliders?.();
        }
        if (document.querySelector('.product-box-slider')) {
            window.initProductSliders?.();
        }
    },
};

window.Homepage = Homepage;

