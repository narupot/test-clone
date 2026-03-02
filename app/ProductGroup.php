<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ProductSubGroup;
use App\AdminUser;


class ProductGroup extends Model
{
    protected $table = 'product_group';
    protected $fillable = [
        'name', 'image', 'sorting_no', 'status', 'updated_date', 'updated_by'
    ];
    public $timestamps = false;

    public function subgroups()
    {
        return $this->hasMany(ProductSubGroup::class, 'pro_group_id', 'id')->orderBy('sorting_no', 'asc');
    }
    public function updater()
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }
}