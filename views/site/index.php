<?php

/** @var yii\web\View $this */
/** @var app\models\User $users */
/** @var app\models\Task $tasks */

/** @var app\models\TaskForm $model */

use app\controllers\Helper\UtilityHelper;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\bootstrap5\Modal;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

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
                    'label' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
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

            $currentUser = UtilityHelper::getUserInformation()->username;
            $filteredUsers = array_filter($users, function($user) use ($currentUser) {
                return $user['username'] != $currentUser;
            });
            $usersOptions = ArrayHelper::map((array)$filteredUsers, 'username', 'fullname');
            ?>

            <div class="row">
                <div class="col-lg-6 mb-2">
                    <?= $form->field($model, 'assignee')->widget(Select2::className(), [
                        'data' => $usersOptions,
                        'options' => ['placeholder' => 'Select a user ...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#kartik-modal'
                        ],
                    ]); ?>
                </div>
                <div class="col-lg-6 mb-2">
                    <?= $form->field($model, 'title')->textInput() ?>
                </div>
                <div class="col-lg-12 mb-2">
                    <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
                </div>
                <div class="col-lg-6 mb-2">
                    <?= $form->field($model, 'priority')->widget(Select2::className(), [
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
                    ]); ?>
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
                        echo GridView::widget([
                            'dataProvider' => $tasks,
                            'columns' => [
                                ['class' => 'yii\grid\SerialColumn'],
                                [
                                    'attribute' => 'title', 'label' => 'Title',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        $truncatedTitle = StringHelper::truncate($model->title, 20);
                                        return '<a href="/task/view?task='.$model->slug.'&edit_mode=false">' . $truncatedTitle . '</a>';
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
                    ?>
                <?php  } ?>
            </div>
        <?php } ?>
    </div>
</div>
