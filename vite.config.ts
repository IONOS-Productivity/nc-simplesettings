import { createAppConfig } from '@nextcloud/vite-config'
import { existsSync } from 'node:fs'
import { join, resolve } from 'path'
import { fileURLToPath } from 'url'

const __dirname = fileURLToPath(new URL('.', import.meta.url))

// Nextcloud's core SCSS variables live in the server repo. An app always sits
// two levels below the server root - <server-root>/<app-dir>/simplesettings -
// whatever the app directory is called. Standalone checkouts (CI) have no
// server tree above them and fall back to the bundled mirror.
const serverCoreVariables = resolve(__dirname, '..', '..', 'core', 'css', 'variables.scss')
const hasServerCoreVariables = existsSync(serverCoreVariables)

if (!hasServerCoreVariables) {
	console.warn(
		`[simplesettings] No Nextcloud core variables at ${serverCoreVariables} - `
		+ 'falling back to the bundled mirror at src/css/nc-core-variables.scss. '
		+ 'Values may drift from core; this is expected for standalone '
		+ 'checkouts such as CI.',
	)
}

const coreVariables = hasServerCoreVariables
	? serverCoreVariables
	: resolve(__dirname, 'src', 'css', 'nc-core-variables.scss')

export default createAppConfig(
	{
		main: resolve(join(__dirname, 'src', 'main.ts')),
	},
	{
		createEmptyCSSEntryPoints: true,
		extractLicenseInformation: true,
		thirdPartyLicense: false,
		config: {
			resolve: {
				alias: {
					'@nc-core-variables': coreVariables,
				},
			},
		},
	},
)
