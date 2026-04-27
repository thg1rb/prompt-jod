# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 13 application for managing money transfer slips using OCR for automatic data extraction. The app is designed for the Thai market with features for expense analysis, multi-wallet management, and budgeting.

**Tech Stack:**
- Backend: Laravel 13 (PHP 8.3+)
- Frontend: Laravel Blade + Alpine.js
- UI Framework: TailwindCSS v4
- Database: PostgreSQL 16 (via Sail)
- Testing: Pest
- Build Tool: Vite 8
- Runtime: Laravel Sail (Docker) + Bun (JavaScript)

## Development Commands

**Important**: All commands should be run through Laravel Sail with Bun for JavaScript tasks.

### Setup
```bash
sail composer install
sail artisan key:generate
sail artisan migrate
sail bun install
sail bun run build
```

### Development Server
```bash
sail up               # Start all Sail containers
sail artisan serve    # Laravel dev server
sail bun run dev      # Vite dev server with HMR
```

### Testing
```bash
sail artisan test               # Run all tests (Pest)
sail artisan test --filter TestName  # Run specific test
```

### Building
```bash
sail bun run build     # Production build with Vite
```

### Code Quality
```bash
sail artisan pint      # Laravel Pint code formatter
sail artisan boost:update  # Update Laravel Boost AI tools
```

### Common Artisan Commands
```bash
sail artisan migrate               # Run migrations
sail artisan migrate:fresh --seed  # Fresh migration with seeders
sail artisan tinker                # REPL for Laravel
sail artisan queue:work            # Process queue jobs
sail artisan pail                  # View logs in real-time
```

## Architecture

### Core Application Structure

The application follows Laravel's standard MVC architecture with these key patterns:

**Route Structure** (from PROMPT.md):
```
/                       → Redirect → /dashboard
/dashboard              → Dashboard summary + charts
/slips                  → All slips list
/slips/upload           → Upload new slip
/slips/{id}             → Slip details
/transactions           → All transactions
/categories             → Category management
/budgets                → Budget settings
/wallets                → Wallet management
/settings               → Settings (theme, profile, notifications)
```

### Frontend Architecture

**Theme System:**
- Uses TailwindCSS `dark:` variant with `class` strategy
- Theme preference stored in `localStorage`
- Alpine.js store `theme` handles toggle (see `resources/js/app.js`)
- Color tokens defined as CSS variables in `resources/css/app.css`

**Component Organization:**
```
resources/
├── css/app.css               # CSS variables + Tailwind
├── js/app.js                 # Alpine.js init + theme store
├── js/components/            # Reusable Alpine.js components
└── views/
    ├── layouts/
    │   ├── app.blade.php     # Main layout with nav, sidebar, theme
    │   └── auth.blade.php
    └── [feature]/            # Feature-specific views
```

**Shared UI Components** (to be implemented):
- `<AppButton>` - Multiple variants (Primary/Secondary/Danger/Ghost)
- `<AppInput>` - Text input with label, error state, icon
- `<AppModal>` - Dialog/Sheet with mobile swipe-to-dismiss
- `<AppToast>` - Notification toast
- `<AppBadge>` - Status badge
- `<AppCard>` - Surface container
- `<AppSkeleton>` - Loading placeholder
- `<AppChart>` - Chart.js/ApexCharts wrapper
- `<ThemeToggle>` - Light/Dark toggle

### OCR Slip Upload Flow

```
User uploads image
  → Laravel validates (max 10MB, jpeg/png/webp)
  → Send to OCR Service
  → Return extracted fields:
      - amount, datetime, recipient, sender, transaction_id, bank
  → Show Editable Review Form
  → Check for duplicate transaction_id
  → Save with selected wallet and category
  → Auto-categorize via keyword matching
```

### Database Structure

**Database**: PostgreSQL 16 running in Sail container (service: `pgsql`)

**Key Models:**
- `User` - Standard Laravel user
- `Wallet` - Multi-wallet support (Bank, e-Wallet, Cash)
- `Slip` - Uploaded slip images with OCR data
- `Transaction` - Financial transactions (expense/income/adjustment)
- `Category` - Spending categories with auto-categorization rules
- `Budget` - Monthly budget per category

### Responsive Design Strategy

**Breakpoints** (TailwindCSS):
- Mobile: < 640px (base styles)
- Tablet: ≥ 768px (`md:`)
- Desktop: ≥ 1024px (`lg:`)
- Wide Desktop: ≥ 1280px (`xl:`)

**Navigation Pattern:**
- Mobile: Bottom Tab Bar (5 icons)
- Tablet: Collapsible Side Drawer
- Desktop: Fixed Sidebar

## Testing Setup

Uses Pest (modern PHP testing framework) with Laravel plugin.

- Test configuration: `tests/Pest.php`
- Base test case: `Tests\TestCase`
- Feature tests in: `tests/Feature/`
- Unit tests in: `tests/Unit/`

## Key Configuration Files

- `composer.json` - PHP dependencies and scripts
- `package.json` - Node dependencies and Vite scripts
- `vite.config.js` - Vite configuration with Laravel plugin and TailwindCSS
- `tailwind.config.js` - TailwindCSS configuration (ensure `darkMode: 'class'`)
- `phpunit.xml` - Test configuration for Pest
- `compose.yaml` - Sail container configuration (PostgreSQL, Redis, Mailpit)

## Laravel Boost

The project includes `laravel/boost` for AI-assisted development:
- Provides 15+ tools and skills for building Laravel apps
- Run `sail artisan boost:install` if not already installed
- Automatically updated via composer post-update-cmd

## Auto-Categorization System

Categories are auto-assigned using keyword matching on the `recipient` field:
- Rules stored in database for easy editing
- Default categories: Food, Shopping, Transport, Utilities, Entertainment, Health, Finance, Other
- Case-insensitive matching

## Runtime Notes

- PHP/Laravel commands: Use `sail <command>` or `sail artisan <command>`
- JavaScript/Node commands: Use `sail bun <command>`
- All development happens within Sail containers
- Bun is used as the JavaScript runtime and package manager (faster than npm)
