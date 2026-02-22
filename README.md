# Buha System

Kleines Buchhaltungssystem, gebaut mit **Laravel 12**, **Filament v5** und **MySQL**.
Verwaltet Kunden, Lieferanten, Artikel, Ausgangsrechnungen und Eingangsrechnungen.

## Tech-Stack

| Komponente | Version |
|---|---|
| PHP | >= 8.2 |
| Laravel | 12.x |
| Filament | 5.x |
| MySQL | 8.x |
| Docker / Sail | Lokale Entwicklung |

## Projektstruktur

```
app/
├── Filament/
│   ├── Resources/
│   │   ├── Articles/          # Artikel-Verwaltung
│   │   ├── Customers/         # Kunden-Verwaltung
│   │   ├── IncomingInvoices/  # Eingangsrechnungen
│   │   ├── Invoices/          # Ausgangsrechnungen
│   │   └── Suppliers/         # Lieferanten-Verwaltung
│   └── Widgets/
│       └── BuhaStatsOverview.php  # Dashboard-Kennzahlen
├── Models/
│   ├── Traits/
│   │   └── HasContactFields.php   # Gemeinsamer Trait fuer Kontakte
│   ├── Article.php
│   ├── Customer.php
│   ├── IncomingInvoice.php
│   ├── Invoice.php
│   ├── InvoiceItem.php
│   └── Supplier.php
└── Providers/Filament/
    └── AdminPanelProvider.php
```

## Datenmodell

```
customers  ──< invoices ──< invoice_items >── articles
suppliers  ──< incoming_invoices
```

- **customers / suppliers**: Getrennte Tabellen, gleiche Felder (Name, Adresse, USt-IdNr., Zahlungsziel)
- **articles**: Artikel-Stammdaten mit Einheit, Nettopreis, MwSt-Satz
- **invoices**: Ausgangsrechnungen mit auto-generierter Nummer (`RE-{Jahr}-{0001}`)
- **invoice_items**: Rechnungspositionen (Menge, Preis, MwSt, Sortierung)
- **incoming_invoices**: Eingangsrechnungen mit Netto/MwSt/Brutto-Betraegen

## Filament Admin-Panel

Das Admin-Panel ist unter `/admin` erreichbar.

**Navigation:**
- **Kontakte** -- Kunden, Lieferanten
- **Artikel** -- Artikel-Stammdaten
- **Rechnungswesen** -- Rechnungen, Eingangsrechnungen

**Dashboard-Widgets:**
- Offene Rechnungen (Anzahl + Summe)
- Offene Eingangsrechnungen (Anzahl + Summe)
- Einnahmen / Ausgaben / Gewinn

---

## Lokale Entwicklungsumgebung einrichten

### Voraussetzungen

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installiert und gestartet

### Schritt-fuer-Schritt

```bash
# 1. Repo klonen
git clone <repo-url> buha-system
cd buha-system

# 2. Composer-Dependencies installieren (einmalig ohne Sail)
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php84-composer:latest \
  composer install --ignore-platform-reqs

# 3. .env vorbereiten
cp .env.example .env
#    Dann in .env sicherstellen:
#    APP_NAME="Buha System"
#    APP_LOCALE=de
#    APP_FALLBACK_LOCALE=de
#    APP_FAKER_LOCALE=de_DE
#    APP_TIMEZONE=Europe/Berlin
#    DB_CONNECTION=mysql
#    DB_HOST=mysql
#    DB_DATABASE=laravel
#    DB_USERNAME=sail
#    DB_PASSWORD=password

# 4. Sail starten
./vendor/bin/sail up -d

# 5. App-Key generieren
./vendor/bin/sail artisan key:generate

# 6. Datenbank migrieren + Testdaten laden
./vendor/bin/sail artisan migrate --seed

# 7. Admin-User anlegen (falls nicht per Seeder)
#    Der Seeder legt bereits an: test@example.com / password
#    Fuer einen eigenen User:
./vendor/bin/sail artisan make:filament-user

# 8. Fertig -- im Browser oeffnen
open http://localhost/admin
```

### Login-Daten (Testumgebung)

| Feld | Wert |
|---|---|
| E-Mail | `test@example.com` |
| Passwort | `password` |

### Nuetzliche Sail-Befehle

```bash
# Sail starten / stoppen
./vendor/bin/sail up -d
./vendor/bin/sail down

# Artisan-Befehle
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh --seed   # DB komplett neu aufsetzen
./vendor/bin/sail artisan tinker

# Composer
./vendor/bin/sail composer require paket/name

# Logs anschauen
./vendor/bin/sail artisan pail

# MySQL-Shell
./vendor/bin/sail mysql
```

---

## Produktionsumgebung einrichten

### Voraussetzungen Server

- PHP >= 8.2 mit Extensions: `mbstring`, `xml`, `bcmath`, `mysql`, `gd`, `intl`, `zip`
- MySQL >= 8.0 (oder MariaDB >= 10.6)
- Composer >= 2.x
- Nginx oder Apache
- Node.js >= 18 + npm (nur fuer Build)
- SSL-Zertifikat (z.B. Let's Encrypt)

### Deployment-Schritte

```bash
# 1. Code auf den Server bringen
git clone <repo-url> /var/www/buha-system
cd /var/www/buha-system

# 2. Dependencies installieren (ohne Dev-Pakete)
composer install --no-dev --optimize-autoloader

# 3. Frontend-Assets bauen
npm ci && npm run build

# 4. .env konfigurieren
cp .env.example .env
```

Dann `.env` fuer Produktion anpassen:

```env
APP_NAME="Buha System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://deine-domain.de
APP_TIMEZONE=Europe/Berlin

APP_LOCALE=de
APP_FALLBACK_LOCALE=de
APP_FAKER_LOCALE=de_DE

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=buha_system
DB_USERNAME=buha_user
DB_PASSWORD=<sicheres-passwort>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

```bash
# 5. App-Key generieren
php artisan key:generate

# 6. Datenbank migrieren (OHNE --seed auf Produktion!)
php artisan migrate --force

# 7. Caches aufwaermen
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components

# 8. Admin-User anlegen
php artisan make:filament-user
#    --> Name, E-Mail und Passwort eingeben

# 9. Berechtigungen setzen
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Nginx-Konfiguration (Beispiel)

```nginx
server {
    listen 80;
    server_name deine-domain.de;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name deine-domain.de;

    root /var/www/buha-system/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/deine-domain.de/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/deine-domain.de/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Updates deployen

```bash
cd /var/www/buha-system

# Wartungsmodus aktivieren
php artisan down

# Code aktualisieren
git pull origin main

# Dependencies aktualisieren
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Datenbank migrieren
php artisan migrate --force

# Caches neu aufbauen
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components

# Wartungsmodus deaktivieren
php artisan up
```

---

## Wichtige Hinweise

- **Rechnungsnummern** werden automatisch im Format `RE-{Jahr}-{0001}` vergeben und sind pro Jahr sequentiell
- **Auf Produktion niemals** `migrate:fresh` oder `--seed` verwenden -- das loescht alle Daten!
- **Backups**: Regelmaessig die MySQL-Datenbank sichern (`mysqldump`)
- **APP_DEBUG=false** auf Produktion -- sonst werden sensible Infos angezeigt
