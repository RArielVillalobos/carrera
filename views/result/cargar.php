<?php
/**
 * Created by PhpStorm.
 * User: ariel
 * Date: 06/09/19
 * Time: 02:36
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<br>
<br>
<br>
<br>
<div class="container">

    <div class="row">
        <div class="col-8">
            <h3>Cargar resultado</h3>
            <div class="pago-form">

                <?php $form = ActiveForm::begin([
                        'options' => ['enctype' => 'multipart/form-data'],
                        "action"=>"index.php?r=result%2Fprocesar",
                ]); ?>


                    <label>Archivo</label>
                    <div class="form-group">
                        <?= Html::input('textarea','archivo','', ['style'=>'width: 800px; height: 200px;']) ?>
                        <br>
                    </div>




                    <button class="btn btn-danger btn-sm" type="submit">Cargar</button>



                <?php ActiveForm::end(); ?>

            </div>

        </div>

    </div>

</div>

