<?php

namespace App\Traits;

trait UppercaseAttributes
{

    public static function bootUppercaseAttributes()
    {
        static::saving(function ($model) {
            if (property_exists($model, 'uppercase') && is_array($model->uppercase)) {
                foreach ($model->uppercase as $attribute) {
                    if (isset($model->{$attribute})) {
                        $model->{$attribute} = strtoupper($model->{$attribute});
                    }
                }
            }
        });
    }
}
