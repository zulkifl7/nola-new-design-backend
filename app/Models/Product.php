<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'cover_image',
        'featured',
        'status',
        'seo_title',
        'seo_description',
        'lead_time_days',
    ];

    protected $casts = [
        'featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class)->orderBy('sort_order');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class)->orderBy('sort_order');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProductMaterial::class)->orderBy('sort_order');
    }

    public function careInstructions(): HasMany
    {
        return $this->hasMany(ProductCareInstruction::class)->orderBy('sort_order');
    }
}
