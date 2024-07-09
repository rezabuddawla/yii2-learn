<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Register';

?>

<div class="site-register">
    <div class="row justify-content-center h-75">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold"><?= Html::encode($this->title) ?></h5>
                </div>
                <div class="card-body">
                    <?php
                        $form = ActiveForm::begin([
                            'id' => 'register-form',
//                            'enableAjaxValidation' => true,  // Enable AJAX validation
//                            'validateOnChange' => true,     // Validate fields on change
//                            'validateOnBlur' => true,       // Validate fields on blur
                            'fieldConfig' => [
                                'template' => "{label}\n{input}\n{error}",
                                'labelOptions' => ['class' => 'col-lg-12 col-form-label mr-lg-3'],
                                'inputOptions' => ['class' => 'col-lg-3 form-control'],
                                'errorOptions' => ['class' => 'col-lg-7 invalid-feedback'],
                            ],
                        ])
                    ?>

                    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                    <?= $form->field($model, 'fullname')->textInput() ?>

                    <?= $form->field($model, 'email')->textInput() ?>

                    <?= $form->field($model, 'password')->passwordInput() ?>

                    <?= $form->field($model, 'password_repeat')->passwordInput() ?>

                    <div class="form-group">
                        <?= Html::submitButton('Register', ['class' => 'btn btn-primary w-100', 'name' => 'register-button']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>

<!--                    --><?php
//                    // AJAX validation script
//                    $this->registerJs("
//                        $('#register-form').on('beforeSubmit', function (e) {
//                            var form = $(this);
//                            $.ajax({
//                                url: form.attr('action'),
//                                type: 'post',
//                                data: form.serialize(),
//                                success: function (response) {
//                                    console.log(response);
//                                }
//                            });
//                            return false; // Prevent default form submission
//                        });
//                    ");
//                    ?>

                    <a href="/site/login">Already a member?</a>
                </div>
            </div>
        </div>
    </div>
</div>
