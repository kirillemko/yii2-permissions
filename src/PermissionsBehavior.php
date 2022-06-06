<?php

namespace kirillemko\Yii\Permissions;


use \yii\base\Behavior;


class PermissionsBehavior extends Behavior
{

    /**
     * @var array permissions array for the model as PermissionName => PermissionRulesParams
     */
    public $permissions = [];





    protected $_permissionsCalculated = null;



    public function getPermissions(): array
    {
        if( $this->_permissionsCalculated === null ){
            foreach ($this->permissions as $permissionName => $ruleData) {
                self::$_permissionsCalculated[$permissionName] = \Yii::$app->user->can($permissionName, $ruleData);
            }
        }

        return self::$_permissionsCalculated;
    }




}
