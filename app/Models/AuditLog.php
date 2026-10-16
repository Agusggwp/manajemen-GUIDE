<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function log(string $action, ?string $model = null, ?int $modelId = null, ?array $oldValues = null, ?array $newValues = null): self
    {
        $log = static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        try {
            /** @var \App\Services\DiscordWebhookService $discord */
            $discord = app(\App\Services\DiscordWebhookService::class);
            if ($discord->isActivityEnabled()) {
                if (app()->runningUnitTests() || app()->runningInConsole()) {
                    $discord->sendActivityLog($log);
                } else {
                    dispatch(function () use ($log) {
                        app(\App\Services\DiscordWebhookService::class)->sendActivityLog($log);
                    })->afterResponse();
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Discord activity webhook trigger failed: ' . $e->getMessage());
        }

        return $log;
    }
}
