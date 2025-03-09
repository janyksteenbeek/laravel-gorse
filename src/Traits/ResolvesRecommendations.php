<?php

namespace JanykSteenbeek\LaravelGorse\Traits;

use Illuminate\Support\Collection;

trait ResolvesRecommendations
{
    /**
     * Resolve recommendations into model instances with scores.
     */
    protected function resolveRecommendations(Collection $recommendations): Collection
    {
        return $recommendations
            ->map(function ($recommendation) {
                $modelClass = $this->getModelFromGorseId($recommendation['Id'] ?? $recommendation);
                $id = $this->getIdFromGorseId($recommendation['Id'] ?? $recommendation);

                return [
                    'modelClass' => $modelClass,
                    'id' => $id,
                    'score' => $recommendation['Score'] ?? 0,
                ];
            })
            ->reject(fn ($item) => $item['modelClass'] === null)
            ->groupBy('modelClass')
            ->map(function ($items) {
                $modelClass = $items->first()['modelClass'];
                $models = (new $modelClass)
                    ->whereIn((new $modelClass)->getKeyName(), $items->pluck('id'))
                    ->get();

                // Attach scores to the models
                return $models->map(function ($model) use ($items) {
                    $item = $items->firstWhere('id', $model->getKey());
                    $model->gorse_score = $item['score'];
                    return $model;
                });
            })
            ->values()
            ->flatten();
    }

    /**
     * Get the model class from a Gorse ID.
     */
    protected function getModelFromGorseId(string $id): ?string
    {
        $parts = explode(':', $id);

        return class_exists($parts[0]) ? $parts[0] : null;
    }

    /**
     * Get the ID from a Gorse ID.
     */
    protected function getIdFromGorseId(string $id): string
    {
        $parts = explode(':', $id);

        return $parts[1] ?? $id;
    }
} 