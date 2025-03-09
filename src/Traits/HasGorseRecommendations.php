<?php

namespace JanykSteenbeek\LaravelGorse\Traits;

use DateTime;
use Illuminate\Support\Collection;
use JanykSteenbeek\LaravelGorse\Facades\Gorse;
use JanykSteenbeek\LaravelGorse\Observers\GorseRecommendationObserver;

trait HasGorseRecommendations
{
    use Gorseable;

    /**
     * Boot the trait.
     */
    protected static function bootHasGorseRecommendations(): void
    {
        static::observe(GorseRecommendationObserver::class);
    }

    /**
     * Get the labels for this user in Gorse.
     */
    protected function gorseLabels(): array
    {
        return [self::class];
    }

    /**
     * Get the ID that will be used to identify this model in Gorse.
     */
    protected function gorseItemId(): string
    {
        return (string) $this->getKey();
    }

    /**
     * Sync this user with Gorse.
     */
    public function syncWithGorse(): int
    {
        return Gorse::syncUser($this, $this->gorseLabels());
    }

    /**
     * Get recommendations for this user.
     */
    public function recommendations(int $number = 10): Collection
    {
        return Gorse::getRecommendations($this->gorseItemId(), $number);
    }

    /**
     * Get category-specific recommendations for this user.
     */
    public function categoryRecommendations(string $category, int $number = 10): Collection
    {
        return Gorse::getCategoryRecommendations($this->gorseItemId(), $category, $number);
    }

    /**
     * Get popular items.
     */
    public function popularItems(int $number = 10): Collection
    {
        return Gorse::getPopularItems($number, $this->gorseItemId());
    }

    /**
     * Get popular items in a specific category.
     */
    public function popularItemsByCategory(string $category, int $number = 10): Collection
    {
        return Gorse::getPopularItemsByCategory($category, $number, $this->gorseItemId());
    }

    /**
     * Get session-based recommendations.
     */
    public function sessionRecommendations(array $feedback, int $number = 10): Collection
    {
        return Gorse::getSessionRecommendations($feedback, $number);
    }

    /**
     * Get category-specific session-based recommendations.
     */
    public function sessionCategoryRecommendations(string $category, array $feedback, int $number = 10): Collection
    {
        return Gorse::getSessionCategoryRecommendations($category, $feedback, $number);
    }

    /**
     * Get similar users (neighbors) for this user.
     */
    public function userNeighbors(int $number = 10): Collection
    {
        return Gorse::getUserNeighbors($this->gorseItemId(), $number);
    }

    /**
     * Add feedback from this user for an item.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $item  Model instance with HasGorseFeedback trait
     */
    public function feedback(object|string $item, string $type, ?DateTime $timestamp = null): int
    {
        if (is_object($item) && method_exists($item, 'gorseItemId')) {
            $item = $item->gorseItemId();
        } elseif (is_object($item)) {
            $item = get_class($item).':'.$item->getKey();
        }

        return Gorse::insertFeedback($type, $this->gorseItemId(), $item, $timestamp);
    }
}
