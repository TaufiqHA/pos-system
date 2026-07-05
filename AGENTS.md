# POS System — Agent Guide

This is a **Point of Sale** system built with Laravel 13, PHP 8.4, SQLite, and TailwindCSS v4.

## Quick Start & Dev Commands

- `composer run dev` — runs `php artisan serve`, `queue:listen`, `pail`, and `vite` concurrently
- `composer run test` — runs `config:clear` then `php artisan test`
- `npm run build` — Vite production build (run when frontend changes aren't reflected)
- `npm run dev` — Vite dev server only
- `vendor/bin/pint --format agent` — format all PHP files (run before finalizing)

## Architecture

- **All primary keys are UUID strings** (`$incrementing = false`, `$keyType = 'string'`). Every new model must follow this pattern.
- **Auth**: Session-based via `web` guard. Two roles with middleware aliases: `role.admin` (`AdminMiddleware`) and `role.cabang` (`CabangMiddleware`).
- **Routes**: All in `routes/web.php` (no separate API file). Admin routes under `admin/` prefix with `auth` + `role.admin` middleware. Cabang routes under `cabang/` with `auth` + `role.cabang`.
- **Login page** at `/` shows demo credentials (admin@pos.com / password).
- **AuthController** is JSON-aware — responds with JSON when `expectsJson()`, otherwise redirects.
- **Database**: SQLite for dev (`DB_CONNECTION=sqlite`). Session, queue, and cache all use the database driver.
- **Views**: Blade templates in `resources/views/` with `admin/` and `cabang/` subdirectories. `login.blade.php` is the root view. Layouts in `layouts/`.
- **MCP**: Custom `PosServer` in `app/Mcp/Servers/` with tools (`GetProductInfoTool`), resources (`RecentOrdersResource`, `DashboardApp`), and prompts (`CustomerSupportPrompt`).
- **PHP 8.4 attributes** used on some models: `#[Fillable]`, `#[Hidden]` (User model). Check existing conventions when adding attributes.

## Testing

- **PHPUnit only** (no Pest). Use `php artisan make:test --phpunit {name}`.
- **All feature tests** use `RefreshDatabase`. Auth tests use `actingAs($user)`.
- **Setup pattern**: Feature tests create `Role`, `Branch`, `User` (via `UserFactory`), plus dependent entities in `setUp()`.
- Run a single test: `php artisan test --compact --filter=testName`
- Run a file: `php artisan test --compact tests/Feature/SomeTest.php`
- Only `UserFactory` exists. Create factories alongside new models.

## Entity / Domain Map

| Directory | Purpose |
|---|---|
| `app/Models/` | 16 models: Branch, Category, Product, ProductStock, Purchases, PurchaseItem, PurchasePayment, Sales, SalesItem, SalesPayment, Suppliers, WholesalePrice, Wilayah, User, Role, Customer |
| `app/Http/Controllers/` | 16 controllers, one per entity |
| `app/Http/Middleware/` | AdminMiddleware, CabangMiddleware, AuthCheck |
| `database/migrations/` | 17 migrations, all with UUID string PKs |
| `tests/Feature/` | 14 test files (one per major entity) |
| `tests/Unit/` | 4 test files |
| `resources/views/admin/` | 8 Blade views (dashboard, branch, categories, products, etc.) |
| `resources/views/cabang/` | 1 Blade view (dashboard) |

## Notable Conventions

- All models use `string` UUID primary keys — set `$incrementing = false` and `$keyType = 'string'` in every new model.
- Descriptive method/variable names in Indonesian/English mix (e.g. `is_wholesale`, `monitoring-stock`).
- Controllers use implicit route model binding or manual `findOrFail($id)`.
- JSON endpoints under `admin/` prefix return standard JSON responses with appropriate status codes.
- `planning.md` and `prompt` files are gitignored — use for development notes.

## Active Skills

Loaded via `boost.json` and available in `.agents/skills/`:
- `laravel-best-practices` — for controllers, models, queries, migrations
- `mcp-development` — for MCP tools, resources, prompts, servers
- `tailwindcss-development` — for TailwindCSS v4 styling in Blade templates
