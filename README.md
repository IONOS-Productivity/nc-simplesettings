# Simple Settings

Minimal settings for Nextcloud.


## Build

```
$ npm run build
```


## Translation workflow

1. Search for translations and create `.pot` file using `translationtool.phar create-pot-files`

   ```bash
   simplesettings$ php /opt/translationtool/translationtool.phar create-pot-files
   ```

2. Upload `.pot` in translation tool
3. Translate missing or changed texts
4. Download `.po` files as `translationfiles/<language code>/simplesettings.po`

> Be careful not to download all the files at once, as this can cause problems with plural forms.
5. Generate `.json` and `.js` files from `.po` files

   ```bash
   simplesettings$ php /opt/translationtool/translationtool.phar convert-po-files
   ```

6. Rebuild the frontend application

   ```bash
   simplesettings$ npm run build
   ```
