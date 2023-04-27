<?php

namespace kirillemko\Yii\Permissions;


use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;


abstract class BaseBusinessRule extends BaseObject
{
    abstract function execute($model): bool;


}
