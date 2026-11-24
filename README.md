# DocMGR

A PHP/PostgreSQL based document management system (DMS) with full-text searching, keyword support, file discussions, document workflow, task management, WebDAV support, and file locking.

## Fork information

This is a fork of [DocMGR on SourceForge](https://sourceforge.net/projects/docmgr/), which has been unmaintained since 2007. The fork only keeps the application running on current PHP and PostgreSQL versions. It adds no new features.

Tested with **PHP 8.5** (PHP-FPM) and **PostgreSQL 18** on Ubuntu 26.04.

## Changes since the fork

### 2021: PHP 7.4 / PostgreSQL 12
- Reorganised the SVN import into the `docmgr/` directory.
- Removed `get_magic_quotes_gpc()` checks (the function was removed in PHP 8).
- Guarded `count()` on non-arrays, `strstr()` on null paths, and `session_id()` warnings.
- Quoted the event names passed to `logEvent()`, which were undefined constants.

### 2024: PHP 8.1
- Guarded array accesses that are now warnings (`array_key_exists`, `isset`).
- Added defaults for the `USER_ID`/`USER_FN`/`USER_LN` constants when no user is logged in.
- Quoted the undefined constants used in messages and event types (`_HOME`, `OBJ_CREATED`, …).

### 2025: PHP 8.3 / PostgreSQL 16
- Replaced truthiness checks on `$_SESSION`, `$_COOKIE` and API data with `isset()`/`array_key_exists()`. A missing key used to be a warning and could break authentication.

### 2026: PHP 8.5 / PostgreSQL 18
- Replaced the backtick shell operator with `shell_exec()`; the commands are unchanged.
- Made nullable parameter types explicit (`?Type $x = null`) in the bundled sabredav/vobject.
- Removed defaults on optional parameters declared before required ones (phpmailer `POP3::Authorise()`, `showModTableAlt()`); they were already treated as required.
- Changed `(integer)` casts to `(int)` (phpmailer).
- `apilib/lib/docmgr/workflow.php`: a `break` outside a loop was a fatal compile error, so the whole file could not load. It is now `return false`.
- `lib/odt/odt.php`: removed call-time pass-by-reference (`f(&$x)`), a parse error since PHP 7.
- `apilib/apirequest.php`: an API request with no body no longer fails with a `TypeError` (HTTP 500).
- Declared the `CONFIG::$CDB` property (dynamic properties are deprecated) and stopped passing `null` to `strip_tags()`/`json_decode()`.
- sabredav: `continue` inside a `switch` became `break` (same behaviour); removed `final` from a private method.
- `app/common-docmgr.php`: fixed the reversed `EMAIL_SUPPORT` check from the 2024 commit (it was turned on when email was *not* detected); external-app checks now use `!empty()`, which removes the `Undefined array key "imagemagick"` warnings.
- Upgraded the bundled PHPMailer from 5.2.2 (2012, known CVEs including CVE-2016-10033) to **7.2.0**. Only `src/` and `LICENSE` are kept in `lib/phpmailer/`. `lib/sendemail.php` was adapted: namespaced classes, `getSentMIMEMessage()` instead of the now-protected `MIMEHeader`/`MIMEBody`, UTF-8 charset, and no undefined `$name`/`cid` warnings.
- Upgraded CKEditor from 4.1.1 (2013) to **4.22.1**, the last open-source CKEditor 4 release, which includes about 10 years of XSS fixes. Kept docmgr's `config.js` (toolbars, `CustomImage` dialog) and `stylesheet.css`, dropped `samples/`, and set `versionCheck = false` to disable the end-of-life banner.
- Removed unused demo/test code: `sabredav/{examples,docs,tests,fileserver.php}`, `jslib/datepicker/Test`.
- `count()` on `null` is a fatal `TypeError` since PHP 8 (for example, "add filter" in search returned HTTP 500). Every `count()` on a variable in docmgr's code now uses `count((array) $x)`, which keeps the PHP 7 result (`null` → 0). The two `SimpleXMLElement` counts in `lib/xml.php` and `lib/libxml.php` are unchanged.
- Bumped `JS_VERSION` in `config/version.php` so browsers reload the upgraded JavaScript instead of the cached 2013 copies.
- `bin/docmgr-indexer.php`: `debugMsg()` read the undefined `DEBUG` constant, a fatal error since PHP 8, so the indexer died on its first message. Full-text indexing and thumbnails had silently stopped, and uploads just piled up in `docmgr.dm_index_queue`.
- `apilib/lib/docmgr/objindex.php`: `removeTags()` and `clean()` are called statically from other classes (`DOCMGR_UTIL_FILEINDEX`, `DOCMGR_DOCUMENT`, `DOCMGR_URL`), which is a fatal error since PHP 8. The indexer died on the first `.doc`/OpenOffice file. Both are now `static`. A dead `$cleanTags` branch in `clean()` (an undefined variable, never true) was removed.
- `app/openoffice.php`: when the office-document conversion produces no file, `ZipArchive::open('')` throws a `ValueError` in PHP 8, which killed the indexer. It now returns no content, so the document is indexed by name only.

## Installation notes

For a fresh installation, follow **[INSTALL.md](INSTALL.md)**. The notes below are a summary for existing sites.

- The site-specific `docmgr/config/config.php` and `docmgr/config/app-config.php` are **not** in this repository. Create them from `docmgr/config/config.php.dist` and `app-config.php.dist` (or let the web installer write them); `.gitignore` keeps them out of the repository.
- Deploy the `docmgr/` directory to the web root, excluding `files/` (the document store) and the two config files, for example:

  ```sh
  rsync -a --exclude=/files --exclude=/config/config.php --exclude=/config/app-config.php docmgr/ /path/to/webroot/
  ```
- Apache: send `*.php` to PHP-FPM (`SetHandler "proxy:unix:/run/php/phpX.Y-fpm.sock|fcgi://localhost"`). Update the socket path after each PHP upgrade.
- PHP extensions: `pgsql`, `mbstring`, `gd`, `xml`, `zip`, `imagick`.
- External tools: `poppler-utils` (`pdftotext`, `pdfimages`) for PDF text indexing; ImageMagick (`convert`, `mogrify`, `montage`) and Ghostscript for thumbnails; `zip`; optionally `ocrad`/`tesseract` (OCR) and LibreOffice (office documents). docmgr detects them at login and silently skips what is missing.
- Ubuntu 26.04 confines Ghostscript with an AppArmor profile, so ImageMagick cannot read the document store and thumbnails fail with `Permission denied`. Allow it in `/etc/apparmor.d/local/gs`, then run `apparmor_parser -r /etc/apparmor.d/gs`:
  ```
  /path/to/files/** r,
  ```
- After an upload the indexer runs in the background. To process the queue or a single object by hand, run from the web root as the web server user:
  ```sh
  php bin/docmgr-indexer.php                     # process docmgr.dm_index_queue
  php bin/docmgr-indexer.php --index-object 123  # one object: text index + thumbnails
  ```
- After changing any JavaScript or CSS (including a CKEditor upgrade), bump `JS_VERSION` in `docmgr/config/version.php`. It is the `?v=` cache-busting parameter on every script and stylesheet, so without a bump browsers keep serving stale files.

To check compatibility after a PHP upgrade, lint every file. PHP reports compile-time deprecations only once per file, so the error log alone misses most of them:

```sh
find docmgr -name '*.php' -print0 | xargs -0 -I{} php -d error_reporting=-1 -l {} 2>&1 | grep -v '^No syntax errors'
```

## Known issues

- Full-text indexing of `.doc`/`.xls`/`.ppt` files does not work: the bundled `bin/DocumentConverter.py` (Python UNO converter) fails on current LibreOffice. These files are indexed by name only. A fix would be converting with `soffice --headless --convert-to odt` instead.
- Other PHP 8 `TypeError`s may remain on screens not tested yet: built-in array functions (`in_array`, `array_keys`, `implode`, …) called with `null`. The lint does not find these, so they only appear as HTTP 500 errors when a screen is used; check the PHP error log.
- WebDAV (bundled SabreDAV 1.8.1, 2013) is unmaintained and unused. Upgrading means a rewrite for SabreDAV 4, so block it in Apache instead:
  ```apache
  <Location /sabredav>
      Require all denied
  </Location>
  ```
- MooTools 1.4.5 (the UI framework) is outdated, but the project is dead and has no notable security issues, so it is left as is.
- Many `Undefined array key` warnings remain in `lib/arrays.php` and `lib/xml.php`. They clutter the log but don't break anything.
