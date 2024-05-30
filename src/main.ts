import Vue from 'vue'
import { PiniaVuePlugin, createPinia } from 'pinia'
import App from './App.vue'

declare function n(a: string): string;
declare function t(a: string): string;

const pinia = createPinia()

Vue.use(PiniaVuePlugin)

Vue.mixin({ methods: { t, n } })

const View = Vue.extend(App)
new View({ pinia }).$mount('#simplesettings')
