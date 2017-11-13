<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
//自动加载阿里云sdk
Yii::setAlias('@Aliyun', dirname(dirname(__DIR__)) . '/frontend/aliyun');
