<?php

namespace JanykSteenbeek\LaravelGorse\Traits;

use DateTime;
use JanykSteenbeek\LaravelGorse\Facades\Gorse;
use JanykSteenbeek\LaravelGorse\Observers\GorseObserver;

trait HasGorseFeedback
{
    /**
     * Boot the trait.
     */
    protected static function bootHasGorseFeedback(): void
    {
        static::observe(GorseObserver::class);
    }

    /**
     * Get the ID that will be used to identify this model in Gorse.
     */
    public function getGorseItemId(): string|int
    {
        return $this->getKey();
    }

    /**
     * Get the categories for this model in Gorse.
     * Override this method to provide custom categories.
     */
    protected function gorseCategories(): array
    {
        return [self::class];
    }

    /**
     * Get the labels for this model in Gorse.
     * Override this method to provide custom labels.
     */
    protected function gorseLabels(): array
    {
        return [];
    }

    /**
     * Determine if this model should be hidden in Gorse.
     * Override this method to provide custom hidden logic.
     */
    protected function gorseIsHidden(): bool
    {
        return false;
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
     * Sync this model with Gorse.
     */
    public function syncWithGorse(): int
    {
        return Gorse::syncItem(
            $this,
            $this->gorseCategories(),
            $this->gorseLabels(),
            $this->gorseIsHidden(),
            $this->gorseTimestamp()
        );
    }

    /**
     * Record feedback for this model from a user.
     */
    public function receiveFeedback(string $type, string $userId, ?DateTime $timestamp = null): int
    {
        return Gorse::insertFeedback($type, $userId, (string) $this->getGorseItemId(), $timestamp);
    }
} 