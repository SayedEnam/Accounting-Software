<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLocation extends Model
{
    use HasFactory;

    protected $table = 'product_locations';


    protected $fillable = [
        'name',
        'type',
        'created_by',
    ];

    public static $catTypes = [
        'product & service' => 'Product & Service',
        'income' => 'Income',
        'expense' => 'Expense',
        'asset' => 'Asset',
        'liability' => 'Liability',
        'equity' => 'Equity',
        'costs of good sold' => 'Costs of Goods Sold',
    ];


    /**
     * A product location has many product services
     */
    public function productServices()
    {
        return $this->hasMany(ProductService::class, 'productlocation_id');
    }
}
