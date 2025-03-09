<?php

namespace JanykSteenbeek\LaravelGorse\Services;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use JanykSteenbeek\LaravelGorse\Client\GorseClient;

class GorseService
{
    public function __construct(
        protected GorseClient $client
    ) {}

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
    public function getRecommendations(string $userId, int $number = 10): array
    {
        return $this->client->getRecommendations($userId, $number);
    }
}
