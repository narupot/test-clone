<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentCategory extends Model
{
    protected $table = 'parent_category';
    protected $fillable = [
            'category_name', 'url', 'img', 'is_deleted',
            'meta_title', 'meta_keyword', 'meta_description',
            'cat_description', 'sorting_no', 'group_id',
            'subgroup_id', 'created_at', 'created_by',
            'updated_at', 'updated_by'
        ];

    public $timestamps = false;

    public function group()
    {
        return $this->belongsTo(ProductGroup::class, 'group_id', 'id');
    }

    public function subgroup()
    {
        return $this->belongsTo(ProductSubGroup::class, 'subgroup_id', 'id');
    }
}
