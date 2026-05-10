# AGENTS.md — PromptJod

## Project

Thai-market personal finance app. Upload money transfer slip images → OCR via EasySlip API → auto-extract transaction data. Features: multi-wallet management, expense categories with auto-categorization rules, budgeting with alerts, dashboard with charts.

Auth: Google OAuth (Socialite) + standard Breeze email/password.

## Stack

- **PHP 8.3+**, **Laravel 13**
- **SQLite** by default locally (`.env.example`); PostgreSQL 16 available via Sail (`compose.yaml`)
- **TailwindCSS v3** with `darkMode: 'class'` and CSS-variable design system in `resources/css/app.css`
- **Alpine.js v3** — all frontend interactivity (no Livewire, no Inertia)
- **ApexCharts** for dashboard charts
- **Pest v4** for testing
- **Bun** for JS (`bun run build`, `bun run dev`); `.npmrc` sets `ignore-scripts=true`
- **Laravel Boost v2** (MCP tools + skills — already injected into system prompt)

## Commands

```bash
# Dev server (runs serve + queue + pail + vite concurrently)
composer run dev

# Tests
php artisan test --compact
php artisan test --compact --filter=testName

# Formatting (always run after editing PHP)
vendor/bin/pint --dirty --format agent

# Build frontend
bun run build

# Fresh DB
php artisan migrate:fresh --seed
```

**Do not prefix commands with `sail`** — Sail is configured but not enabled (`boost.json` has `"sail": false`). Run commands natively.

## Architecture

### Key Directories

- `app/Services/EasySlipService.php` — OCR slip verification via EasySlip API
- `app/Models/` — User, Wallet, Transaction, Category, CategoryRule, Slip, Budget, BudgetAlert, BalanceAdjustment
- `app/Enums/` — TransactionType, WalletType, SlipStatus, BudgetPeriod, BudgetStatus, AlertType, AlertStatus, VerificationStatus
- `app/Observers/` — TransactionObserver, BudgetObserver, TransactionBudgetObserver
- `app/Http/Requests/` — Form requests for validation (TransactionStoreRequest, SlipVerifyRequest, WalletStoreRequest, etc.)
- `resources/js/components/` — Alpine.js data components: `chart.js`, `categories.js`, `transactions.js`, `transaction-modal.js`

### Routes

All in `routes/web.php` + `routes/auth.php`. No API routes. Key groups:
- `/dashboard` — summary + charts (DashboardController)
- `/wallets` — CRUD + balance adjustment + set-default (WalletController)
- `/categories` — CRUD + data endpoint (CategoryController)
- `/transactions` — CRUD + data/export/verify-slip (TransactionController)
- `/auth/redirect`, `/auth/callback` — Google OAuth

### Frontend

- Alpine stores in `resources/js/app.js`: `theme` (dark mode), `mobileMenu`, `sidebar`, `toast`
- Custom Alpine directive: `x-tooltip`
- Design tokens: HSL CSS variables in `resources/css/app.css` → mapped to Tailwind colors in `tailwind.config.js`
- Fonts: Sarabun (Thai) + Inter
- No component library — all UI is hand-built with Tailwind utilities

### External API

EasySlip (`SLIP_VERIFY_API_KEY`, `SLIP_VERIFY_API_URL` in `.env`):
- POST image → returns amount, date, sender/receiver names, transaction ref
- Parsed in `EasySlipService::parseResponse()`

## Conventions

- Use `php artisan make:` commands to create files. Pass `--no-interaction`.
- Every change must have a corresponding test.
- Run `vendor/bin/pint --dirty --format agent` after modifying PHP files.
- Form requests handle validation (check `app/Http/Requests/` before adding inline validation).
- Models use Observers for lifecycle hooks (not controller-side events).
- Check sibling files for code patterns before creating new files.
