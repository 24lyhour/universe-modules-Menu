<?php

namespace Modules\Menu\Models;

use App\Traits\BelongsToOutlet;
use App\Traits\HasMutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Menu\Database\Factories\MenuFactory;

class Menu extends Model
{
    use HasFactory, BelongsToOutlet, SoftDeletes, HasMutable;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

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
        'is_muted',
        'muted_at',
        'muted_until',
        'muted_reason',
        'muted_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'schedule_status' => 'boolean',
    ];

    protected static function newFactory(): MenuFactory
    {
        return MenuFactory::new();
    }

    public function outlet()
    {
        return $this->belongsTo(\Modules\Outlet\Models\Outlet::class);
    }

    public function menuType()
    {
        return $this->belongsTo(MenuType::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
