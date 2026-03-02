<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ProductGroup;
use App\ParentCategory;

class ProductSubGroup extends Model
{
    protected $table = 'product_subgroup';
    protected $fillable = [
        'subgroup_name', 'images', 'sorting_no', 'pro_group_id', 'status', 'updated_by', 'updated_date'
    ];
    public $timestamps = false;

    // Keep both 'group' and 'parentGroup' if you use both names in different parts of your app
    public function group()
    {
        return $this->belongsTo(ProductGroup::class, 'pro_group_id', 'id');
    }

    public function getGroupNameAttribute()
    {
        return $this->group ? $this->group->name : null;
    }

    public function parentGroup()
    {
        return $this->belongsTo(ProductGroup::class, 'pro_group_id', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    public function categories()
    {
        return $this->hasMany(ParentCategory::class, 'subgroup_id', 'id')
                    ->where('is_deleted', '0')
                    ->orderBy('category_name', 'asc');
    }
}
