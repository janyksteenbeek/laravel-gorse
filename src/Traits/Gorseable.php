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
}
