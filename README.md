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

## Installation notes

- The site-specific `docmgr/config/config.php` and `docmgr/config/app-config.php` are **not** in this repository. Create them from the files in `docmgr/install/` and keep them out of deployments.
- Deploy the `docmgr/` directory to the web root, excluding `files/` (the document store) and the two config files, for example:

  ```sh
  rsync -a --exclude=/files --exclude=/config/config.php --exclude=/config/app-config.php docmgr/ /path/to/webroot/
  ```
- Apache: send `*.php` to PHP-FPM (`SetHandler "proxy:unix:/run/php/phpX.Y-fpm.sock|fcgi://localhost"`). Update the socket path after each PHP upgrade.
- PHP extensions: `pgsql`, `mbstring`, `gd`, `xml`, `zip`, `imagick`.

To check compatibility after a PHP upgrade, lint every file. PHP reports compile-time deprecations only once per file, so the error log alone misses most of them:

```sh
find docmgr -name '*.php' -print0 | xargs -0 -I{} php -d error_reporting=-1 -l {} 2>&1 | grep -v '^No syntax errors'
```

## Known issues

- Many `Undefined array key` warnings remain in `lib/arrays.php` and `lib/xml.php`. They clutter the log but don't break anything.
- `lib/phpmailer/extras/htmlfilter.php` and `lib/phpmailer/test_script/index.php` don't parse on modern PHP. The application doesn't use them.
