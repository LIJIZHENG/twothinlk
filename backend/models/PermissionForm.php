<?php
namespace backend\models;

use yii\base\Model;

class PermissionForm extends Model{
    public $name;
    public $description;
    public $oldName;
    public $hobby;

    //场景 场景必须要对应验证规则
    const SCENARIO_Add = 'add';
    const SCENARIO_EDIT = 'edit';




    public function rules()
    {
        return [//验证规则没有定义场景 则所有场景都生效
            ['name','required'],
            ['description','safe'],
            //自定义验证规则  on表示只在该场景下生效
            ['name','validateName','on'=>[self::SCENARIO_Add]],//添加时生效 修改时不生效
            ['name','validateUpdateName','on'=>self::SCENARIO_EDIT],//修改时验证
        ];
    }

    public function validateName(){
        //自定义验证方法 只处理验证失败的情况
        $auth = \Yii::$app->authManager;
        $model = $auth->getPermission($this->name);
        if($model){
            //权限已存在
            $this->addError('name','权限已存在');
            //return false;
        }
    }
//修改时验证权限名称
    public function validateUpdateName()
    {
        //只处理验证失败的情况  名称被修改,新名称已存在
        $auth = \Yii::$app->authManager;
        if($this->oldName != $this->name){
            $model = $auth->getPermission($this->name);
            if($model){
                //权限已存在
                $this->addError('name','权限已存在');
                //return false;
            }
        }

    }


    public function add(){
        $auth = \Yii::$app->authManager;

        $permission = $auth->createPermission($this->name);
        $permission->description = $this->description;
        return $auth->add($permission);
    }

    //更新权限
    public function update($name){
        $auth = \Yii::$app->authManager;
        $permission = $auth->getPermission($name);
        $permission->name = $this->name;
        $permission->description = $this->description;
        return $auth->update($name,$permission);
    }
}