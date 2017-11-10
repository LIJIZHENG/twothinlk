<?php

namespace backend\controllers;

use backend\models\PermissionForm;
use yii\web\NotFoundHttpException;

class RbacController extends \yii\web\Controller
{
    //添加权限
    public function actionAddPermission(){
        $model = new PermissionForm();
        $model->hobby = ['zhangsan','lisi'];//选项['zhangsan'=>'张三','lisi'=>'李四']
        //设置场景  当前场景是SCENARIO_Add场景
        $model->scenario = PermissionForm::SCENARIO_Add;
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate() && $model->add()){
                //提示信息 跳转
                echo '添加成功';exit;
            }


        }
        return $this->render('add-permission',['model'=>$model]);
    }

    //修改权限
    public function actionEditPermission($name){
        $auth = \Yii::$app->authManager;
        //获取权限
        $permission =  $auth->getPermission($name);

        //如果权限不存在,提示
        if($permission == null){
            throw new NotFoundHttpException('权限不存在');
        }
        $model = new PermissionForm();
        $model->scenario = PermissionForm::SCENARIO_EDIT;
        $model->oldName = $permission->name;
        $model->name = $permission->name;
        $model->description = $permission->description;
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate() && $model->update($name)){
                //提示信息 跳转
                echo '修改成功';exit;
            }


        }
        return $this->render('add-permission',['model'=>$model]);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

}
