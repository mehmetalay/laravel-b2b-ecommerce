import '../../../shared/loading';
import '../../../shared/notifications';
import '../../../shared/request-axios';
import { registerBackupHandlers } from './backup';
import { registerCampaignHandlers } from './campaign';
import { registerCartActions } from './cart-actions';
import { registerDiscountHandlers } from './discount';
import { createGiftModalController } from './gift-modal';
import { createCartContext } from './helpers';
import { registerPriceModal } from './price-modal';
import { registerQuantityHandlers } from './quantity';

(function bootstrapCartIndexPage() {
    const pageRoot = document.querySelector('[data-js="cart-index-page"]');
    if (!pageRoot) {
        return;
    }

    const context = createCartContext(pageRoot);
    const giftModalController = createGiftModalController(context);

    registerCartActions(context);
    registerQuantityHandlers(context);
    registerDiscountHandlers(context);
    registerBackupHandlers(context);
    registerCampaignHandlers(context, giftModalController);
    giftModalController.registerEvents();
    registerPriceModal(context);
})();
