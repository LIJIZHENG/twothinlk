<?php

namespace frontend\controllers;

use common\models\LoginForm;

class MemberController extends \yii\web\Controller
{
    //登录
    public function actionLogin(){
        //登录表单
        $model = new LoginForm();
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post(),'');
            if($model->validate()){
                $model->login();
                //提示 跳转
            }
        }


        return $this->render('login');
    }

    //验证用户名唯一
    public function actionCheckName($username){
        if($username=='admin'){
            return 'false';
        }
        return 'true';
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

}
