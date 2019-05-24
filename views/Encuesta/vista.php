<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $encuesta app\models\Encuesta */
/* @var $pregunta app\models\Pregunta */
/* @var $opcion app\models\RespuestaOpcion */

?>

<h2>Titulo de Encuesta: <?= $encuesta['encTitulo']?></h2>
<h4>Descripcion: <?= $encuesta['encDescripcion']?></h4>
<hr>

<div class="encuesta-form">
    <?php  $form=ActiveForm::begin([
        'method'=>'post',
        'action'=>Url::toRoute('respuesta/respuesta'),
        ]
    ); ?>
        <?php $text = 1;?>
        <?php $drop = 1;?>
        <?php $check = 1;?>
        <?php $radio = 1;?>
        <?php foreach($pregunta as $valor):?>
            <div class="form-group">
            <h3> <?php $idPregunta=$valor['idPregunta']; ?></h3>
            <h3> <?= $valor['pregDescripcion']; ?></h3>

            <?php if($valor['idRespTipo']==1){
                    echo $this->render('_texto',[
                        'opcion'=>$opcion,
                        'idPregunta'=>$idPregunta,
                        'form'=>$form,
                        'respuesta'=>$respuesta,
                        'num'=>$text,
                    ]);
                    $text++;

                }elseif($valor['idRespTipo']==2){
                    echo $this->render('_drop', [
                        'opcion'=>$opcion,
                        'idPregunta'=>$idPregunta,
                        'form'=>$form,
                        'respuesta'=>$respuesta,
                        'num'=>$drop,
                        ]);
                        $drop++;

                }elseif($valor['idRespTipo']==3){
                    echo $this->render('_check', [
                        'opcion'=>$opcion,
                        'idPregunta'=>$idPregunta,
                        'form'=>$form,
                        'respuesta'=>$respuesta,
                        'num'=>$check,
                        ]);
                        $check++;

                }elseif($valor['idRespTipo']==4){
                    
                    echo $this->render('_radio', [
                        'opcion'=>$opcion,
                        'idPregunta'=>$idPregunta,
                        'form'=>$form,
                        'respuesta'=>$respuesta,
                        'num'=>$radio,
                        ]);
                        $radio++;
                        
                }
                 
            ?>
            </div>
        <hr>
        <?php endforeach?>
        <?= $form->field($respuesta, 'idEncuesta')->hiddenInput(['value'=>$encuesta['idEncuesta']])->label(false) ?> 
        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>
