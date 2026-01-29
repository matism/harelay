# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 application for a Home Assistant proxy service (planned features: user auth, subscriptions, remote HA access).

## Common Commands

```bash
# Development (runs server, queue, logs, and vite in parallel)
composer dev

# Setup new environment
composer setup

# Run tests
composer test

# Run single test
php artisan test --filter=TestName

# Code formatting
./vendor/bin/pint

# Database migrations
php artisan migrate

# Create migration
php artisan make:migration create_table_name

# Create model with migration, factory, and controller
php artisan make:model ModelName -mfc
```

## Architecture

- **Framework**: Laravel 12 with Vite and Tailwind CSS 4
- **Database**: SQLite (default), configured for MySQL/PostgreSQL in production
- **Queue**: Database driver
- **Cache/Session**: Database driver

### Directory Structure

- `app/Http/` - Controllers and middleware
- `app/Models/` - Eloquent models
- `routes/web.php` - Web routes
- `routes/console.php` - Artisan commands
- `database/migrations/` - Database migrations
- `resources/views/` - Blade templates
- `tests/Feature/` - Feature tests
- `tests/Unit/` - Unit tests

## Testing

Tests use in-memory SQLite. Run with `composer test` or `php artisan test`.