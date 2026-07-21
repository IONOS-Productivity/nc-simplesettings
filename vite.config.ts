import { createAppConfig } from '@nextcloud/vite-config'
import { join, resolve } from 'path'
import { fileURLToPath } from 'url'

const __dirname = fileURLToPath(new URL('.', import.meta.url))

export default createAppConfig(
	{
		main: resolve(join(__dirname, 'src', 'main.ts')),
	},
	{
		createEmptyCSSEntryPoints: true,
		extractLicenseInformation: true,
		thirdPartyLicense: false,
	},
)
