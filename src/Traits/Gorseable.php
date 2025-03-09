<?php

namespace JanykSteenbeek\LaravelGorse\Traits;

use DateTime;

trait Gorseable
{
    /**
     * Get the ID that will be used to identify this model in Gorse.
     */
    public function gorseItemId(): string
    {
        return static::class.':'.$this->getKey();
    }

    abstract protected function gorseCategories(): array;

    /**
     * Get the labels for this model in Gorse.
     * Override this method to provide custom labels.
     */
    protected function gorseLabels(): array
    {
        return [static::class];
    }

    /**
     * Get the timestamp for this model in Gorse.
     * Override this method to provide a custom timestamp.
     */
    protected function gorseTimestamp(): ?DateTime
    {
        return $this->updated_at;
    }

    /**
     * Get the model class from a Gorse item ID.
     */
    protected static function getModelFromGorseId(string $itemId): ?string
    {
        if (! str_contains($itemId, ':')) {
            return null;
        }

        [$class] = explode(':', $itemId);

        return class_exists($class) ? $class : null;
    }

    /**
     * Get the model ID from a Gorse item ID.
     */
    protected static function getIdFromGorseId(string $itemId): ?string
    {
        if (! str_contains($itemId, ':')) {
            return null;
        }

        [, $id] = explode(':', $itemId);

        return $id;
    }
}
