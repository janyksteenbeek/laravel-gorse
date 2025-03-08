<?php

namespace JanykSteenbeek\LaravelGorse\Facades;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use JanykSteenbeek\LaravelGorse\Services\GorseService;

/**
 * @method static int syncUser(Model $model, array $labels = [])
 * @method static int deleteUser(string $userId)
 * @method static int syncItem(Model $model, array $categories = [], array $labels = [], bool $isHidden = false, ?DateTime $timestamp = null)
 * @method static int deleteItem(string $itemId)
 * @method static int insertFeedback(string $type, string $userId, string $itemId, ?DateTime $timestamp = null)
 * @method static array getRecommendations(string $userId, int $number = 10)
 * 
 * @see \JanykSteenbeek\LaravelGorse\Services\GorseService
 */
class Gorse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GorseService::class;
    }
} 