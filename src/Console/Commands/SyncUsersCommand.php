<?php

namespace JanykSteenbeek\LaravelGorse\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use JanykSteenbeek\LaravelGorse\Traits\HasGorseRecommendations;

use function Laravel\Prompts\progress;

class SyncUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gorse:sync-users 
        {--model=App\\Models\\User : The user model to sync}
        {--chunk=100 : The number of records to process at once}';

    /**
     * The console command description.
     */
    protected $description = 'Sync all users with Gorse recommendation engine';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $model = $this->getUserModel();

        if (! $model) {
            return self::FAILURE;
        }

        $users = $model->query()->get();
        $total = $users->count();

        if ($total === 0) {
            $this->info('No users found to sync.');

            return self::SUCCESS;
        }

        $stats = ['processed' => 0, 'failed' => 0, 'errors' => []];

        progress(
            label: "Syncing {$total} users with Gorse",
            steps: $users,
            callback: function ($user, $progress) use (&$stats) {
                try {
                    $user->syncWithGorse();
                    $stats['processed']++;

                    $progress->label("Syncing user {$user->getKey()}");
                } catch (Exception $e) {
                    $stats['failed']++;
                    $stats['errors'][] = "Error syncing user {$user->getKey()}: {$e->getMessage()}";

                    $progress->label("Error syncing user {$user->getKey()}");
                }
            },
            hint: 'This may take a while depending on the number of users.'
        );

        return $this->displayResults($stats);
    }

    /**
     * Get the user model instance.
     */
    protected function getUserModel(): ?Model
    {
        $modelClass = $this->option('model');

        if (! class_exists($modelClass)) {
            $this->error("Model class {$modelClass} does not exist.");

            return null;
        }

        /** @var Model $model */
        $model = new $modelClass;

        if (! $this->usesGorseRecommendations($model)) {
            $this->error(sprintf(
                'Model %s must use the %s trait.',
                $modelClass,
                HasGorseRecommendations::class
            ));

            return null;
        }

        return $model;
    }

    /**
     * Check if the model uses the HasGorseRecommendations trait.
     */
    protected function usesGorseRecommendations(Model $model): bool
    {
        return in_array(HasGorseRecommendations::class, class_uses_recursive($model));
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
