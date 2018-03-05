<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\datamodels_local\KillProduct */

$this->title = Yii::t('app', '更新秒杀: ', [
    'modelClass' => 'Article',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '秒杀'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', '更新');
?>
<div class="article-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
