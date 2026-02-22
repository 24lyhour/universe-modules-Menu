<?php

namespace Modules\Menu\Models;

use App\Traits\BelongsToOutlet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Menu\Database\Factories\MenuFactory;

class Menu extends Model
{
    use HasFactory, BelongsToOutlet;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'tenant_type',
        'company_id',
        'tenant_id',
        'outlet_id',
        'menu_id',
        'menu_type_id',
        'product_id',
        'category_id',
        'status',
        'name',
        'description',
        'image_url',
        'schedule_mode',
        'schedule_days',
        'schedule_start_time',
        'schedule_end_time',
        'schedule_start_date',
        'schedule_end_date',
        'schedule_status',
        'created_by',
        'updated_by',
    ];

    /**
     * cast
     */
    protected $casts = [
       
        'status' => 'boolean'   ,
        'schedule_status' => 'boolean',
       
    ];

    protected static function newFactory(): MenuFactory
    {
        return MenuFactory::new();
    }

    /**
     * Belongs to outlet
     */
    public function outlet()
    {
        return $this->belongsTo(\Modules\Outlet\Models\Outlet::class);
    }

    /**
     * Belongs to menu type
     */
    public function menuType()
    {
        return $this->belongsTo(MenuType::class);
    }

    /**
     * Belongs to product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Has many categories
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Belongs to company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
