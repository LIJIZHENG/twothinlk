<?php
namespace console\controllers;

use yii\console\Controller;

class TaskController extends Controller{

    public function actionCancel(){
        //php脚本默认最大执行时间30秒
        set_time_limit(0);//该脚本执行时间无限制,一直执行下去
        //下单后1小时,如果未支付,自动取消该订单
        while (1){
            $time = time();
            //$sql = 'update `order` set status=0 where status=1 and '.$time.' - create_time > 3600';
            //\Yii::$app->db->createCommand($sql)->execute();
            //最快1秒,30秒 60秒
            sleep(1);
            //转码
            //iconv();            mb_convert_encoding();
            echo iconv('utf-8','gbk','清理完成');
            echo date('Y-m-d H:i:s',$time);
            echo "\n";
        }
    }
}