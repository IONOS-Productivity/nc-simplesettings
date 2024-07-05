/// <reference types="@nextcloud/typings" />

declare namespace Nextcloud29WithPolyfills {
	interface DialogsPolyfill {
		confirm(
	        title: string,
	        message: string,
	        callback: () => void,
	        modal: boolean): void;
	}

    interface OC extends Nextcloud.v29.OC {
		dialogs: Nextcloud.Common.Dialogs & DialogsPolyfill;
    }
}

// eslint-disable-next-line no-var
declare var OC: Nextcloud29WithPolyfills
