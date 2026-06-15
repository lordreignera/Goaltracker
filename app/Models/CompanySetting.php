<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'company_short_name',
        'brand_mark',
        'logo_path',
        'product_name',
        'tagline',
        'email',
        'phone',
        'website',
        'address',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'company_name' => 'Africa Renewal Ministries',
            'company_short_name' => 'Africa Renewal',
            'brand_mark' => '90',
            'product_name' => 'SMART Goals Tracker',
            'tagline' => 'Plan, review, approve, and report on measurable goals.',
        ]);
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}
