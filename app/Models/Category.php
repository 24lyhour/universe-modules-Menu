<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Menu\Database\Factories\CategoryFactory;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'menu_id',
        'product_id',
        'name',
        'image_Url',
        'description',
        'sort_order',
        'status',
        
       
    ];

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    /**
     * raltion to menu
     */
    public function menu(){
        return $this->hasMany(Menu::class);
    }

    /**
     * reslation to the product
     */
    public function product() 
    {
        return $this->hasMany(Product::class);
    }
}

