<?php

namespace kirillemko\Yii\Permissions;


use \yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;


abstract class BasePermissionsBehavior extends Behavior
{

    protected $_modelPermissions = [];
    protected $_permissionsConfigCalculated = null;
    protected $_businessRulesConfigCalculated = null;

    abstract protected function getPermissionsConfig(): array;
    abstract protected function getBusinessRulesConfig(): array;





    public function getPermissionsData()
    {
        foreach ($this->getPermissionsConfigCalculated() as $permissionName => $permissionRuleData) {
            if( is_numeric($permissionName) ){
                $permissionName = $permissionRuleData;
                $permissionRuleData = null;
            }
            $this->getModelPermission($permissionName)->isPassed();
        }

        return $this->_modelPermissions;
    }


    public function permissionPassedOrFail($permissionName): void
    {
        $modelPermission = $this->getModelPermission($permissionName);

        if( !$modelPermission->isCanPassed() ){
            throw new ForbiddenHttpException(\Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    public function permissionRulesPassedOrFail($permissionName): void
    {
        $modelPermission = $this->getModelPermission($permissionName);

        if( !$modelPermission->isRulesPassed() ){
            throw new ForbiddenHttpException(\Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }








    private function getModelPermission($permissionName): ModelPermission
    {
        if( !isset($this->_modelPermissions[$permissionName]) ){
            $this->_modelPermissions[$permissionName] = new ModelPermission($this->owner, $permissionName);
            $this->_modelPermissions[$permissionName]->setRoleParams( $this->getPermissionsConfigCalculated()[$permissionName] ?? [] );
            $this->_modelPermissions[$permissionName]->setRules( $this->getBusinessRulesConfigCalculated()[$permissionName] ?? [] );
        }

        return $this->_modelPermissions[$permissionName];
    }



    private function getPermissionsConfigCalculated(): array
    {
        if( $this->_permissionsConfigCalculated === null ){
            $this->_permissionsConfigCalculated = $this->getPermissionsConfig();
        }
        return $this->_permissionsConfigCalculated;
    }
    private function getBusinessRulesConfigCalculated(): array
    {
        if( $this->_businessRulesConfigCalculated === null ){
            $this->_businessRulesConfigCalculated = $this->getBusinessRulesConfig();
        }
        return $this->_businessRulesConfigCalculated;
    }


}
