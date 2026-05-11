import { createApp, type Component } from 'vue';
import ProductSearch from '../components/ProductSearch.vue';
import DataTable from '../components/DataTable.vue';
import Modal from '../components/Modal.vue';
import ServerDataTable from '../components/ServerDataTable.vue';
import ProductsIndex from '../components/ProductsIndex.vue';
import OrdersIndex from '../components/OrdersIndex.vue';
import PaymentsIndex from '../components/PaymentsIndex.vue';
import ModalForm from '../components/ModalForm.vue';
import CategoriesIndex from '../components/CategoriesIndex.vue';
import BrandsIndex from '../components/BrandsIndex.vue';
import AttributeGroupsIndex from '../components/AttributeGroupsIndex.vue';
import AttributesIndex from '../components/AttributesIndex.vue';
import AttributeValuesIndex from '../components/AttributeValuesIndex.vue';
import SlidersIndex from '../components/SlidersIndex.vue';

const registry: Record<string, Component> = {
    'product-search': ProductSearch,
    'data-table': DataTable,
    modal: Modal,
    'server-data-table': ServerDataTable,
    'products-index': ProductsIndex,
    'orders-index': OrdersIndex,
    'payments-index': PaymentsIndex,
    'modal-form': ModalForm,
    'categories-index': CategoriesIndex,
    'brands-index': BrandsIndex,
    'attribute-groups-index': AttributeGroupsIndex,
    'attributes-index': AttributesIndex,
    'attribute-values-index': AttributeValuesIndex,
    'sliders-index': SlidersIndex,
};

const parseProps = (rawProps?: string): Record<string, unknown> => {
    if (!rawProps) {
        return {};
    }

    try {
        const parsed = JSON.parse(rawProps);
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (error) {
        console.warn('[VueMount] Invalid data-props JSON:', rawProps, error);
        return {};
    }
};

export const mountVueComponents = (root: ParentNode = document): void => {
    const elements = root.querySelectorAll<HTMLElement>('[data-vue]');

    elements.forEach((element) => {
        const componentName = element.dataset.vue;

        if (!componentName || element.dataset.vueMounted === '1') {
            return;
        }

        const component = registry[componentName];

        if (!component) {
            console.warn(`[VueMount] Component not registered: ${componentName}`);
            return;
        }

        const props = parseProps(element.dataset.props);
        createApp(component, props).mount(element);
        element.dataset.vueMounted = '1';
    });
};
