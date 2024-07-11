<?php

/** @var yii\web\View $this */
/** @var app\models\Task $tasks */

/** @var app\models\TaskForm $model */

use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\bootstrap5\Modal;
use yii\grid\GridView;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->title = 'Task Manager';
$this->params['navbar'] = true;
$this->params['footer'] = true;
?>
<div class="site-index">
    <div class="container">
        <?php if (Yii::$app->user->isGuest) { ?>
        <div class="alert alert-danger" role="alert">
            Please <a href="/site/login" class="fw-bold">Login</a> to <span class="fw-bold">create/view</span> tasks.
        </div>
        <?php } else {
            Modal::begin([
                'options' => [
                    'id' => 'kartik-modal',
                    'tabindex' => false
                ],
                'title' => 'Create new task',
                'size' => Modal::SIZE_EXTRA_LARGE,
                'toggleButton' => [
                    'label' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-1" style="width: 25px">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                    <span>Create New Task</span>',
                    'class' => 'btn btn-success d-flex align-items-center gap-2 mb-5',
                ],
            ]);

            $form = ActiveForm::begin([
                'id' => 'create-task-form',
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'col-lg-12 col-form-label mr-lg-3'],
                    'errorOptions' => ['class' => 'col-lg-7 invalid-feedback'],
                ],
                'options' => ['enctype' => 'multipart/form-data'],
                'action' => "/site/create-task",
            ]);

            ?>

            <div class="row">
                <div class="col-lg-6 mb-2">
                    <?php try {
                        echo $form->field($model, 'assignee')->widget(Select2::class, [
                            'options' => ['placeholder' => 'Select a user ...'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 3,
                                'dropdownParent' => '#kartik-modal',
                                'language' => [
                                    'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                                ],
                                'ajax' => [
                                    'url' => Url::to(['user-list']),
                                    'dataType' => 'json',
                                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'templateResult' => new JsExpression('function(user) { return user.text; }'),
                                'templateSelection' => new JsExpression('function (user) { return user.text; }'),
                            ],
                        ]);
                    } catch (Exception $e) {
                    } ?>
                </div>
                <div class="col-lg-6 mb-2">
                    <?= $form->field($model, 'title')->textInput() ?>
                </div>
                <div class="col-lg-12 mb-2">
                    <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
                </div>
                <div class="col-lg-6 mb-2">
                    <?php try {
                        echo $form->field($model, 'priority')->widget(Select2::class, [
                            'data' => [
                                "Low" => "Low",
                                "Medium" => "Medium",
                                "High" => "High",
                            ],
                            'options' => ['placeholder' => 'Select an Option ...'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'dropdownParent' => '#kartik-modal'
                            ],
                        ]);
                    } catch (Exception $e) {
                    } ?>
                </div>
                <div class="col-lg-6 mb-2">
                    <?= $form->field($model, 'images[]')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>
                </div>
                <div class="col-lg-12 mb-2">
                    <div>
                        <?= Html::submitButton('Create', ['class' => 'btn btn-success w-100', 'name' => 'create-button']) ?>
                    </div>
                </div>
            </div>

            <?php ActiveForm::end(); ?>



            <?php Modal::end(); ?>
            <div>
                <?php if (!$tasks) { ?>
                <div class="alert alert-danger" role="alert">
                    <b><s>Congratulation</s></b> you don't have any task left. 😔😟😩😭😖
                </div>
                <?php } else {
                    try {
                        echo GridView::widget([
                            'dataProvider' => $tasks,
                            'columns' => [
                                ['class' => 'yii\grid\SerialColumn'],
                                [
                                    'attribute' => 'title', 'label' => 'Title',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        $truncatedTitle = StringHelper::truncate($model->title, 20);
                                        return '<a href="/task/view?task=' . $model->slug . '&edit_mode=false">' . $truncatedTitle . '</a>';
                                    }
                                ],
                                [
                                    'attribute' => 'description',
                                    'label' => 'Description',
                                    'value' => function ($model) {
                                        $truncatedDesc = StringHelper::truncate($model->description, 70);
                                        return Html::encode($truncatedDesc);
                                    }
                                ],
                                'priority',
                                [
                                    'attribute' => 'created_by', 'label' => 'Assigned By',
                                ],
                            ],
                        ]);
                    } catch (Throwable $e) {
                    }
                } ?>
            </div>
        <?php } ?>
    </div>
</div>
