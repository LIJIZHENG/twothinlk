<h1>京西商场收银台</h1>
<p>订单编号:<?=$order->out_trade_no?>
金额:<?=$order->total_fee/100?>元</p>
<img src="<?=\yii\helpers\Url::to(['qr','content'=>$code_url])?>">