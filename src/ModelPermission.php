<?php

namespace kirillemko\Yii\Permissions;


use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;


class ModelPermission
{

    protected $model;
    protected $permissionName;

    protected $roleParams = [];
    public $can = null;

    protected $rules = [];
    public $rulesPass = null;
    public $errors = [];

    
    public function __construct($model, $permissionName)
    {
        $this->model = $model;
        $this->permissionName = $permissionName;
    }


    
    
    public function isPassed()
    {
        return $this->isCanPassed() && $this->isRulesPassed();
    }    
    
    public function isCanPassed()
    {
        if( $this->can === null ){
            $this->checkCan();
        }
        return $this->can;
    }

    public function isRulesPassed()
    {
        if( $this->rulesPass === null ){
            $this->checkRules();
        }
        return $this->rulesPass;
    }

    
    
    

    /**
     * @param array|callable $roleParams
     * @return void
     */
    public function setRoleParams($roleParams): void
    {
        $this->roleParams = $roleParams;
    }
    
    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }
    
    
    
    private function checkCan()
    {
        if( is_callable($this->roleParams) ){
            $this->roleParams = ($this->roleParams)($this->model);
        }
        $this->can = \Yii::$app->user->can($this->permissionName, $this->roleParams);
    }
    
    private function checkRules()
    {
        foreach ($this->rules as $checkMethodName => $errorText) {
            if( is_numeric($checkMethodName) ){
                $checkMethodName = $errorText;
                $errorText = null;
            }

            try {
                $result = $this->model->$checkMethodName();
            } catch (UnknownMethodException $e) {
                throw new InvalidConfigException('Method ' . $checkMethodName . ' is not found in class');
            }

            if( !$result ){
                $this->rulesPass = false;
                if( $errorText ) {
                    $this->errors[] = $errorText;
                }
            }
        }
    }
    


}
