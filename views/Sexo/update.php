<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Sexo */

$this->title = 'Update Sexo: ' . $model->idSexo;
$this->params['breadcrumbs'][] = ['label' => 'Sexos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->idSexo, 'url' => ['view', 'id' => $model->idSexo]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sexo-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
