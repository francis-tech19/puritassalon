<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'details',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log(?int $userId, string $action, string $module, ?string $details = null, ?string $ip = null): static
    {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'details' => $details,
            'ip_address' => $ip ?? request()->ip(),
        ]);
    }
}
