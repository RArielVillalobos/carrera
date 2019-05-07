<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProvinciaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Provincias';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="provincia-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Provincia', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'idProvincia',
            'nombreProvincia',
            'codigoIso31662',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
