<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Pago */
/* @var $form yii\widgets\ActiveForm */
//formulario para que el participante realice el pago-----------------------------
?>
<?php
       if($usuario->dniUsuario == $equipo->dniCapitan):?>
       <div style='font-family: "Roboto", sans-serif;font-size: 14px;color:#6A8A7C;'>
       <?Php echo 'Eres capitan del equipo '.Html::encode($equipo->nombreEquipo);?>
        </div>
        <div style='font-family: "Roboto", sans-serif;font-size: 14px;color:#6A8A7C;'> 
        <?Php echo 'DNI '.Html::encode($equipo->dniCapitan);?>
        </div>
        
<?Php else: ?>
        <div style='font-family: "Roboto", sans-serif;font-size: 14px;color:#6A8A7C;'>
        <?Php  echo 'Eres integrante del equipo '.Html::encode($equipo->nombreEquipo);?>
        </div>
        <div style='font-family: "Roboto", sans-serif;font-size: 14px;color:#6A8A7C;'>
        <?Php  echo 'DNI '.Html::encode($usuario->dniUsuario);?>
        </div>
 <?Php endif ?>
       <div style='font-family: "Roboto", sans-serif;font-size: 14px;color:#6A8A7C;'>
          <?= 'Carrera '.Html::encode($tipocarrera->descripcionCarrera) ?>
       </div>
       <div style='font-family: "Roboto", sans-serif;font-size: 14px;color:#6A8A7C;'>
          <?= 'Costo de inscripcion $'.Html::encode($importecarrera->importe) ?>
       </div>
       <br><br>
<div class="pago-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    <figure>
        <?= $form->field($model, 'importePagado')->textInput(['placeholder'=>'Importe a pagar']) ?>
        <?Php if($saldo != 0):?>
        <figcaption><strong>Tu saldo a pagar es de $ <?=Html::encode($saldo)?></strong></figcaption>
        <?Php endif ?>
    </figure>

    <?= $form->field($model, 'entidadPago')->textInput(['placeholder'=>'Entidad de pago','maxlength' => true]) ?>

    <!--<?= $form->field($model, 'idPersona')->textInput() ?>-->

    <!--<?= $form->field($model, 'idImporte')->textInput() ?>-->
    
    <!--<?= $form->field($model, 'idEquipo')->textInput() ?>-->
    <?= $form->field($model, 'imagenComprobante')->fileInput() ?>
   
    <div class="form-group">
    <?php
      if ($model->isNewRecord) 
             echo Html::submitButton('Acreditar pago', ['class' => 'btn btn-success']);
      else	 
	      echo Html::submitButton('Actualizar', ['class' => 'btn btn-success']);
	  ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
