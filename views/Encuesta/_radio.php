<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $encuesta app\models\Encuesta */
/* @var $pregunta app\models\Pregunta */
/* @var $opcion app\models\RespuestaOpcion */
/* @var $respuesta app\models\Respuesta */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $resp=[];?>

<?php foreach($opcion as $clave=>$valor2):?>
    
    <?php foreach($valor2 as $unaOpc):?>

        <?php if($unaOpc['idPregunta']==$idPregunta):?>
            <?php array_push($resp, $unaOpc); ?>
        <?php endif;?>

    <?php endforeach;?>

<?php endforeach?>

<?php $r=ArrayHelper::map($resp, 'idRespuestaOpcion', 'opRespvalor') ?>

<?= $form->field($respuesta, 'idRespuesta')->radioList( $r )->label(false);?>