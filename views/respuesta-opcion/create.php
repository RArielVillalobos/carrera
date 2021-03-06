<?php

/* ---------------------------------------------------------------------------------------------
-- Vista que nos permite la generación y carga de las opciones de las opciones de respuesta posteriores
-- ----------------------------------------------------------------------------------------------*/
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Respuestaopcion */
/* @var $pregunta app\models\Pregunta */
/* @var $encTipo app\models\Encuesta */

$this->title = 'Crear Opciones de respuesta';

?>
<div class="respuestaopcion-create reglamento-container">

    <h1><?= Html::encode($this->title) ?></h1>
    <h2><?php echo Html::encode("Pregunta: ".$pregunta->pregDescripcion) ?></h2>
    
    <!-- Muestra las opciones cargadas con anterioridad -->
	<?php if($opciones!=null): ?>
		<?php $i=1; ?>
		<div class="alert alert-success">

			<p>Opciones ya cargadas:</p>
			<?php foreach($opciones as $unaOpcion): ?>
				<p><strong><?php echo $i." - ".$unaOpcion['opRespvalor'] ?></strong></p>
				<?php $i++ ?>
			<?php endforeach ?>
		</div>
	<?php endif ?>

    <?= $this->render('_form', [
        'model' => $model,
		'pregunta'=>$pregunta,
		'encTipo'=>$encTipo,
    ]) ?>

</div>
