<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * CompanyAiKey — BYO API key per company per provider.
 * Keys are encrypted with the application key.
 */
class CompanyAiKey extends Model
{
    protected $table = 'company_ai_keys';

    protected $fillable = [
        'company_id',
        'provider',
        'api_key_encrypted',
        'model',
        'is_active',
        'created_by',
    ];

    protected $hidden = ['api_key_encrypted'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function setApiKeyAttribute(string $key): void
    {
        $this->attributes['api_key_encrypted'] = Crypt::encryptString($key);
    }

    public function getDecryptedKey(): string
    {
        return Crypt::decryptString($this->api_key_encrypted);
    }
}
