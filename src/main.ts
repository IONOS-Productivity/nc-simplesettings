// import { translate as t } from '@nextcloud/l10n'
// import Vue from 'vue'
import App from './App.vue'
// import AppScript from './App'
declare function t(a: string): string;
declare function n(a: string): string;
// eslint-disable-next-line no-console
console.log('t', t)
// eslint-disable-next-line no-console
console.log('n', n)
// Vue.mixin({ methods: { t, n } })

// const View = Vue.extend(App)
// new View().$mount('#simplesettings')
// eslint-disable-next-line no-console
console.error('App Vue', App)

// Dummy to mae Webpack happy:
// Error: TypeScript emitted no output for /app/src/main.ts.
export default { }
