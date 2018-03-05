<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\datamodels_local\KillProduct */

$this->title = Yii::t('app', '添加秒杀');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '秒杀'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
