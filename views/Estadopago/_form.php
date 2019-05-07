<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Estadopago */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="estadopago-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idEstadoPago')->textInput() ?>

    <?= $form->field($model, 'descripcionEstadoPago')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
