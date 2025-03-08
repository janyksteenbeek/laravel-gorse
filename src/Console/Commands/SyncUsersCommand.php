<?php

namespace JanykSteenbeek\LaravelGorse\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use JanykSteenbeek\LaravelGorse\Traits\HasGorseRecommendations;
use Symfony\Component\Console\Helper\ProgressBar;

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

        $total = $model->count();

        if ($total === 0) {
            $this->info('No users found to sync.');
            return self::SUCCESS;
        }

        return $this->syncUsers($model, $total);
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
     * Sync users with Gorse.
     */
    protected function syncUsers(Model $model, int $total): int
    {
        $this->info("Starting sync of {$total} users...");

        $bar = $this->createProgressBar($total);
        $stats = ['processed' => 0, 'failed' => 0, 'errors' => []];

        $model->query()
            ->chunkById($this->option('chunk'), function (Collection $users) use ($bar, &$stats) {
                $this->processUserChunk($users, $bar, $stats);
            });

        $bar->finish();
        $this->newLine(2);

        return $this->displayResults($stats);
    }

    /**
     * Process a chunk of users.
     */
    protected function processUserChunk(Collection $users, ProgressBar $bar, array &$stats): void
    {
        $users->each(function (Model $user) use ($bar, &$stats) {
            try {
                $user->syncWithGorse();
                $stats['processed']++;
            } catch (Exception $e) {
                $stats['failed']++;
                $stats['errors'][] = "Error syncing user {$user->getKey()}: {$e->getMessage()}";
            }

            $bar->advance();
        });
    }

    /**
     * Create a progress bar instance.
     */
    protected function createProgressBar(int $total): ProgressBar
    {
        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        return $bar;
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