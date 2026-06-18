<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class Integration extends Model
{
    protected $fillable = [
        'tenant_id', 'key', 'status', 'config', 'secrets', 'connected_at',
    ];

    protected function casts(): array
    {
        return [
            'config'       => 'array',
            'connected_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Scope: get or create integration row for tenant+key
    public static function forTenant(int $tenantId, string $key): self
    {
        return static::firstOrNew(['tenant_id' => $tenantId, 'key' => $key]);
    }

    public function setSecret(string $field, string $value): void
    {
        $secrets = $this->getRawSecrets();
        $secrets[$field] = Crypt::encryptString($value);
        $this->secrets = json_encode($secrets);
    }

    public function getSecret(string $field): ?string
    {
        $secrets = $this->getRawSecrets();
        if (empty($secrets[$field])) {
            return null;
        }
        try {
            return Crypt::decryptString($secrets[$field]);
        } catch (\Exception) {
            return null;
        }
    }

    public function maskedSecret(string $field): string
    {
        $val = $this->getSecret($field);
        if (blank($val)) {
            return '';
        }
        return substr($val, 0, 4) . str_repeat('•', max(0, strlen($val) - 4));
    }

    private function getRawSecrets(): array
    {
        $raw = is_string($this->secrets) ? json_decode($this->secrets, true) : $this->secrets;
        return is_array($raw) ? $raw : [];
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }
}
