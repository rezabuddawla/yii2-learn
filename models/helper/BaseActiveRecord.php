<?php

namespace app\models\helper;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord as BaseActiveRecordAlias;
use yii\db\Expression;

class BaseActiveRecord extends ActiveRecord
{
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecordAlias::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    BaseActiveRecordAlias::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}