<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Pagoinscripcion */

$this->title = $model->idPago;
$this->params['breadcrumbs'][] = ['label' => 'Pagoinscripcions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="pagoinscripcion-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->idPago], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->idPago], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'idPago',
            'importe',
            'entidadpago',
            'fechapago',
            'pagado',
            ['attribute'=>'idPersona',
              'value'=>function($model){
                  return $model->persona->nombrePersona;
              }
            ],
            'imagencomprobante:image',
        ],
    ]) ?>

</div>