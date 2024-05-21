import path from 'node:path'
import { createAppConfig } from '@nextcloud/vite-config'

// See
// - https://github.com/nextcloud-libraries/nextcloud-vite-config/tree/v2.0.0
// - https://github.com/nextcloud-libraries/nextcloud-vite-config/blob/v2.0.0/lib/appConfig.ts
export default createAppConfig({
    // entry points: {name: script}
    main: path.resolve(__dirname, 'src/main.ts'),
}, {
    inlineCSS: true,
    config: () => ({
      build: {
        // The dist filename /js/main.js is decided by
        // - folder: nextcloud:lib/public/Util::addScript() (assumed to be in /js/)
        // - filename: app:/template/mindex.php (explicitly passed to addScript())
        outDir: 'js',
        rollupOptions: {
          output: {
             entryFileNames: () => 'main.js',
          }
        }
      }
    })
})
