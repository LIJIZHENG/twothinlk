<?php
namespace frontend\controllers;


use yii\helpers\Url;
use yii\web\Controller;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;
use Endroid\QrCode\QrCode;

class PayController extends Controller{

    public $enableCsrfValidation = false;
    //测试微信支付

    public function actionWechat($order_id){
        $local_order = \frontend\models\Order::findOne(['id'=>$order_id]);
        if($local_order == null || $local_order->status != 1){
            //提示
        }


        //配置
        $app = new Application(\Yii::$app->params['wechat']);
        $payment = $app->payment;

        //创建微信支付订单

        $attributes = [
            'trade_type'       => 'NATIVE', // JSAPI，NATIVE，APP... 扫码支付填NATIVE
            'body'             => '京西商场订单',//订单标题
            'detail'           => 'IphoneX 256G',//订单详情
            'out_trade_no'     => $local_order->trade_no,//'234872dggjsghf8',//第三方支付交易号
            'total_fee'        => $local_order->total*100, // 订单金额 单位：分
            'notify_url'       => Url::to(['notify'],1),// http://www.yii2shop.com   /pay/notify // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            //'openid'           => '当前用户的 openid', // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
            // ...
        ];
        $order = new Order($attributes);

        //调统一下单api
        $result = $payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            //$prepayId = $result->prepay_id;
            $code_url = $result->code_url;
        }
        //var_dump($result);//code_url
        return $this->render('wechat',['order'=>$order,'code_url'=>$code_url]);
    }

    //支付结果通知地址
    public function actionNotify(){
        $app = new Application(\Yii::$app->params['wechat']);
        $response = $app->payment->handleNotify(function($notify, $successful){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = \frontend\models\Order::findOne(['trade_no'=>$notify->out_trade_no]);
            //$order = ($notify->out_trade_no);
            if (!$order) { // 如果订单不存在
                return 'Order not exist.'; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            // 如果订单存在
            // 检查订单是否已经更新过支付状态
            if ($order->status != 1) { // 假设订单字段“支付时间”不为空代表已经支付
                return true; // 已经支付成功了就不再更新了
            }
            // 用户是否支付成功
            if ($successful) {
                // 不是已经支付状态则修改为已经支付状态
                //$order->paid_at = time(); // 更新支付时间为当前时间
                //$order->status = 'paid';
                $order->status = 2;
            } else { // 用户支付失败
                //$order->status = 'paid_fail';
            }
            $order->save(); // 保存订单
            return true; // 返回处理完成
        });
        return $response;
    }

    //生成二维码
    public function actionQr($content){
        $qrCode = new QrCode($content);
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
    }

    //查询微信支付订单
    public function actionQuery(){
        $orderNo = "234872dggjsghf8";
        $app = new Application(\Yii::$app->params['wechat']);
        $payment = $app->payment;
        $r = $payment->query($orderNo);
        var_dump($r);
    }

}