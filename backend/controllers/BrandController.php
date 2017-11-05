<?php

namespace backend\controllers;


use backend\models\Brand;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

use yii\web\UploadedFile;

// 引入鉴权类
use Qiniu\Auth;

// 引入上传类
use Qiniu\Storage\UploadManager;

class BrandController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;
    //添加品牌
    public function actionAdd()
    {
        $model = new Brand();
        if($model->load(\Yii::$app->request->post()) && $model->validate()){
            $model->save();
            \Yii::$app->session->setFlash('success','品牌添加成功');
            return $this->redirect(['brand/index']);
        }
        return $this->render('add',['model'=>$model]);
    }

    //品牌列表
    public function actionIndex()
    {
        //只显示未删除的品牌，status != -1
        $query = Brand::find()->where(['!=','status','-1']);
        $pager = new Pagination([
            'totalCount'=>$query->count(),
            'defaultPageSize'=>10,
        ]);
        $models = $query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('index',['models'=>$models,'pager'=>$pager]);
    }


    //处理ajax图片上传
    public function actionUpload(){
        if(\Yii::$app->request->isPost){
            $imgFile = UploadedFile::getInstanceByName('file');
            //判断是否有文件上传
            if($imgFile){
                $fileName = '/upload/'.uniqid().'.'.$imgFile->extension;
                $imgFile->saveAs(\Yii::getAlias('@webroot').$fileName,0);
                //=========将图片上传到七牛云============
                // 需要填写你的 Access Key 和 Secret Key
                $accessKey ="hqNnJqiC0r7xoCcroZKMbqgbmELaZPyYmrbnNIDg";
                $secretKey = "KwzsOiQ7UbAesjwXKh5fMblJCbbrOHuN6grCQxzq";
                //对象存储 空间名称
                $bucket = "php0711";
                $domain = 'oyxduf0fk.bkt.clouddn.com';

                    // 构建鉴权对象
                $auth = new Auth($accessKey, $secretKey);

                // 生成上传 Token
                $token = $auth->uploadToken($bucket);

                // 要上传文件的本地路径
                $filePath = \Yii::getAlias('@webroot').$fileName;

                // 上传到七牛后保存的文件名
                $key = $fileName;

                // 初始化 UploadManager 对象并进行文件的上传。
                $uploadMgr = new UploadManager();

                // 调用 UploadManager 的 putFile 方法进行文件的上传。
                list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
                //echo "\n====> putFile result: \n";
                if ($err !== null) {
                    //上传失败 打印错误
                    //var_dump($err);
                    return Json::encode(['error'=>$err]);
                } else {
                    //没有出错  打印上传结果
                    //var_dump($ret);
                    return Json::encode(['url'=>'http://'.$domain.'/'.$fileName]);
                }
                //====================================
                //return Json::encode(['url'=>$fileName]);
            }
        }


    }


    //测试七牛云上传
    public function actionTest(){


        // 需要填写你的 Access Key 和 Secret Key
        $accessKey ="hqNnJqiC0r7xoCcroZKMbqgbmELaZPyYmrbnNIDg";
        $secretKey = "KwzsOiQ7UbAesjwXKh5fMblJCbbrOHuN6grCQxzq";
                //对象存储 空间名称
        $bucket = "php0711";

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);

        // 要上传文件的本地路径
        $filePath = \Yii::getAlias('@webroot').'/upload/59fe779423988.jpg';

        // 上传到七牛后保存的文件名
        $key = '/upload/59fe779423988.jpg';

        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        echo "\n====> putFile result: \n";
        if ($err !== null) {
            //上传失败 打印错误
            var_dump($err);
        } else {
            //没有出错  打印上传结果
            var_dump($ret);
        }

    }




}
