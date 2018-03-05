<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\datamodels_local\KillProduct */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?=Yii::t('app',$this->title)?></h3>
    </div>
    <div class="panel-body">
        <div class="article-form">
            <?php $form = ActiveForm::begin(); ?>
            <div class="row">
                <div class="col-lg-6 col-sm-6">
                    <?= $form->field($model, 'name')->input('text',['maxlength' => true])?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2 col-sm-2">
                    <?= $form->field($model, 'partition')->input('text',[])?>
                </div>
                <div class="col-lg-2 col-sm-2">
                    <?= $form->field($model, 'total')->input('text',['maxlength' => true])?>
                </div>
                <div class="col-lg-2 col-sm-2">
                    <?= $form->field($model, 'rest')->input('text',['maxlength' => true])?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2">
                    <?= $form->field($model, 'mode')->dropDownList([0=>'固定',1=>'随机'],['prompt'=>'请选择模式'])?>
                </div>
                <div class="col-lg-2 col-sm-2">
                    <?= $form->field($model, 'num')->textInput(['maxlength' => true])?>
                </div>
                <div class="col-lg-2">
                    <?= $form->field($model, 'status')->dropDownList([0=>'不可用',1=>'可用',2=>'到期'],['prompt'=>'请选择状态'])?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-2">
                    <?= $form->field($model, 'product_id')->input('text',['maxlength'=>true])?>
                </div>
                <div class="col-lg-2">
                    <?= $form->field($model, 'add_params')->input('text',['maxlength'=>true])?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2">
                    <?= $form->field($model, 'start_time')->input('text',['maxlength'=>true,'placeholder'=>time()])?>
                </div>
                <div class="col-lg-2">
                    <?= $form->field($model, 'end_time')->input('text',['maxlength'=>true,'placeholder'=>time()])?>
                </div>
            </div>

            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
