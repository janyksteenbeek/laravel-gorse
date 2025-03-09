<?php

namespace JanykSteenbeek\LaravelGorse\Traits;

use DateTime;
use JanykSteenbeek\LaravelGorse\Facades\Gorse;
use JanykSteenbeek\LaravelGorse\Observers\GorseObserver;
use Illuminate\Support\Collection;

trait HasGorseFeedback
{
    use Gorseable;

    /**
     * Boot the trait.
     */
    protected static function bootHasGorseFeedback(): void
    {
        static::observe(GorseObserver::class);
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
     * Get the categories for this model in Gorse.
     * Override this method to provide custom categories.
     */
    protected function gorseCategories(): array
    {
        return [static::class];
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
     * Record feedback for this item from a user.
     */
    public function feedback(string $type, ?string $userId = null, ?DateTime $timestamp = null): int
    {
        return Gorse::insertFeedback($type, $userId, $this->gorseItemId(), $timestamp);
    }

    /**
     * Get similar items (neighbors) for this item.
     */
    public function neighbors(int $number = 10): Collection
    {
        return Gorse::getUserNeighbors($this->gorseItemId(), $number);
    }
}
