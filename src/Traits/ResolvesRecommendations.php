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
            ->mapToGroups(function ($recommendation) {
                $modelClass = $this->getModelFromGorseId($recommendation['Id'] ?? $recommendation);
                $id = $this->getIdFromGorseId($recommendation['Id'] ?? $recommendation);

                return [$modelClass => [
                    'id' => $id,
                    'score' => $recommendation['Score'] ?? 0,
                ]];
            })
            ->filter(fn ($items, $modelClass) => $modelClass !== null)
            ->map(function ($items, $modelClass) {
                $models = (new $modelClass)
                    ->whereIn((new $modelClass)->getKeyName(), collect($items)->pluck('id'))
                    ->get();

                // Attach scores to the models
                return $models->map(function ($model) use ($items) {
                    $item = collect($items)->firstWhere('id', $model->getKey());
                    $model->gorse_score = $item['score'];
                    return $model;
                });
            })
            ->reduce(function (Collection $carry, Collection $models) {
                return $carry->merge($models);
            }, new Collection);
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