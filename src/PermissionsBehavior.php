<?php

namespace kirillemko\Yii\Permissions;


use \yii\base\Behavior;
use yii\base\InvalidConfigException;


abstract class BasePermissionsBehavior extends Behavior
{
    protected $_permissionsCalculated = null;
    protected $_businessRulesCalculated = null;
    protected $_businessRulesErrors = [];



    abstract protected function getPermissionsConfig(): array;
    abstract protected function getBusinessRulesConfig(): array;


    protected $object;


    /**
     * @param $object
     */
    public function __construct()
    {
        $this->object = $this->owner;
    }







    public function getPermissions(): array
    {
        if( $this->_permissionsCalculated === null ){
            $rawPermissions = $this->getPermissionsConfig();
            if( !$rawPermissions ){
                $this->_permissionsCalculated = [];
            }
            foreach ($rawPermissions as $permissionName => $ruleData) {
                if( is_numeric($permissionName) ){
                    $permissionName = $ruleData;
                    $ruleData = null;
                }
                $this->_permissionsCalculated[$permissionName] = \Yii::$app->user->can($permissionName, $ruleData);
            }
        }

        return $this->_permissionsCalculated;
    }



    public function getBusinessRules($stopOnFirstError=false): array
    {
        if( $this->_businessRulesCalculated === null ){
            $rawRules = $this->getBusinessRulesConfig();
            if( !$rawRules ){
                $this->_businessRulesCalculated = [];
            }
            foreach ($rawRules as $permissionName => $permissionRules) {
                $this->_businessRulesErrors[$permissionName] = [];
                foreach ($permissionRules as $checkMethodName => $errorText) {
                    if( is_numeric($checkMethodName) ){
                        $checkMethodName = $errorText;
                        $errorText = null;
                    }
                    if( !method_exists($this->owner, $checkMethodName) ){
                        throw new InvalidConfigException('Method ' . $checkMethodName . ' is not found in class');
                    }
                    if( !$this->owner->{$checkMethodName}() ){
                        $this->_businessRulesCalculated[$permissionName] = false;
                        if( $errorText ) {
                            $this->_businessRulesErrors[$permissionName][] = $errorText;
                        }
                        if( $stopOnFirstError ){
                            break;
                        }

                    }
                }
            }
        }
        return $this->_businessRulesCalculated;
    }


    public function getBusinessRulesErrors(): array
    {
        if( $this->_businessRulesCalculated === null ){
            $this->getBusinessRules();
        }
        return $this->_businessRulesErrors;
    }


    public function getFullPermissionData(): array
    {
        $data = [];
        foreach ($this->getPermissions() as $permissionName => $permissionAllow) {
            $data[$permissionName] = [
                'allow' => $permissionAllow,
                'business' => false,
                'errors' => [],
            ];
        }
        foreach ($this->getBusinessRules() as $permissionName => $ruleAllow) {
            if( !isset($data[$permissionName]) ){
                $data[$permissionName] = [
                    'allow' => false,
                    'business' => false,
                    'errors' => [],
                ];
            }
            $data[$permissionName]['business'] = $ruleAllow;
        }
        foreach ($this->getBusinessRulesErrors() as $permissionName => $errors) {
            $data[$permissionName]['errors'] = $errors;
        }

        return $data;
    }




}
