# balance by BEQN

Buchhaltungs- und Rechnungssystem fuer kleine Unternehmen, gebaut mit **Laravel 12**, **Filament v5** und **MySQL**.

## Features

- **Kundenverwaltung** -- Kontaktdaten, Ansprechpartner, Zahlungsziel, Kundenrabatt, Notizen
- **Lieferantenverwaltung** -- Kontaktdaten, Adresse, Zahlungsziel
- **Artikelverwaltung** -- Kategorien (Many-to-Many), Brutto/Netto-Rechner, MwSt-Saetze
- **Angebote** -- Positionen, Rabatt (pro Kunde konfigurierbar), Live-Summenberechnung, MwSt-Aufschluesselung
- **Lieferscheine** -- Positionen mit Mengen, Preisen, MwSt
- **Ausgangsrechnungen** -- Positionen, automatische Rechnungsnummern
- **Eingangsrechnungen** -- Lieferantenrechnungen mit Netto/MwSt/Brutto
- **Nummernkreise** -- Flexibles Platzhalter-Format (`RE-{jjjj}-{jz,4}`) mit Jahres-/Monats-/Tageszaehlern
- **Firmenstammdaten** -- Logo, Bankverbindung, Steuerdaten
- **Benutzer- & Rechteverwaltung** -- Flexible Rollen mit CRUD-Berechtigungen pro Bereich, geschuetzter Super-Admin

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
│   ├── Pages/
│   │   └── CompanySettings.php       # Firmenstammdaten & Nummernkreise
│   ├── Resources/
│   │   ├── Articles/                 # Artikel-Verwaltung
│   │   ├── Categories/               # Artikel-Kategorien
│   │   ├── Customers/                # Kunden-Verwaltung (Tabs)
│   │   ├── DeliveryNotes/            # Lieferscheine
│   │   ├── IncomingInvoices/         # Eingangsrechnungen
│   │   ├── Invoices/                 # Ausgangsrechnungen
│   │   ├── Quotes/                   # Angebote (mit Summen)
│   │   ├── Roles/                    # Rollen-Verwaltung
│   │   ├── Suppliers/                # Lieferanten-Verwaltung
│   │   └── Users/                    # Benutzer-Verwaltung
│   └── Widgets/
│       └── BalanceStatsOverview.php   # Dashboard-Kennzahlen
├── Models/
│   ├── Article.php
│   ├── Category.php
│   ├── CompanySetting.php
│   ├── ContactPerson.php
│   ├── Customer.php
│   ├── DeliveryNote.php / DeliveryNoteItem.php
│   ├── IncomingInvoice.php
│   ├── Invoice.php / InvoiceItem.php
│   ├── NumberRange.php
│   ├── Quote.php / QuoteItem.php
│   ├── Role.php
│   ├── Supplier.php
│   └── User.php
├── Policies/                          # Zugriffssteuerung pro Resource
│   ├── ArticlePolicy.php
│   ├── CategoryPolicy.php
│   ├── CustomerPolicy.php
│   ├── DeliveryNotePolicy.php
│   ├── IncomingInvoicePolicy.php
│   ├── InvoicePolicy.php
│   ├── QuotePolicy.php
│   ├── RolePolicy.php
│   ├── SupplierPolicy.php
│   └── UserPolicy.php
└── Services/
    └── NumberFormatService.php        # Nummernkreis-Formatierung
