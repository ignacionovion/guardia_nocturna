import { createApp } from 'vue';
import { createPinia } from 'pinia';
import GuardiaLive from './pages/GuardiaLive.vue';
import { useGuardiaStore } from './stores/guardia';

const el = document.getElementById('guardia-live-app');

if (el) {
    const app = createApp(GuardiaLive);
    const pinia = createPinia();

    app.use(pinia);

    // Hydrate store from server-rendered initial state BEFORE mounting
    // so components render with data on first paint (no flash of empty content)
    const store = useGuardiaStore(pinia);
    const initial = window.__GUARDIA_LIVE_INITIAL_STATE__;
    if (initial) {
        store.initFromServer(initial);
    }

    app.mount(el);
}
