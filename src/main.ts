import Vue from 'vue'
import App from './App.vue'
// Import without use to prevent odd Webpack resolve error
//    ERROR in ./src/App.vue?vue&type=script&lang=ts (./node_modules/babel-loader/lib/index.js!./node_modules/ts-loader/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/App.vue?vue&type=script&lang=ts) 29:0-31
//    Module not found: Error: Can't resolve '../logger' in '/app/src'
// eslint-disable-next-line
import { useAuthTokenStore } from './store/authtoken'

declare function n(a: string): string;
declare function t(a: string): string;

Vue.mixin({ methods: { t, n } })

const View = Vue.extend(App)
new View().$mount('#simplesettings')
