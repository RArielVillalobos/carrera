<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Persona */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="persona-form">

<!-- vista del tab datos personales del formulario-->
<div class="datosPersonales" >
    <div id="cuilUsuario">
        <?= $form->field($usuario, 'cuilUsuario')->textInput() ?>
    </div>

    <div id="nombrePersona">
        <?= $form->field($persona, 'nombrePersona')->textInput(['maxlength' => true]) ?>
    </div>

    <div id="apellidoPersona">
        <?= $form->field($persona, 'apellidoPersona')->textInput(['maxlength' => true]) ?>
    </div>

    <div id="fechaNacPersona">
    <!-- utilizacion de un widget de kartik llamado DatePicker, permite escoger 
    una fecha desde un calendario permitiendo tambien seleccionar años o meses 
    con una mayor facilidad --> 
        <label>Fecha de Nacimiento</label>
        <?= DatePicker::widget([
            'name' => 'fechaNacPersona',
            'type' => DatePicker::TYPE_COMPONENT_PREPEND,
            'options' => ['placeholder' => 'Seleccione su fecha de nacimiento'],//Contenido que se mostrará dentro del input
            'language' => 'es',//definicion del lenguaje del widget
            'pluginOptions' => [
                'autoclose'=>true,
                'format' => 'yyyy-mm-dd',//definicion del formato de fecha 
            ]
        ])?>
    </div>

    <div id="idSexoPersona">
        <?= $form->field($persona, 'idSexoPersona')->dropDownList(
                \yii\helpers\ArrayHelper::map(\app\models\Sexo::find()->all(),'idSexo','descripcionSexo'),
                ['prompt'=>'Seleccione su sexo...']
        )->label('Sexo'); ?>
    </div>

    <div id="nacionalidadPersona">
        <?= $form->field($persona, 'nacionalidadPersona')->textInput(['maxlength' => true]) ?>
    </div>

</div>


    <div class="form-group">
        <?= Html::Button('Siguiente', ['class' => 'btn btn-info']) ?>
    </div>



</div>