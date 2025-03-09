<?php

namespace JanykSteenbeek\LaravelGorse\Traits;

use DateTime;
use Illuminate\Support\Collection;
use JanykSteenbeek\LaravelGorse\Facades\Gorse;
use JanykSteenbeek\LaravelGorse\Observers\GorseRecommendationObserver;

trait HasGorseRecommendations
{
    use Gorseable, ResolvesRecommendations;

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
        $recommendations = Gorse::getRecommendations($this->gorseItemId(), $number);

        return $this->resolveRecommendations($recommendations);
    }

    /**
     * Get category-specific recommendations for this user.
     */
    public function categoryRecommendations(string $category, int $number = 10): Collection
    {
        $recommendations = Gorse::getCategoryRecommendations($this->gorseItemId(), $category, $number);

        return $this->resolveRecommendations($recommendations);
    }

    /**
     * Get popular items.
     */
    public function popularItems(int $number = 10): Collection
    {
        $recommendations = Gorse::getPopularItems($number, $this->gorseItemId());

        return $this->resolveRecommendations($recommendations);
    }

    /**
     * Get popular items in a specific category.
     */
    public function popularItemsByCategory(string $category, int $number = 10): Collection
    {
        $recommendations = Gorse::getPopularItemsByCategory($category, $number, $this->gorseItemId());

        return $this->resolveRecommendations($recommendations);
    }

    /**
     * Get session-based recommendations.
     */
    public function sessionRecommendations(array $feedback, int $number = 10): Collection
    {
        $recommendations = Gorse::getSessionRecommendations($feedback, $number);

        return $this->resolveRecommendations($recommendations);
    }

    /**
     * Get category-specific session-based recommendations.
     */
    public function sessionCategoryRecommendations(string $category, array $feedback, int $number = 10): Collection
    {
        $recommendations = Gorse::getSessionCategoryRecommendations($category, $feedback, $number);

        return $this->resolveRecommendations($recommendations);
    }

    /**
     * Get similar users (neighbors) for this user.
     */
    public function userNeighbors(int $number = 10): Collection
    {
        $recommendations = Gorse::getUserNeighbors($this->gorseItemId(), $number);

        return $this->resolveRecommendations($recommendations);
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
