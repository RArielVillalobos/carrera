<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ResultSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Results';
$this->params['breadcrumbs'][] = $this->title;
?>
<br>
<br>
<br>
<div class="result-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Result', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'idResultado',
            'numEquipo',
            'tiempoLlegada',
            'respuestasCorrectas',
            'bolsasCompletas',
            //'penalizacionBolsa',
            //'trivia',
            //'total',
            //'categoria',
            //'cantPersonas',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
