# 🐳 Laravel Tenancy — Docker Setup
### Stack: Laravel · Inertia.js · Vue 3 · MySQL · Redis · phpMyAdmin · SASS

---

## 📁 File Structure

```
project/
├── docker-compose.yml
├── Makefile
├── .env.example
├── vite.config.js
│
├── docker/
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.ini
│   ├── nginx/
│   │   ├── default.conf        ← main app (localhost)
│   │   └── wildcard.conf       ← tenant subdomains (*.localhost)
│   └── mysql/
│       ├── my.cnf
│       └── init.sql            ← tenant DB creation permission
```

---

## 🚀 First-Time Setup

```bash
# 1. Clone the project
git clone <your-repo> && cd <project>

# 2. Create .env file
cp .env.example .env

# 3. Full setup with a single command
make setup

# Or step by step:
make build          # Build Docker images
make up             # Start containers
make key-gen        # Generate APP_KEY
make migrate        # Run database migrations
make npm-install    # Install Node packages
make npm-dev        # Start Vite dev server
```

---

## 🌐 URLs

| Service | URL |
|---------|-----|
| Laravel App | http://localhost |
| phpMyAdmin | http://localhost:8080 |
| Vite (HMR) | http://localhost:5173 |
| MySQL | localhost:3306 |
| Redis | localhost:6379 |

---

## 🏢 Tenant Subdomains (Local Dev)

Add the following to your `/etc/hosts` file:

```
127.0.0.1   tenant1.localhost
127.0.0.1   tenant2.localhost
```

Then access http://tenant1.localhost in your browser.

---

## 🔑 Database Credentials

| | Value |
|-|-------|
| Host | `mysql` (container) / `localhost` (host) |
| Database | `laravel` |
| Username | `laravel` |
| Password | `secret` |
| Root Password | `rootsecret` |

---

## ⚡ Common Commands

```bash
make shell                       # Bash into app container
make artisan CMD="route:list"    # Run an artisan command
make migrate                     # Run main migrations
make migrate-tenant              # Run tenant migrations
make logs                        # Tail all logs
make cache-clear                 # Clear application cache
make queue-restart               # Restart queue workers
make shell-mysql                 # Open MySQL CLI
make shell-redis                 # Open Redis CLI
```

---

## 📦 Tenancy (stancl/tenancy) Config

In `config/tenancy.php`, make sure you have:

```php
'database' => [
    'prefix' => 'tenant',
    'suffix' => '',
],
```

In `.env`:

```env
TENANCY_DB_HOST=mysql
TENANCY_DB_USERNAME=laravel
TENANCY_DB_PASSWORD=secret
```

> ⚠️ The `init.sql` file grants the `laravel` user `GRANT ALL` on every database,
> so that Tenancy can dynamically create new databases.

---

## 🎨 SASS Structure (Suggested)

```
resources/
└── sass/
    ├── app.scss          ← main entry
    ├── _variables.scss   ← colors, fonts, breakpoints
    ├── _mixins.scss      ← reusable mixins
    └── components/
        └── _button.scss
```

---

## 🏗️ Production Notes

- Set `APP_DEBUG=false`
- Set `APP_ENV=production`
- Build assets with `make npm-build`
- Optimize Laravel with `make optimize`
- Add Nginx SSL config with a wildcard certificate
# sass
