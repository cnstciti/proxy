<?php
use yii\widgets\ActiveForm;

$this->title = 'Загрузка данных. Тип адреса HTTP';
?>

<h1><?= $this->title ?></h1>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'loadFile')->fileInput() ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>

