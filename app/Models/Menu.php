<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Menu\Database\Factories\MenuFactory;

class Menu extends Model
{
    use HasFactory;

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
     * BelongTo resltion  to the outlet
     */
    public function outlet() 
    {
        return $this->hasMany(Outlet::class);
    }

    /**
     * belong to the products
     */
    public function product()
    {
        return $this->belongTo(Product::class);
    }

    /**
     * belong to the categorys 
     */
    public function category() 
    {
        return $this->hasMany(Category::class);
    }

    /**
     * beloong to company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
