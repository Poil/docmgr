# Installing DocMGR

A complete installation of this fork on a fresh server. These steps were checked on **Ubuntu 26.04 LTS with PHP 8.5 (PHP-FPM), Apache 2.4 and PostgreSQL 18**. Other Debian-based systems work the same way, with different package versions.

The examples use these names; replace them with your own:

| Item | Example |
|---|---|
| Site URL | `https://docmgr.example.org/` |
| Web root (the repository's `docmgr/` folder) | `/srv/docmgr/www` |
| Document store (**outside** the web root) | `/srv/docmgr/files` |
| Database / role | `docmgr` / `docmgr` |
| Web server user | `www-data` |

Nothing is missing from the repository: the two site-specific files, `config/config.php` and `config/app-config.php`, are not committed. You create them in step 5 from the `*.dist` examples, or the web installer writes them.

## 1. Install the packages

```sh
sudo apt install apache2 postgresql \
  php8.5-fpm php8.5-cli php8.5-pgsql php8.5-mbstring php8.5-gd php8.5-xml php8.5-zip php8.5-imagick \
  poppler-utils imagemagick ghostscript zip unzip wget \
  ocrad libtiff-tools clamav-daemon \
  libreoffice-core python3-uno
```

| Package | Used for | Needed? |
|---|---|---|
| `php8.5-*` | the application itself | yes |
| `poppler-utils` (`pdftotext`, `pdfimages`, `pdftoppm`) | PDF full-text search | yes |
| `imagemagick`, `ghostscript` | thumbnails and previews | strongly recommended |
| `zip`, `unzip` | ZIP downloads | recommended |
| `wget` | "URL" objects | optional |
| `ocrad`, `libtiff-tools` | OCR of scanned images and TIFF files | optional |
| `clamav-daemon` | virus scan of uploads | optional |
| `libreoffice-core`, `python3-uno` | office document conversion (see Known issues in the README) | optional |
| an MTA providing `sendmail` (e.g. `postfix`) | notifications and sharing by email | optional |

At login, docmgr checks which tools are available and silently skips any that are missing, so a missing tool shows up only as a missing feature (no thumbnails, no full-text search).

## 2. Get the code and create the directories

```sh
sudo mkdir -p /srv/docmgr
sudo git clone https://github.com/Poil/docmgr.git /srv/docmgr/src
sudo rsync -a /srv/docmgr/src/docmgr/ /srv/docmgr/www/
sudo mkdir -p /srv/docmgr/files/{data,document,home,import,preview,thumbnails,tmp}
sudo chown -R www-data:www-data /srv/docmgr/files
sudo chmod 750 /srv/docmgr/files
sudo chown -R www-data: /srv/docmgr/www
```

- **Keep the document store outside the web root.** The installer's default (`[[SITE_PATH]]/files`) puts it inside, where Apache could serve stored documents directly to anyone.
- Only the top folders above need to exist. docmgr creates the `data/<n>/<n>/` sub-folders itself.
- `www-data` must be able to write `files/`. During a wizard install it must also be able to write `www/config/` (step 5B).

## 3. Create the database

Use a dedicated role, not the `postgres` superuser:

```sh
sudo -u postgres createuser --pwprompt docmgr        # choose a password
sudo -u postgres createdb -O docmgr -E UTF8 docmgr
```

**Manual install (5A):** load the schema now, as the new role, so it owns every table:

```sh
psql -h 127.0.0.1 -U docmgr -d docmgr -v ON_ERROR_STOP=1 -f /srv/docmgr/www/install/docmgr.pgsql
```

This creates the 46 tables and one account: **`admin` / password `admin`**. **Wizard install (5B):** skip the load; the wizard does it, and needs the database to exist and be empty.

On Ubuntu, `127.0.0.1` already uses password (scram) authentication in `pg_hba.conf`, so no change is needed there.

## 4. Configure PHP

The PHP defaults cap uploads at **2 MB**, too small for most documents. Create `/etc/php/8.5/fpm/conf.d/99-docmgr.ini`:

```ini
upload_max_filesize = 200M
post_max_size = 210M
memory_limit = 256M
max_execution_time = 300
```

Then run `sudo systemctl restart php8.5-fpm`.

## 5. Create the configuration

### 5A. Manual (recommended)

```sh
cd /srv/docmgr/www/config
sudo cp config.php.dist config.php
sudo cp app-config.php.dist app-config.php
sudo chown www-data: config.php app-config.php && sudo chmod 640 config.php app-config.php
```

Edit `config.php`. These are the settings to check; leave the rest as is:

| Setting | Value |
|---|---|
| `DBHOST`, `DBPORT`, `DBNAME`, `DBUSER`, `DBPASSWORD` | the database from step 3 (use `127.0.0.1` or `localhost`) |
| `SITE_URL` | public URL, with the trailing `/` |
| `SITE_PATH` | the web root, e.g. `/srv/docmgr/www` |
| `FILE_DIR` | the document store, e.g. `/srv/docmgr/files`. The `*_DIR` settings below it derive from it. |
| `ADMIN_EMAIL`, `SMTP_HOST`, `SMTP_PORT`, `SMTP_AUTH*` | your mail relay (only for email features) |
| `TSEARCH2_PROFILE` | full-text search language: `english`, `french`, … (any `\dF` text search configuration) |
| `DEFAULT_LANGUAGE` | interface language (`en`, `fr`, …) |
| `DATE_FORMAT` | e.g. `dd/mm/yyyy` |

**Never define `DEV_MODE` or `DEBUG` in production.** The installer offers them as options; they print SQL and backtraces to visitors.

`app-config.php` holds the commands for the external tools (`APP_PDFTOTEXT`, `APP_CONVERT`, …). The defaults find them in `PATH`; change a line only if a tool lives elsewhere.

### 5B. Web installer (alternative)

The installer is disabled on purpose: `index.php` includes `install/install.php` on **every** request while that file is active. To use it:

1. Delete the first line `return false;` of `www/install/install.php`, and give `www-data` write access to `www/config/` and `www/install/`.
2. Open the site. The wizard runs four steps: requirements check, `config.php` settings (keep `FILE_DIR` outside the web root and leave `DEV_MODE`/`DEBUG` unticked), database schema, then external tool paths (`app-config.php`; set `APP_PYTHON` to `/usr/bin/python3`).
3. **When it finishes, disable it again:** put `return false;` back as the first line of `install/install.php`, or delete the `install/` folder from the web root. Then remove the extra write access from `www/config/` and `www/install/`.

The wizard dates from 2013 and was not exercised on PHP 8.5; the manual path above was.

## 6. Configure Apache

```sh
sudo a2enmod proxy_fcgi setenvif rewrite ssl
```

Create `/etc/apache2/sites-available/docmgr.conf`:

```apache
<VirtualHost *:80>
    ServerName docmgr.example.org
    DocumentRoot /srv/docmgr/www

    <FilesMatch ".+\.ph(ar|p|tml)$">
        SetHandler "proxy:unix:/run/php/php8.5-fpm.sock|fcgi://localhost"
    </FilesMatch>

    <Directory /srv/docmgr/www/>
        Options +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Never serve stored files or internals directly
    <Files "*.docmgr">
        Require all denied
    </Files>
    <Location /install>
        Require all denied
    </Location>
    # Bundled WebDAV (SabreDAV 1.8, 2013) is unmaintained: keep it closed
    <Location /sabredav>
        Require all denied
    </Location>

    ErrorLog ${APACHE_LOG_DIR}/docmgr.error.log
    CustomLog ${APACHE_LOG_DIR}/docmgr.access.log combined
</VirtualHost>
```

If you use the web installer, remove the `/install` block until it has finished.

```sh
sudo a2ensite docmgr && sudo apache2ctl configtest && sudo systemctl reload apache2
sudo apt install certbot python3-certbot-apache && sudo certbot --apache -d docmgr.example.org   # HTTPS
```

After a PHP upgrade, the socket name in `SetHandler` changes (`php8.5-fpm.sock`). Update it, or the site returns 503.

## 7. Allow thumbnails (Ubuntu 26.04 AppArmor)

Ubuntu 26.04 confines Ghostscript with an AppArmor profile, so ImageMagick cannot read the document store and every thumbnail fails with `Permission denied`. Allow the store, read-only:

```sh
echo '/srv/docmgr/files/** r,' | sudo tee /etc/apparmor.d/local/gs
sudo apparmor_parser -r /etc/apparmor.d/gs
```

## 8. First login

1. Open the site and log in as **`admin` / `admin`**.
2. **Change the `admin` password immediately** in its account settings. Passwords are stored as unsalted MD5 hashes, so the default is trivially known.
3. Create your own accounts and folders. Each user gets a `Users/<login>` home folder.

## 9. Indexing and thumbnails

After each upload docmgr queues the object in `docmgr.dm_index_queue` and starts `bin/docmgr-indexer.php` in the background. No cron job is needed. To process the queue or a single object by hand, run from the web root as `www-data`:

```sh
cd /srv/docmgr/www
sudo -u www-data php bin/docmgr-indexer.php                     # whole queue
sudo -u www-data php bin/docmgr-indexer.php --index-object 123  # one object: text + thumbnail + preview
sudo -u postgres psql -d docmgr -c "select count(*) from docmgr.dm_index_queue"   # what's left
```

Full text goes into `docmgr.dm_index.idxfti`. An `idxtext` column that stays empty is normal.

## 10. Importing many files at once

Copy a folder tree into `files/import/` (owned by `www-data`). In docmgr, open the **Import** screen, select the folder and import it. The tree is recreated as docmgr folders.

Keep names free of apostrophes (`'`). Parts of the code build SQL without escaping, and such a name fails to import; use `’` instead.

## 11. Upgrades and maintenance

- **Deploy new code** from the checkout without touching the site files:
  ```sh
  sudo rsync -a --exclude=/files --exclude=/config/config.php --exclude=/config/app-config.php \
    --chown=www-data: /srv/docmgr/src/docmgr/ /srv/docmgr/www/
  sudo systemctl reload php8.5-fpm
  ```
- **After any JavaScript or CSS change**, bump `JS_VERSION` in `config/version.php`. Otherwise browsers keep the cached files.
- **Backups:** `pg_dump -Fc docmgr` **and** the whole `files/` tree. Each is useless without the other: the database holds names, folders and permissions, `files/data` holds the contents (as `<version-id>.docmgr`).
- **PostgreSQL major upgrades:** `pg_upgradecluster <old> main` (dump method) also rebuilds indexes after a glibc collation change.
- **PHP upgrades:** lint every file to catch new deprecations. PHP reports compile-time deprecations only once per file, so the error log alone misses most of them:
  ```sh
  find /srv/docmgr/www -name '*.php' -print0 | xargs -0 -I{} php -d error_reporting=-1 -l {} 2>&1 | grep -v '^No syntax errors'
  ```

## 12. Troubleshooting

| Symptom | Check |
|---|---|
| "Error loading page. Status: 500" | `docmgr.error.log`: a PHP 8 `TypeError`/`ValueError` usually names the file and line |
| Site returns 503 | the PHP-FPM socket in the vhost doesn't exist (PHP version changed) |
| No thumbnails | ImageMagick installed? AppArmor denials: `journalctl -k \| grep 'profile="gs"'` |
| Search doesn't find new documents | `dm_index_queue` not emptying: run the indexer by hand (step 9) and read its output |
| Editor or page broken after an update | stale browser cache: bump `JS_VERSION` |
| Uploads fail for large files | `upload_max_filesize` / `post_max_size` (step 4) |
