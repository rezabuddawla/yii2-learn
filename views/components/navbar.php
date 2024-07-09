<?php

use app\controllers\Helper\UtilityHelper;
use app\views\components\HtmlHelper;
$currentController = Yii::$app->controller->id;
$currentAction = Yii::$app->controller->action->id;
?>
<header id="header" class="bg-dark">
    <nav class="navbar navbar-expand-md container" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/"><?= Yii::$app->name ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <?php if (Yii::$app->user->isGuest) { ?>
                            <a class="nav-link active" aria-current="page" href="/site/login">Login</a>
                        <?php } else { ?>
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= UtilityHelper::username() ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <form id="logout-form" action="/site/logout" method="post">
                                        <?= HtmlHelper::csrfInput() ?>
                                        <button type="submit" class="dropdown-item">
                                            Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        <?php } ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>