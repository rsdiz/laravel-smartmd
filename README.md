# Laravel Smartmd

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue.svg)](https://php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-%5E11.0%20%7C%7C%20%5E12.0-red.svg)](https://laravel.com/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-PHPUnit%2011-brightgreen.svg)](phpunit.xml)

A modern, feature-rich Laravel Markdown editor with enhanced functionality and Laravel 12 compatibility. This package provides a simple yet powerful Markdown editor that's compatible with most Markdown parsers and supports advanced features like mathematical formulas, Mermaid diagrams, and optimized image uploads.

## ✨ Features

- 🎯 **Laravel 11 & 12 Compatible** - Full support for modern Laravel versions
- 📝 **Enhanced Markdown Parser** - Extended Parsedown with additional features
- 🧮 **Mathematical Expressions** - LaTeX/MathJax support for complex formulas
- 📊 **Mermaid Diagrams** - Flowcharts, sequence diagrams, and more
- 🖼️ **Smart Image Uploads** - Automatic optimization and resizing
- 🔒 **Security First** - Built-in XSS protection and content sanitization
- 🎨 **Syntax Highlighting** - Enhanced code block rendering
- ⚡ **Performance Optimized** - Efficient parsing with optional caching
- 🧪 **Comprehensive Tests** - PHPUnit 11 compatible test suite
- 📘 **Type Declarations** - Full PHP 8.2+ type safety    

## 📋 Requirements

- PHP ^8.2
- Laravel ^11.0 || ^12.0
- Intervention Image Laravel ^1.3
- Parsedown ^1.8.0-beta-7

## 🚀 Installation

### 1. Install via Composer

```bash
composer require noisywinds/laravel-smartmd
```

### 2. Publish Assets and Configuration

```bash
# Publish all assets at once
php artisan vendor:publish --tag=smartmd

# Or publish individually
php artisan vendor:publish --tag=smartmd-config
php artisan vendor:publish --tag=smartmd-views
php artisan vendor:publish --tag=smartmd-assets
php artisan vendor:publish --tag=smartmd-controllers
```

### 3. Configure Routes

Add the following routes to your `routes/web.php`:

```php
use App\Http\Controllers\Smartmd\ParseController;
use App\Http\Controllers\Smartmd\UploadController;

// Markdown parsing routes
Route::get('/smartmd/demo', [ParseController::class, 'index'])->name('smartmd.demo');
Route::post('/smartmd/parse', [ParseController::class, 'parse'])->name('smartmd.parse');

// Image upload route
Route::post('/smartmd/upload', [UploadController::class, 'imSave'])->name('smartmd.upload');
```

## ⚙️ Configuration

The configuration file provides extensive customization options. See the published config file for details.

## 🎯 Usage

### Basic Usage with Facade

```php
use NoisyWinds\Smartmd\Facades\Smartmd;

// Parse markdown with enhanced features
$html = Smartmd::parse('# Hello World\n\nThis is **bold** text.');

// Access configuration
$maxSize = Smartmd::config('image.max_size', 4096);
```

### In Blade Views

```blade
@extends('layouts.app')

@section('content')
    @include('Smartmd::write')
@endsection
```

## 📚 Advanced Features

- Mathematical expressions with LaTeX support
- Mermaid diagrams and flowcharts
- Enhanced code highlighting
- Smart image uploads with optimization
- Security features and XSS protection

## 🧪 Testing

```bash
# Run tests
composer test

# Run with coverage
composer test-coverage
```

## 📄 License

MIT Licensed. See LICENSE file for details.

## 🙏 Acknowledgments

- Built on top of [Parsedown](https://parsedown.org/)
- Image processing by [Intervention Image](http://image.intervention.io/)
- Inspired by modern markdown editors

---

**Laravel Smartmd** - Making Markdown editing powerful and secure in Laravel applications.