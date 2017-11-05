<?php

namespace backend\controllers;

use backend\filters\RbacFilter;
use backend\models\Brand;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use flyok666\uploadifive\UploadAction;
use flyok666\qiniu\Qiniu;
use yii\web\UploadedFile;

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
                return Json::encode(['url'=>$fileName]);
            }
        }


    }





}
