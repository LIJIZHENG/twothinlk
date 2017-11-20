<?php

namespace frontend\controllers;

use common\models\LoginForm;
use frontend\components\Sms;

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

    //用户注册
    public function actionRegister(){

        return $this->render('register');
    }

    //AJAX发送短信  后台AJAX发送短信功能:
    public function actionAjaxSms($phone){
        $redis = new \Redis();
        $redis->connect('127.0.0.1');
        //==========设置短信发送间隔(1分钟同一手机号只能发送一条)===========
        //redis获取该手机号码发送验证码的信息(验证码,过期时间)
        $code = $redis->get('captcha_'.$phone);
        if($code){
            //最近10分钟有发送短信
            //判断是否是一分钟内发送的(根据过期时间)
            // 10分钟过期 -  生命还剩多少秒  =  发送多久了
            //$redis->ttl();//生命还剩多少秒
            $result = 10*60 - $redis->ttl('captcha_'.$phone);
            if($result <= 60){
                echo '两次短信发送间隔不到60秒(请'.(60-$result).'秒后再试)';exit;
            }
        }else{
            //最近10分钟,该手机号没有发送过短信
            //可以继续发送
        }

        //接收请求手机号码
        //$phone = '123';
        //发送短信
        /*$response = Sms::sendSms(
            "石头故事", // 短信签名
            "SMS_109545450", // 短信模板编号
            $phone, // 短信接收者
            Array(  // 短信模板中字段的值
                "code"=>rand(1000,9999),
                //"product"=>"dsd"
            )//,
        //"123"   // 流水号,选填
        );*/
        //根据$response结果判断是否发送成功 $response->Code
        //保存验证码(SESSION或)REDIS

        $code = rand(1000,9999);
        $redis->set('captcha_'.$phone,$code,10*60);
        //验证验证码
        //$code = $redis->get('captcha_'.$phone);
        //if($code == '1234');

        echo '发送成功,验证码是'.$code;
        //return 'sucess';// 'fail'

    }
    //AJAX验证短信
    public function actionCheckSms($sms){
        //从redis获取验证码
        //返回对比结果
        //验证验证码
        //$code = $redis->get('captcha_'.$phone);
        //if($code == '1234');
    }


    //测试阿里大于短信发送功能
    public function actionSms(){
        //$sms = new Sms();
        $response = Sms::sendSms(
            "石头故事", // 短信签名
            "SMS_109545450", // 短信模板编号
            "18080011549", // 短信接收者
            Array(  // 短信模板中字段的值
                "code"=>rand(1000,9999),
                //"product"=>"dsd"
            )//,
            //"123"   // 流水号,选填
        );
        echo "发送短信(sendSms)接口返回的结果:\n";
        print_r($response);

        //frontend\components\Sms ---> require '@frontend\components\Sms.php';
        //Aliyun\Core\Config;   ---> require  '@Aliyun\Core\Config.php';
    }
}
