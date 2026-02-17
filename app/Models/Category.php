<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Menu\Database\Factories\CategoryFactory;
use Modules\Product\Models\Product;

class Category extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'menu_categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'menu_id',
        'name',
        'image_url',
        'description',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    /**
     * Belongs to menu.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Many-to-many relationship with products.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'menu_category_products')
            ->withPivot('price_override', 'sort_order', 'is_available')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}

