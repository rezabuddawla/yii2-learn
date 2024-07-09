<?php

namespace app\views\components;

use yii;
use yii\helpers\Html;

class HtmlHelper
{
    public static function csrfInput(): string
    {
        return Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken);
    }


    public static function getPriority($priority): string
    {
        if ($priority == 'Low') {
            return '<span class="badge text-bg-success">Low</span>';
        }
        else if ($priority == 'Medium') {
            return '<span class="badge text-bg-warning">Medium</span>';
        }
        else {
            return '<span class="badge text-bg-danger">High</span>';
        }
    }
}