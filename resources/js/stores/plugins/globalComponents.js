import { defineAsyncComponent } from 'vue';

export default function registerGlobalComponents(app) {
    app.component(
        'MetaTags',
        defineAsyncComponent(() => import('@theme/cores/MetaTags.vue'))
    );

    app.component(
        'LocalizedLink',
        defineAsyncComponent(() => import('@theme/cores/LocalizedLink.vue'))
    );

    app.component(
        'Loading',
        defineAsyncComponent(() => import('@theme/cores/Loading.vue'))
    );

    app.component(
        'PriceDisplay',
        defineAsyncComponent(() => import('@theme/cores/PriceDisplay.vue'))
    );

    app.component(
        'PriceByCurrencyCode',
        defineAsyncComponent(() => import('@theme/cores/PriceByCurrencyCode.vue'))
    );

    app.component(
        'PriceConverter',
        defineAsyncComponent(() => import('@theme/cores/PriceConverter.vue'))
    );

    app.component(
        'DisplayAddress',
        defineAsyncComponent(() => import('@theme/cores/DisplayAddress.vue'))
    );

    app.component(
        'ActionsNavbar',
        defineAsyncComponent(() => import('@theme/cores/ActionsNavbar.vue'))
    );
}
