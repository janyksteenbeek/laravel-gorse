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

# Optional: Enable automatic synchronization with Gorse
GORSE_AUTO_SYNC=false

# Optional: Enable automatic model resolving (default: true)
GORSE_RESOLVING_ENABLED=true
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

    protected function gorseLabels(): array
    {
        return [self::class, $this->country];
    }
}
```

### Getting Recommendations

```php
// Get recommendations for a user
$recommendations = $user->recommendations(10); // Get 10 recommendations

// Get category-specific recommendations
$recommendations = $user->categoryRecommendations('electronics', 10);

// Get popular items
$recommendations = $user->popularItems(10);

// Get popular items in a category
$recommendations = $user->popularItemsByCategory('electronics', 10);

// Get session-based recommendations
$recommendations = $user->sessionRecommendations([
    [
        'FeedbackType' => 'click',
        'ItemId' => 'product:123',
        'Timestamp' => '2024-03-20T12:00:00Z'
    ]
], 10);

// Get category-specific session recommendations
$recommendations = $user->sessionCategoryRecommendations('electronics', [
    [
        'FeedbackType' => 'click',
        'ItemId' => 'product:123'
    ]
], 10);

// Get similar users (neighbors)
$similarUsers = $user->userNeighbors(10);

// Record feedback
$user->feedback($product, 'like');
```

### Auto-Sync

The package supports automatic synchronization of your models with Gorse. When enabled, any changes to your models (create, update, delete) will be automatically synchronized with Gorse. You can enable this feature by setting `GORSE_AUTO_SYNC=true` in your `.env` file.

### Model Resolving

By default, when retrieving recommendations, the package will automatically resolve the recommended items into their respective Eloquent models. This means you'll get a collection of actual model instances instead of just IDs. You can disable this feature by setting `GORSE_RESOLVING_ENABLED=false` in your `.env` file.

### Manual Synchronization

The package provides commands to manually sync your data with Gorse:

```bash
# Sync all users
php artisan gorse:sync-users --model=App\\Models\\User --chunk=100

# Sync all items
php artisan gorse:sync-items --model=App\\Models\\Product --chunk=100
```

The `--chunk` option allows you to specify how many records should be processed at once (default: 100).

### Using the Facade

You can also use the Gorse facade directly:

```php
use JanykSteenbeek\LaravelGorse\Facades\Gorse;

// Get recommendations (resolved to model instances)
$recommendations = Gorse::getRecommendations($userId, 10);

// Get raw recommendations without model resolution
$rawRecommendations = Gorse::raw()->getRecommendations($userId, 10);
// Returns a Collection of: [['Id' => 'Product:1', 'Score' => 0.95], ...]

// All recommendation methods return Collections, making them Laravel-friendly
$rawRecommendations->pluck('Score')->avg(); // Get average score
$rawRecommendations->sortByDesc('Score'); // Sort by score
$rawRecommendations->filter(fn ($item) => $item['Score'] > 0.5); // Filter by score

// Or use resolveModels() to control resolution
$rawRecommendations = Gorse::resolveModels(false)->getRecommendations($userId, 10);

// Get category recommendations
$recommendations = Gorse::getCategoryRecommendations($userId, 'electronics', 10);

// Get popular items
$recommendations = Gorse::getPopularItems(10, $userId);

// Get popular items in category
$recommendations = Gorse::getPopularItemsByCategory('electronics', 10, $userId);

// Get session recommendations
$recommendations = Gorse::getSessionRecommendations($feedback, 10);

// Get session recommendations in category
$recommendations = Gorse::getSessionCategoryRecommendations('electronics', $feedback, 10);

// Get similar users
$recommendations = Gorse::getUserNeighbors($userId, 10);

// Insert feedback
Gorse::insertFeedback('like', $userId, $itemId);
```

All recommendation methods return Laravel Collections, regardless of whether model resolution is enabled. This means you can use all of Laravel's Collection methods on both resolved and raw results:

```php
// Raw response with scores (as Collection)
$rawRecommendations = Gorse::raw()->getRecommendations($userId, 10);

// Use Collection methods on raw results
$highScoring = $rawRecommendations
    ->filter(fn ($item) => $item['Score'] > 0.8)
    ->sortByDesc('Score')
    ->values();

$averageScore = $rawRecommendations->pluck('Score')->avg();

// Resolved to models with scores (as Collection)
$recommendations = Gorse::getRecommendations($userId, 10);

// Use Collection methods on resolved models
$highScoring = $recommendations
    ->filter(fn ($model) => $model->gorse_score > 0.8)
    ->sortByDesc('gorse_score')
    ->values();

$averageScore = $recommendations->pluck('gorse_score')->avg();
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. 