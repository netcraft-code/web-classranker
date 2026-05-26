<?php
namespace CustomFeature\ClassRanker\Models;

use Illuminate\Database\Eloquent\Model;
use CustomFeature\ClassRanker\Contracts\AiCommentUsage as AiCommentUsageContract;

class AiCommentUsage extends Model implements AiCommentUsageContract
{
    protected $fillable = ['customer_id', 'date', 'count'];

    protected $casts = ['date' => 'date'];

    public static function getRemainingForToday(int $customerId): int
    {
        $usage = static::where('customer_id', $customerId)
            ->where('date', today()->toDateString())
            ->first();

        return max(0, 5 - ($usage?->count ?? 0));
    }

    public static function incrementForToday(int $customerId): int
    {
        $usage = static::firstOrCreate(
            ['customer_id' => $customerId, 'date' => today()->toDateString()],
            ['count' => 0]
        );

        if ($usage->count >= 5) return 0;

        $usage->increment('count');
        return 5 - $usage->fresh()->count;
    }
}