<?php
/** @var yii\web\View $this */
/** @var app\models\User $users */
/** @var app\models\Task $task */
/** @var app\models\Task $edit_mode */

use app\views\components\HtmlHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Task View';
$this->params['navbar'] = true;
$this->params['footer'] = true;
?>


<div class="site-index">
    <?php if ($task) { ?>
        <div id="animated-thumbnails-gallery">
            <div class="d-flex justify-content-between">
                <h3 class="fw-bolder">
                    <?= $task->title ?>
                </h3>
                <div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="edit-mode-toggle" <?= $edit_mode ? 'checked' : '' ?>
                        <label class="form-check-label" for="edit-mode-toggle">Edit Mode</label>
                    </div>
                    <div>
                        <form id="delete-task-form" action="/task/delete?task=<?= $task->slug ?>" method="post">
                            <?= HtmlHelper::csrfInput() ?>
                            <?= Html::submitButton('Delete Task', ['class' => 'btn btn-danger', 'name' => 'delete-button']) ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="w-50 text-wrap text-break">
                <h6><span class="fw-medium">Description: </span><span class="fw-normal"><?= $task->description ?></span></h6>
            </div>
            <h6><span class="fw-medium">Priority: </span><?= HtmlHelper::getPriority($task->priority) ?></h6>
            <h6 id="status" class="<?= $edit_mode ? 'd-none' : ''?>"><span class="fw-medium">Status: </span><?= $task->status ?></h6>
            <div class="<?= !$edit_mode ? 'd-none' : ''?>" id="edit-form">
                <form action="/task/update?task=<?= $task->slug ?>" method="post">
                    <?= HtmlHelper::csrfInput() ?>
                    <label for="statusForm"></label>
                    <div class="d-flex gap-2">
                        <select name="status" id="statusForm" class="">
                            <?php foreach (array('New', 'In Progress', 'Testing', 'Deployed') as $elem) { ?>
                                <option value="<?= $elem ?>" <?= $task->status == $elem ? 'selected' : '' ?>><?= $elem ?></option>
                            <?php } ?>
                        </select>
                        <div>
                            <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'name' => 'edit-button']) ?>
                        </div>
                    </div>
                </form>
            </div>
            <h6><span class="fw-medium">Assigned By: </span><?= $task->created_by ?></h6>



            <p class="">Attachments:</p>
            <div id="img-gallery">
                <?php foreach ($task->taskImages as $image) {
                    $imageUrl = Url::to('@web/uploads/' . $image->image_path);
                ?>
                    <a href="<?= $imageUrl ?>">
                        <?= Html::img($imageUrl, ['alt' => $image->image_path, 'class' => 'img-thumbnail', 'style' => 'width: 150px;']);?>
                    </a>
                <?php } ?>
            </div>
            <script>
                let gallery = document.getElementById('img-gallery');
                lightGallery(gallery, {
                    controls: true,
                    counter: true,
                    download: true,
                    plugins: [lgZoom, lgThumbnail],
                })

                document.addEventListener('DOMContentLoaded', function() {
                    const toggle = document.getElementById('edit-mode-toggle');
                    const editForm = document.getElementById('edit-form');
                    const status = document.getElementById('status');

                    toggle.addEventListener('change', function() {
                        if (toggle.checked) {
                            editForm.classList.remove('d-none');
                            status.classList.add('d-none');
                        } else {
                            editForm.classList.add('d-none');
                            status.classList.remove('d-none');
                        }
                    });
                });


                document.addEventListener('DOMContentLoaded', function() {
                    const deleteForm = document.getElementById('delete-task-form');

                    deleteForm.addEventListener('submit', function(event) {
                        event.preventDefault();

                        const confirmation = confirm('Are you sure you want to delete this task?');

                        if (confirmation) {
                            deleteForm.submit();
                        }
                    });
                });

            </script>

        </div>
    <?php } else { ?>
        <div>
            <div class="alert alert-danger" role="alert">
                <b>401 Unauthorized</b>
            </div>
        </div>
    <?php } ?>
</div>


