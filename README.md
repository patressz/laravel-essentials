# Laravel Essentials

<p align="center">
    <a href="https://packagist.org/packages/patressz/laravel-essentials"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/patressz/laravel-essentials"></a>
    <a href="https://packagist.org/packages/patressz/laravel-essentials"><img alt="Latest Version" src="https://img.shields.io/packagist/v/patressz/laravel-essentials"></a>
    <a href="https://packagist.org/packages/patressz/laravel-essentials"><img alt="License" src="https://img.shields.io/packagist/l/patressz/laravel-essentials"></a>
</p>

------

**Laravel Essentials** is a Laravel package that streamlines the setup of essential development tools and applies Laravel best practices to your application. It provides an interactive installation command that sets up code quality tools and configures your AppServiceProvider with production-ready optimizations.

> **Requires [PHP 8.3+](https://php.net/releases/) and Laravel 11+**

## Installation

Install the package via Composer:

```bash
composer require patressz/laravel-essentials --dev
```

## Usage

Run the installation command to set up your Laravel project with essential tools and configurations:

```bash
php artisan essentials:install
```

### What it does

The installation command will:

1. **Install Development Tools** (interactive selection):
   - **Laravel Pint** - Code style fixer
   - **PHPStan (Larastan)** - Static analysis tool
   - **Rector** - PHP automated refactoring tool

2. **Configure AppServiceProvider** with Laravel best practices:
   - **Model Strictness** - Enforce strict model behavior in production
   - **Model Unguarded** - Disable mass assignment protection globally
   - **Automatic Eager Loading** - Prevent N+1 queries in development
   - **Date Configuration** - Use Carbon Immutable by default
   - **Command Safety** - Prohibit destructive commands in production
   - **HTTPS Enforcement** - Force HTTPS scheme in production
   - **Asset Prefetching** - Enable Vite asset prefetching

### Command Options

```bash
# Skip confirmation prompts and overwrite existing files
php artisan essentials:install --yes
```

## Development Tools

After installation, you can use these commands for code quality:

🧹 **Code Style Fixing with Pint**:
```bash
./vendor/bin/pint
```

⚗️ **Static Analysis with PHPStan**:
```bash
./vendor/bin/phpstan
```

🔧 **Code Refactoring with Rector**:
```bash
./vendor/bin/rector
```

## Configuration Files

The package will create configuration files for the selected tools:

- `pint.json` - Laravel Pint configuration
- `phpstan.neon` - PHPStan configuration with Laravel rules
- `rector.php` - Rector configuration with Laravel-specific rules

## Requirements

- PHP 8.3 or higher
- Laravel 11.0 or higher

## License

**Laravel Essentials** was created by **[Patrik Strišovský](mailto:patrik.strisovsky7@gmail.com)** under the **[MIT license](https://opensource.org/licenses/MIT)**.
