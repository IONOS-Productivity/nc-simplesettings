import Vue from 'vue'
import App from './App.vue'

declare function n(a: string): string;
declare function t(a: string): string;

Vue.mixin({ methods: { t, n } })

const View = Vue.extend(App)
new View().$mount('#simplesettings')
