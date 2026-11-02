# Deploying CDS World (cPanel, shared account `websgene`)

Rules: this app lives only in `/home/websgene/cds-app`. Never touch other apps' folders, DBs, or crontab lines.
Disk on `/home` is ~96% full: keep uploads small; videos should be YouTube/Facebook embeds.

## One-time setup (staging first)
1. cPanel > **MySQL Databases**: create a NEW database + user (e.g. `websgene_cds` / `websgene_cdsu`), grant ALL on that DB only.
2. cPanel > **Subdomains**: `staging.cdsworld.org` with document root `/home/websgene/cds-app/public`.
   cPanel > **MultiPHP Manager**: set that domain to PHP 8.3. Enable AutoSSL.
3. SSH: `cd ~ && git clone git@github.com:hussainsad-git/cds-world.git cds-app` (same SSH-key/deploy-key method as your other repos).
4. `cd ~/cds-app && cp deploy/.env.production.example .env` then edit `.env` (DB_*, APP_URL, SEED_ADMIN_EMAIL, mail). `chmod 600 .env`.
5. `PHP=/opt/cpanel/ea-php83/root/usr/bin/php`
   `COMPOSER_MEMORY_LIMIT=-1 $PHP /usr/local/bin/composer install --no-dev --optimize-autoloader`
   `$PHP artisan key:generate --force`
   `$PHP artisan migrate --force && $PHP artisan db:seed --force`   (prints the admin password ONCE; save it)
   `$PHP artisan shield:generate --all --panel=admin --ignore-existing-policies`
   `$PHP artisan storage:link && chmod -R ug+rwX storage bootstrap/cache`
   then run `bash deploy/deploy.sh` once to warm the caches.
6. `crontab -e` and ADD (do not remove the existing leadflow lines):
   `* * * * * cd /home/websgene/cds-app && /opt/cpanel/ea-php83/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1`
   `* * * * * cd /home/websgene/cds-app && /opt/cpanel/ea-php83/root/usr/bin/php artisan queue:work database --stop-when-empty --max-time=50 --tries=1 --memory=128 >> /home/websgene/cds-app/storage/logs/worker.log 2>&1`
7. Open `https://staging.cdsworld.org/admin`, log in, change the password.

## Every update
`ssh` in, then `bash ~/cds-app/deploy/deploy.sh` (pull, composer, migrate, caches, queue restart; maintenance mode on/off automatically).

## Cutover to cdsworld.org (after staging is verified)
1. Targeted backup (NOT a full-account backup, the disk is nearly full):
   `tar czf ~/db-backups/cdsworld-static-$(date +%F).tgz -C ~ cdsworld.org` and a `mysqldump` of any CDS database; copy off-server with rclone.
2. Set `.env` `APP_URL=https://cdsworld.org`, then `php artisan config:cache`.
3. cPanel > **Domains**: change cdsworld.org document root from `/home/websgene/cdsworld.org` to `/home/websgene/cds-app/public`. Keep the old folder untouched.
4. Smoke test: `/`, `/articles`, `/events`, `/about`, `/admin`. **Rollback** = set the document root back (instant).
