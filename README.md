# Laravel Gorse

[![Latest Version on Packagist](https://img.shields.io/packagist/v/janyksteenbeek/laravel-gorse.svg?style=flat-square)](https://packagist.org/packages/janyksteenbeek/laravel-gorse)
[![Total Downloads](https://img.shields.io/packagist/dt/janyksteenbeek/laravel-gorse.svg?style=flat-square)](https://packagist.org/packages/janyksteenbeek/laravel-gorse)

A Laravel integration for the [Gorse](https://gorse.io) recommendation engine. This package provides a seamless way to integrate Gorse's recommendation capabilities into your Laravel application.

## Features

- Easy integration with Laravel's Eloquent models
- Automatic synchronization of users and items with Gorse
- Support for feedback tracking
- Simple recommendation retrieval
- Command-line tools for bulk synchronization

## Requirements

- PHP 8.1 or higher
- Laravel 11.x or 12.x
- A running Gorse instance

## Installation

You can install the package via composer:

```bash
composer require janyksteenbeek/laravel-gorse
```

After installation, publish the configuration file:

```bash
php artisan vendor:publish --tag="gorse-config"
```

## Configuration

Add the following environment variables to your `.env` file:

```env
GORSE_API_KEY=your-api-key
GORSE_ENDPOINT=http://your-gorse-instance:8087

# Optional: Disable SSL verification for development/self-signed certificates
GORSE_VERIFY_SSL=false
```

### SSL Verification

By default, SSL certificate verification is enabled for security. However, in development environments or when using self-signed certificates, you may need to disable it. You can do this by setting `GORSE_VERIFY_SSL=false` in your `.env` file.

> **Note:** It's recommended to keep SSL verification enabled in production environments.

## Usage

### Making Models Recommendable

To make a model recommendable (e.g., products, articles), use the `HasGorseFeedback` trait:

```php
use JanykSteenbeek\LaravelGorse\Traits\HasGorseFeedback;

class Product extends Model
{
    use HasGorseFeedback;
    
    // Optional: Customize Gorse integration
    protected function gorseCategories(): array
    {
        return ['products', $this->category];
    }
    
    protected function gorseLabels(): array
    {
        return [$this->type, $this->brand];
    }
}
```

### Making Users Receive Recommendations

To enable users to receive recommendations, use the `HasGorseRecommendations` trait:

```php
use JanykSteenbeek\LaravelGorse\Traits\HasGorseRecommendations;

class User extends Model
{
    use HasGorseRecommendations;
}
```

### Getting Recommendations

```php
// Get recommendations for a user
$recommendations = $user->getRecommendations(10); // Get 10 recommendations

// Record feedback
$user->addFeedback('like', $product->getKey());
```

### Manual Synchronization

The package provides commands to manually sync your data with Gorse:

```bash
# Sync all users
php artisan gorse:sync-users

# Sync all items
php artisan gorse:sync-items
```

### Using the Facade

You can also use the Gorse facade directly:

```php
use JanykSteenbeek\LaravelGorse\Facades\Gorse;

// Get recommendations
$recommendations = Gorse::getRecommendations($userId, 10);

// Insert feedback
Gorse::insertFeedback('like', $userId, $itemId);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. 