```

## Datenmodell

```
roles ──< users
customers ──< contact_persons
customers ──< invoices ──< invoice_items >── articles
customers ──< quotes ──< quote_items >── articles
customers ──< delivery_notes ──< delivery_note_items >── articles
suppliers ──< incoming_invoices
articles >──< categories (Pivot: article_category)
```

## Benutzer- & Rechteverwaltung

Das System verwendet flexible Rollen mit CRUD-Berechtigungen:

**Bereiche:**
Kunden, Lieferanten, Artikel, Kategorien, Rechnungen, Eingangsrechnungen, Angebote, Lieferscheine, Einstellungen, Benutzer & Rollen

**Berechtigungen pro Bereich:** Anzeigen, Erstellen, Bearbeiten, Loeschen

**Vorkonfigurierte Rollen (Seeder):**

| Rolle | Zugriff |
|---|---|
| Administrator | Voller Zugriff (Super-Admin) |
| Buchhalter | Alles ausser Benutzer/Rollen-Verwaltung |
| Mitarbeiter | Nur Lesen bei Kunden, Artikeln, Rechnungswesen |

**Sicherheit:**
- Geschuetzter Super-Admin (nicht loeschbar)
- Benutzer ohne Rolle haben keinen Zugriff
- Super-Admin-Rolle kann nicht geloescht werden
- Benutzer koennen sich nicht selbst loeschen

## Filament Admin-Panel

Das Admin-Panel ist unter `/admin` erreichbar.

**Navigation:**
- **Kontakte** -- Kunden (mit Ansprechpartnern), Lieferanten
- **Artikel** -- Artikel-Stammdaten (mit Kategorien, Brutto/Netto-Rechner)
- **Rechnungswesen** -- Rechnungen, Angebote, Lieferscheine, Eingangsrechnungen
- **Einstellungen** -- Firmenstammdaten, Nummernkreise, Kategorien, Benutzer, Rollen

**Dashboard-Widgets:**
- Offene Rechnungen (Anzahl + Summe)
- Offene Eingangsrechnungen (Anzahl + Summe)
- Einnahmen / Ausgaben / Gewinn

## Nummernkreise

Flexibles Platzhalter-System fuer automatische Dokumentnummern:

| Platzhalter | Beschreibung | Beispiel |
|---|---|---|
| `{jjjj}` | Jahr 4-stellig | 2026 |
| `{jj}` | Jahr 2-stellig | 26 |
| `{mm}` | Monat mit Null | 02 |
| `{z,4}` | Globalzaehler 4-stellig | 0042 |
| `{jz,4}` | Jahreszaehler 4-stellig | 0001 |

**Beispiel:** `RE-{jjjj}-{jz,4}` ergibt `RE-2026-0001`

---

## Lokale Entwicklungsumgebung einrichten

### Voraussetzungen

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installiert und gestartet

### Schritt-fuer-Schritt

```bash
# 1. Repo klonen
git clone <repo-url> balance
cd balance

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
#    APP_NAME="balance by BEQN"
#    DB_CONNECTION=mysql
#    DB_HOST=mysql

# 4. Sail starten
./vendor/bin/sail up -d

# 5. App-Key generieren
./vendor/bin/sail artisan key:generate

# 6. Datenbank migrieren + Testdaten laden
./vendor/bin/sail artisan migrate --seed

# 7. Fertig -- im Browser oeffnen
open http://localhost/admin
```

### Login-Daten (Testumgebung)

| Feld | Wert |
|---|---|
| E-Mail | `test@example.com` |
| Passwort | `password` |
| Rolle | Administrator (Super-Admin) |

### Nuetzliche Sail-Befehle

```bash
./vendor/bin/sail up -d                       # Starten
./vendor/bin/sail down                        # Stoppen
./vendor/bin/sail artisan migrate:fresh --seed # DB komplett neu
./vendor/bin/sail artisan test                # Tests ausfuehren
./vendor/bin/sail mysql                       # MySQL-Shell
```

---

## Tests

```bash
# Alle Tests ausfuehren
./vendor/bin/sail artisan test

# Nur Balance-Tests
./vendor/bin/sail artisan test --filter=BalanceTest
```

Aktuell 69 Tests mit 139 Assertions:
- Model-CRUD-Tests
- Beziehungs-Tests
- Berechnungs-Tests (Summen, Rabatte, MwSt)
- Nummernkreis-Tests (Formatierung, Zaehler, Reset)
- Seitenzugriffs-Tests
- Rollen- & Berechtigungs-Tests

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
git clone <repo-url> /var/www/balance
cd /var/www/balance

# 2. Dependencies installieren (ohne Dev-Pakete)
composer install --no-dev --optimize-autoloader

# 3. Frontend-Assets bauen
npm ci && npm run build

# 4. .env konfigurieren
cp .env.example .env
```

Dann `.env` fuer Produktion anpassen:

```env
APP_NAME="balance by BEQN"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://deine-domain.de
APP_TIMEZONE=Europe/Berlin

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=balance
DB_USERNAME=balance_user
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
#    Danach in der DB is_super_admin auf true setzen

# 9. Berechtigungen setzen
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Updates deployen

```bash
cd /var/www/balance
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components
php artisan up
```

---

## Wichtige Hinweise

- **Dokumentnummern** werden automatisch ueber konfigurierbare Nummernkreise vergeben
- **Auf Produktion niemals** `migrate:fresh` oder `--seed` verwenden -- das loescht alle Daten!
- **Backups**: Regelmaessig die MySQL-Datenbank sichern (`mysqldump`)
- **APP_DEBUG=false** auf Produktion -- sonst werden sensible Infos angezeigt
- **Super-Admin**: Mindestens ein Super-Admin muss immer existieren
