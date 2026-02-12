<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Modules\Outlet\Models\Outlet;

class MenuType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'menu_id',
        'menu_type_id',
        'category_id',
        'outlet_id',
        'image_url',
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

    /**
     * Belongs to outlet
     */
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Get the menus for this type.
     */
    public function menus()
    {
        return $this->hasMany(Menu::class, 'menu_type_id');
    }
}
