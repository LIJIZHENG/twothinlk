<?php
$form = \yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'name')->textInput();
echo $form->field($model,'description')->textInput();

echo $form->field($model,'hobby')->checkboxList(['zhangsan'=>'张三','lisi'=>'李四']);
echo \yii\bootstrap\Html::submitButton('提交',['class'=>'btn btn-info']);
\yii\bootstrap\ActiveForm::end();