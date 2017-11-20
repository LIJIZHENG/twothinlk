<?php
namespace frontend\controllers;

use frontend\models\Order;
use yii\db\Exception;
use yii\web\Controller;

class AdvanceController extends Controller{
    //防止短信被刷的方案
    public function actionSms(){
      //1.设置短信发送间隔(1分钟只能发送一条,一小时只能发7条)


        //通过ip限制(不适用)
        //2.验证码(在发送短信前先输入验证码,验证通过,再发送短信)
    }
    //首页静态化
    //清理超时未支付订单
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
            echo '清理完成'.$time;
        }
    }


    //解决高并发超卖问题(秒杀)
    public function actionStock(){
        $redis = new \Redis();
        //前提:商品库存需要保存到redis
        //$redis->set('stock_'.$goods_id,99);

        $order = new Order();
        //开启事务
        $transaction = Yii::$app->db->beginTransaction();
        //保存订单
        try {

            $order->save();
            //判断库存
            $carts = [];
            foreach ($carts as $cart) {
                //先扣减库存(扣减并取出)
                $stock = $redis->decrBy('stock_' . $cart->goods_id, $cart->amount);
                //记录库存扣减情况
                $redis->hSet('decr_stock_'.$order->id,$cart->goods_id,$cart->amount);
                //$decr_stock_20171233213 = [1=>2,4=>5];
                //检测商品库存是否足够
                if ($stock < 0) {
                    throw new Exception($cart->goods->name . '商品库存不足');
                }


                /*$order_goods = new OrderGoods();
                $order_goods->order_id = $order->id;
                $order_goods->goods_id = $cart->goods_id;
                $order_goods->goods_name = $cart->goods->name;
                //....
                $order_goods->amount = $cart->amount;
                $order_goods->total = $order_goods->price*$order_goods->amount;
                $order_goods->save();
                $order->total += $order_goods->total;//订单金额累加*/
                //扣减商品库存
                /*$cart->goods->stock -= $cart->amount;
                $cart->goods->save();*/
                //Goods::updateAllCounters(['stock'=>-$cart->amount],['id'=>$cart->goods_id]);
            $transaction->commit();
            }
        }catch (Exception $e){
            //捕获库存不足异常
            //手动回滚库存
            $decr_stocks = $redis->hGetAll('decr_stock_'.$order->id);
            //$decr_stock_20171233213 = [1=>2,4=>5];
            foreach ($decr_stocks as $goods_id=>$decr_stock){
                $redis->incrBy('stock_' . $goods_id,$decr_stock);
            }

            //
            $transaction->rollback();
        }

    }

    //后台准备秒杀商品
    public function actionAdmin(){
        $redis = new \Redis();
        $datas = ['iphoneX','iphoneX','iphoneX','iphoneX','iphoneX'];
        foreach ($datas as $data){
            $redis->lPush('goods_1',$data);
        }
        echo '秒杀商品已准备好';
    }
    //前台进行秒杀
    public function actionMiao($id){
        //list商品秒杀 5台iphoneX 999台
        $redis = new \Redis();
        //2017-11-21 10:00开始 11:00结束
        $start_time = strtotime('2017-11-21 10:00:00');
        $end_time = strtotime('2017-11-21 11:00:00');
        $time = time();
        /*if(strtotime('2017-11-21 10:00:00') <= time() && time() <= strtotime('2017-11-21 11:00:00')){
            //允许
        }*/
        if($time<$start_time){
            echo '活动还未开始';exit;
        }elseif ($time>$end_time){
            echo '活动已结束';exit;
        }
        //$a ssr  $b sr   $c r  其他未中奖
        $a= 55;
        $b= 12;
        $c = 6;
        $d = 100;//未中奖
        $r = rand(1,($a+$b+$c+$d));
        if($r <= $a){
            echo 'SSR';//$a
        }elseif ($r <= ($a+$b) ){
            echo 'SR';//$b
        }elseif($a+$b+$c){
            echo 'R';//$c
        }else{
            echo '未中奖';//$d
        }

        //中奖率为0.3%  1400人    3/1000
        //1% 1-100 1
        $rand = rand(1,1000);
        $percent = date('s');//中奖率
        if($rand <= $percent){
            //中奖
        }
        //$rand = 1;//1%
        //$rand = 1,2
        //2%   1,2     3%  1,2,3   ----   99%  1-99
        if($rand > $percent){
            echo '秒杀失败,请稍后再试';exit;
        }


        $result = $redis->lPop('goods'.$id);
        if($result){
            echo '恭喜xxx,秒杀到商品'.$result;
        }else{
            echo '秒杀已结束';
        }
    }
}