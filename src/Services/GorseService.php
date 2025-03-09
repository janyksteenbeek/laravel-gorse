<?php

namespace JanykSteenbeek\LaravelGorse\Services;

use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use JanykSteenbeek\LaravelGorse\Client\GorseClient;
use JanykSteenbeek\LaravelGorse\Traits\Gorseable;
use JanykSteenbeek\LaravelGorse\Traits\ResolvesRecommendations;

class GorseService
{
    use ResolvesRecommendations;

    protected bool $shouldResolveModels = true;

    public function __construct(
        protected GorseClient $client
    ) {}

    /**
     * Set whether recommendations should be resolved to model instances.
     */
    public function resolveModels(bool $shouldResolve = true): self
    {
        $this->shouldResolveModels = $shouldResolve;

        return $this;
    }

    /**
     * Get raw recommendations without model resolution.
     */
    public function raw(): self
    {
        return $this->resolveModels(false);
    }

    /**
     * Sync a user with Gorse.
     */
    public function syncUser(Model $model, array $labels = []): int
    {
        return $this->client->insertUser((string) $model->getKey(), $labels);
    }

    /**
     * Delete a user from Gorse.
     */
    public function deleteUser(string $userId): int
    {
        return $this->client->deleteUser($userId);
    }

    /**
     * Sync an item with Gorse.
     */
    public function syncItem(
        Model $model,
        array $categories = [],
        array $labels = [],
        bool $isHidden = false,
        ?DateTime $timestamp = null
    ): int {
        return $this->client->insertItem(
            (string) $model->gorseItemId(),
            $categories,
            $labels,
            $isHidden,
            $timestamp
        );
    }

    /**
     * Delete an item from Gorse.
     */
    public function deleteItem(string $itemId): int
    {
        return $this->client->deleteItem($itemId);
    }

    /**
     * Insert feedback into Gorse.
     */
    public function insertFeedback(string $type, string $userId, string $itemId, ?DateTime $timestamp = null): int
    {
        return $this->client->insertFeedback($type, $userId, $itemId, $timestamp);
    }

    /**
     * Get recommendations for a user.
     */
    public function getRecommendations(string $userId, int $number = 10): Collection
    {
        $recommendations = collect($this->client->getRecommendations($userId, $number));

        return $this->shouldResolveModels
            ? $this->resolveRecommendations($recommendations)
            : $recommendations;
    }

    /**
     * Get category-specific recommendations for a user.
     */
    public function getCategoryRecommendations(string $userId, string $category, int $number = 10): Collection
    {
        $recommendations = collect($this->client->getCategoryRecommendations($userId, $category, $number));

        return $this->shouldResolveModels
            ? $this->resolveRecommendations($recommendations)
            : $recommendations;
    }

    /**
     * Get popular items.
     */
    public function getPopularItems(int $number = 10, ?string $userId = null): Collection
    {
        $recommendations = collect($this->client->getPopularItems($number, $userId));

        return $this->shouldResolveModels
            ? $this->resolveRecommendations($recommendations)
            : $recommendations;
    }

    /**
     * Get popular items in a specific category.
     */
    public function getPopularItemsByCategory(string $category, int $number = 10, ?string $userId = null): Collection
    {
        $recommendations = collect($this->client->getPopularItemsByCategory($category, $number, $userId));

        return $this->shouldResolveModels
            ? $this->resolveRecommendations($recommendations)
            : $recommendations;
    }

    /**
     * Get session-based recommendations.
     */
    public function getSessionRecommendations(array $feedback, int $number = 10): Collection
    {
        $recommendations = collect($this->client->getSessionRecommendations($feedback, $number));

        return $this->shouldResolveModels
            ? $this->resolveRecommendations($recommendations)
            : $recommendations;
    }

    /**
     * Get category-specific session-based recommendations.
     */
    public function getSessionCategoryRecommendations(string $category, array $feedback, int $number = 10): Collection
    {
        $recommendations = collect($this->client->getSessionCategoryRecommendations($category, $feedback, $number));

        return $this->shouldResolveModels
            ? $this->resolveRecommendations($recommendations)
            : $recommendations;
    }

    /**
     * Get similar users (neighbors) for a user.
     */
    public function getUserNeighbors(string $userId, int $number = 10): Collection
    {
        $recommendations = collect($this->client->getUserNeighbors($userId, $number));

        return $this->shouldResolveModels
            ? $this->resolveRecommendations($recommendations)
            : $recommendations;
    }
}
