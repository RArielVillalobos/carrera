<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Usuario;
/* @var $this yii\web\View */
/* @var $model app\models\Gestores */

$this->title ='Administrativo: '. $model->nombreGestor;

\yii\web\YiiAsset::register($this);
?>
<div class="gestores-view reglamento-container">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
    <?php
           $descRol=Usuario::findIdentity($_SESSION['__id']);
            if($descRol->idRol==2){
               echo Html::a('Actualizar', ['update', 'id' => $model->idGestor], ['class' => 'btn btn-primary']); 
               echo Html::a('Eliminar', ['delete', 'id' => $model->idGestor], [
                    'class' => 'btn btn-danger',
                 'data' => [
                    'confirm' => 'Esta seguro que quiere eliminar este registro???',
                   'method' => 'post',
                ],
              ]);     
            }
            ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'idGestor',
            'nombreGestor',
            'apellidoGestor',
            'telefonoGestor',
            [ 'label'=>'Dni Usuario',
                'attribute' => 'idUsuario',
                 'value' => function($model) {
                     return ($model->usuario->dniUsuario);
                 },
                ],
                [
                    'attribute' => 'rol',
                    'value' => function($model) {
                        return ($model->usuario->rol->descripcionRol);
                        }
                ],
                [
                    'attribute' => 'email',
                    'value' => function($model) {
                        return ($model->usuario->mailUsuario);
                        }
                    ],
        ],
    ]) ?>

</div>
