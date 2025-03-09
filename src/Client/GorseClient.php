<?php

namespace JanykSteenbeek\LaravelGorse\Client;

use DateTime;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GorseClient
{
    protected PendingRequest $client;

    public function __construct(
        string $endpoint,
        string $apiKey,
        bool $verifySSL = true
    ) {
        $this->client = Http::baseUrl($endpoint)
            ->withHeaders(['X-API-Key' => $apiKey])
            ->acceptJson()
            ->asJson();

        if (! $verifySSL) {
            $this->client->withoutVerifying();
        }
    }

    /**
     * Insert a user into Gorse.
     */
    public function insertUser(string $userId, array $labels = []): int
    {
        $response = $this->client->post('/api/user', [
            'UserId' => $userId,
            'Labels' => $labels,
        ]);

        return $this->getRowsAffected($response);
    }

    /**
     * Get a user from Gorse.
     */
    public function getUser(string $userId): array
    {
        return $this->client->get("/api/user/{$userId}")
            ->throw()
            ->json();
    }

    /**
     * Delete a user from Gorse.
     */
    public function deleteUser(string $userId): int
    {
        $response = $this->client->delete("/api/user/{$userId}");

        return $this->getRowsAffected($response);
    }

    /**
     * Insert an item into Gorse.
     */
    public function insertItem(
        string $itemId,
        array $categories = [],
        array $labels = [],
        bool $isHidden = false,
        ?DateTime $timestamp = null
    ): int {
        $response = $this->client->post('/api/item', array_filter([
            'ItemId' => $itemId,
            'Categories' => $categories,
            'Labels' => $labels,
            'IsHidden' => $isHidden,
            'Timestamp' => $timestamp?->format('c'),
        ]));

        return $this->getRowsAffected($response);
    }

    /**
     * Delete an item from Gorse.
     */
    public function deleteItem(string $itemId): int
    {
        $response = $this->client->delete("/api/item/{$itemId}");

        return $this->getRowsAffected($response);
    }

    /**
     * Insert feedback into Gorse.
     */
    public function insertFeedback(string $feedbackType, string $userId, string $itemId, ?DateTime $timestamp = null): int
    {
        $response = $this->client->post('/api/feedback', [[
            'FeedbackType' => $feedbackType,
            'UserId' => $userId,
            'ItemId' => $itemId,
            'Timestamp' => $timestamp?->format('c') ?? date('c'),
        ]]);

        return $this->getRowsAffected($response);
    }

    /**
     * Get recommendations for a user.
     */
    public function getRecommendations(string $userId, int $number = 10): array
    {
        return $this->client->get("/api/recommend/{$userId}", [
            'number' => $number,
        ])
            ->throw()
            ->json();
    }

    /**
     * Get category-specific recommendations for a user.
     */
    public function getCategoryRecommendations(string $userId, string $category, int $number = 10): array
    {
        return $this->client->get("/api/recommend/{$userId}/{$category}", [
            'number' => $number,
        ])
            ->throw()
            ->json();
    }

    /**
     * Get popular items.
     */
    public function getPopularItems(int $number = 10, ?string $userId = null): array
    {
        return $this->client->get('/api/popular', array_filter([
            'n' => $number,
            'user-id' => $userId,
        ]))
            ->throw()
            ->json();
    }

    /**
     * Get popular items in a specific category.
     */
    public function getPopularItemsByCategory(string $category, int $number = 10, ?string $userId = null): array
    {
        return $this->client->get("/api/popular/{$category}", array_filter([
            'n' => $number,
            'user-id' => $userId,
        ]))
            ->throw()
            ->json();
    }

    /**
     * Get session-based recommendations.
     */
    public function getSessionRecommendations(array $feedback, int $number = 10): array
    {
        return $this->client->post('/api/session/recommend', $feedback, [
            'n' => $number,
        ])
            ->throw()
            ->json();
    }

    /**
     * Get category-specific session-based recommendations.
     */
    public function getSessionCategoryRecommendations(string $category, array $feedback, int $number = 10): array
    {
        return $this->client->post("/api/session/recommend/{$category}", $feedback, [
            'n' => $number,
        ])
            ->throw()
            ->json();
    }

    /**
     * Get similar users (neighbors) for a user.
     */
    public function getUserNeighbors(string $userId, int $number = 10): array
    {
        return $this->client->get("/api/user/{$userId}/neighbors", [
            'n' => $number,
        ])
            ->throw()
            ->json();
    }

    /**
     * Get the number of rows affected from a response.
     */
    protected function getRowsAffected(Response $response): int
    {
        return $response->throw()->json('RowAffected', 0);
    }
}
