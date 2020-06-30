<?php

namespace App\Models\Traits;

trait QueryBuilderTrait
{

    public function resolveRouteBinding($value, $field = null)
    {
        $queryClass = property_exists($this, 'queryClass') ?
            $this->queryClass :
            '\App\Models\Queries\\' . class_basename($this) . 'Query';
        if (class_exists($queryClass)) {
            return (new $queryClass)->where($field ?? $this->getRouteKeyName(), $value)->first();
        } else {
            return parent::resolveRouteBinding($value, $field);
        }
    }

}