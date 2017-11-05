<?php

namespace backend\controllers;

use backend\models\GoodsCategory;
use yii\web\Response;

class GoodsController extends \yii\web\Controller
{
    //添加商品分类
    public function actionAddCategory(){
        $model = new GoodsCategory();
        //parent_id设置默认值
        $model->parent_id = 0;
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                if($model->parent_id == 0){
                    //创建根节点
                    /*$countries = new Menu(['name' => 'Countries']);
                    $countries->makeRoot();*/
                    $model->makeRoot();
                    //$model->save();//不能使用save来创建节点
                    echo '添加根节点成功';exit;
                }else{
                    //添加子节点
                    /*$russia = new Menu(['name' => 'Russia']);
                    $russia->prependTo($countries);*/
                    $parent = GoodsCategory::findOne(['id'=>$model->parent_id]);
                    $model->prependTo($parent);
                    echo '添加子节点成功';exit;
                }

            }
        }


        return $this->render('add-category',['model'=>$model]);
    }

    //测试ztree
    public function actionTest(){
        //$this->layout = false;
        //不需要加载布局文件
        return $this->renderPartial('test');
    }
































//商品分类管理AJAX版
    public function actionAjax($filter){
        $this->enableCsrfValidation = false;
        \Yii::$app->response->format = Response::FORMAT_JSON;//将输出自动格式化为json格式
        $request = \Yii::$app->request;
        switch ($filter){
            case 'del'://删除商品分类
                $model = GoodsCategory::findOne($request->post('id'));
                if($model){
                    $model->deleteWithChildren();
                }
                break;
            case 'add'://添加商品分类
                $model = new GoodsCategory($request->post());
                if($model->parent_id){
                    //非顶级分类(子分类)
                    $parent = GoodsCategory::findOne(['id'=>$model->parent_id]);
                    $model->prependTo($parent);
                }else{
                    //顶级分类
                    $model->makeRoot();
                }
                return ['id'=>(string)$model->id,'parent_id'=>(string)$model->id,'name'=>(string)$model->name];
                break;
            case 'update'://更新商品分类
                $model = GoodsCategory::findOne($request->post('id'));
                if($model){
                    $model->load($request->post(),'');
                    $model->save();
                }
                break;
            case  'move'://移动商品分类
                $model = GoodsCategory::findOne($request->post('id'));
                $target = GoodsCategory::findOne($request->post('target_id'));
                if($target==null) $target = new GoodsCategory(['id'=>0]);
                switch ($request->post('type')){
                    case 'inner':
                        $model->parent_id=$target->id;
                        if($model->parent_id){
                            $model->appendTo($target);
                        }else{
                            $model->makeRoot();
                        }
                        //$model->prependTo($target);
                        break;
                    case 'prev':
                        $model->parent_id=$target->parent_id;
                        $model->insertBefore($target);
                        break;
                    case 'next':
                        $model->parent_id=$target->parent_id;
                        $model->insertAfter($target);
                        break;
                }
                //id target_id
                // type  "inner"：成为子节点，"prev"：成为同级前一个节点，"next"：成为同级后一个节点
                break;
            case 'getNodes'://获取所有分类节点数据
                //return GoodsCategory::find()->select(['id','parent_id','name'])->asArray()->all();
                return \yii\helpers\ArrayHelper::merge([['id'=>0,'parent_id'=>0,'name'=>'顶级分类']],\backend\models\GoodsCategory::getZtreeNodes());
                break;
        }
    }

    public function actionZtree()
    {
        return $this->render('ztree');
    }

}
