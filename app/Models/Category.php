<?php

namespace App\Models;

use App\Common\Utils\Utils;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{

    use ModelTree, AdminBuilder;

    protected $fillable = [
        'name', 'description',
    ];

    public function getDates()
    {
        return $this->dates;
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setParentColumn('pid');
        $this->setOrderColumn('sort');
        $this->setTitleColumn('name');
    }

    public function sharedCategoriesData()
    {
        $data = Cache::rememberForever('sharedCategories', function () {
            $categories = self::all();
            return Utils::getTreeDataRef($categories);
        });  // 把分类数据缓存起来，如果分类数据不存在了就进行重新获取
        return $data;
    }

    // 作用域
    // ----根据sort字段进行排序（数字越大，越靠前）
    public function scopeSort(Builder $builder, $order = 'desc')
    {
        $builder->orderBy('sort', $order);
    }

    // 关联表
    // ----主题关联表，一对多的关系
    public function topics()
    {
        return $this->hasMany(Topic::class, 'category_id', 'id');
    }

}
