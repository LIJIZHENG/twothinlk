<?php
namespace frontend\models;

use yii\db\ActiveRecord;

class Order extends ActiveRecord{
    //定义配送方式
    public static $deliveries=[
        1=>['顺丰快递',25,'速度非常快,服务好,价格贵'],
        2=>['EMS',15,'速度快,服务一般,价格一般,全国各地都能到'],
        3=>['圆通',10,'速度快,服务一般,价格便宜'],
    ];
    //定义支付方式
}