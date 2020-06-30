<?php

namespace App\Models;

use App\Common\ApiReturnCode;
use App\Common\Utils\Utils;
use App\Jobs\RecordReadCount;
use App\Models\Traits\QueryBuilderTrait;
use App\Observers\TopicObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Topic extends Model
{

    use SoftDeletes;  // 引入软删除的方法操作
    use QueryBuilderTrait;

    protected $fillable = [
        'title', 'content', 'category_id', 'user_id', 'description',
    ];

    protected static function boot()
    {
        parent::boot();  // 调用父类的boot方法
        static::observe(TopicObserver::class);  // 注册使用TopicObserver的模型观察器
    }

    public function getDates()
    {
        return $this->dates;
    }

    /**
     * 主题查询排序的方式
     * @param Builder $query
     * @param string $type
     */
    public function scopeOrder(Builder $query, $type = 'create', $order = 'desc')
    {
        switch (strtolower($type)) {
            case 'create':
                $query->orderBy('created_at', $order);
                break;
            case 'reply':
                $query->orderBy('reply_count', $order);
                break;
            case 'read':
                $query->orderBy('read_count', $order);
                break;
            default:
                $query->orderBy('created_at', $order);;
                break;
        }
    }

    public function scopeSubTreeData(Builder $query, $id)
    {
        $categories = Category::all(['id', 'pid']);  // 查询出分类里面所有数据里面的id，pid字段
        $categoryIds = Utils::getSubTreeIds($categories, $id);
        $query->whereIn('category_id', $categoryIds);
    }

    // 关联表
    // 用户关联表，反向一对一的关系
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    // 分类关联表，反向一对一的关系
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    // 回复关联表，一对多的关系
    public function replies()
    {
        return $this->hasMany(Reply::class)->orderBy('created_at', 'desc');
    }

    /**
     * 每次访问主题的时候，使用队列的方式来记录read_count的次数
     */
    public function recordReadCount()
    {
        $this->increment('read_count');
    }
    public function only($attributes)
    {
    }

    public function except($attributes) :array
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();
        $result = Arr::except(array_merge($this->getAttributes(), $this->relationsToArray()), $attributes);
        return $result;
    }
}
