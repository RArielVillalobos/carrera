<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Sexo */

$this->title = 'Create Sexo';
$this->params['breadcrumbs'][] = ['label' => 'Sexos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sexo-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
