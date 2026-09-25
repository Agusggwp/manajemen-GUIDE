<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuideQueueState extends Model
{
    protected $table = 'guide_queue_state';

    protected $fillable = [
        'current_position',
        'last_guide_id',
        'last_assignment_id',
    ];

    public function lastGuide()
    {
        return $this->belongsTo(Guide::class, 'last_guide_id');
    }

    public function lastAssignment()
    {
        return $this->belongsTo(GuideAssignment::class, 'last_assignment_id');
    }

    public static function getState(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['current_position' => 0]
        );
    }
}
