<?php

namespace JanykSteenbeek\LaravelGorse\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use JanykSteenbeek\LaravelGorse\Traits\HasGorseFeedback;
use function Laravel\Prompts\progress;

class SyncItemsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gorse:sync-items 
        {--model= : The model class to sync}
        {--chunk=100 : The number of records to process at once}';

    /**
     * The console command description.
     */
    protected $description = 'Sync all items with Gorse recommendation engine';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('model')) {
            $this->error('Please provide a model class using --model option.');
            return self::FAILURE;
        }

        $model = $this->getItemModel();

        if (! $model) {
            return self::FAILURE;
        }

        $items = $model->query()->get();
        $total = $items->count();

        if ($total === 0) {
            $this->info('No items found to sync.');
            return self::SUCCESS;
        }

        $stats = ['processed' => 0, 'failed' => 0, 'errors' => []];

        progress(
            label: "Syncing {$total} items with Gorse",
            steps: $items,
            callback: function ($item, $progress) use (&$stats) {
                try {
                    $item->syncWithGorse();
                    $stats['processed']++;
                    
                    $progress->label("Syncing item {$item->getGorseItemId()}");
                } catch (Exception $e) {
                    $stats['failed']++;
                    $stats['errors'][] = "Error syncing item {$item->getGorseItemId()}: {$e->getMessage()}";
                    
                    $progress->label("Error syncing item {$item->getGorseItemId()}");
                }
            },
            hint: 'This may take a while depending on the number of items.'
        );

        return $this->displayResults($stats);
    }

    /**
     * Get the item model instance.
     */
    protected function getItemModel(): ?Model
    {
        $modelClass = $this->option('model');

        if (! class_exists($modelClass)) {
            $this->error("Model class {$modelClass} does not exist.");
            return null;
        }

        /** @var Model $model */
        $model = new $modelClass;

        if (! $this->usesGorseFeedback($model)) {
            $this->error(sprintf(
                'Model %s must use the %s trait.',
                $modelClass,
                HasGorseFeedback::class
            ));
            return null;
        }

        return $model;
    }

    /**
     * Check if the model uses the HasGorseFeedback trait.
     */
    protected function usesGorseFeedback(Model $model): bool
    {
        return in_array(HasGorseFeedback::class, class_uses_recursive($model));
    }

    /**
     * Display the sync results.
     */
    protected function displayResults(array $stats): int
    {
        $this->info('Sync completed!');
        $this->info("Processed: {$stats['processed']}");

        if ($stats['failed'] > 0) {
            $this->error("Failed: {$stats['failed']}");
            $this->error('Errors encountered:');
            
            foreach ($stats['errors'] as $error) {
                $this->error("- {$error}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
} 