<?php
/* @var $this \yii\web\View */
/* @var $form \yii\bootstrap\ActiveForm */
/* @var $model \devzyj\yii2\oauth2\server\demos\models\DemoAuthorizationForm */
/* @var $clientEntity \devzyj\yii2\oauth2\server\entities\ClientEntity  */
/* @var $scopeEntities \devzyj\yii2\oauth2\server\entities\ScopeEntity[]  */
/* @var $user \yii\web\User */
/* @var $loginUrl string */

use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Tabs;
use devzyj\yii2\oauth2\server\demos\assets\DemoAsset;

DemoAsset::register($this);
$this->title = 'OAuth2 Authorization Demo';
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="wrap">
    <div class="container">
        <div class="site-login">
            <h2><?= Html::encode($this->title) ?></h2>
            
            <p>Client: <?= Html::encode($clientEntity->getIdentifier()) ?> | <?= Html::a('Login Page', $loginUrl) ?></p>
            
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
                    'labelOptions' => ['class' => 'col-lg-1 control-label'],
                ],
                'enableAjaxValidation' => false,
            ]); ?>
                
                <?php 
                    if ($model->mode === null) {
                        $model->mode = $user->getIsGuest() ? $model::AUTHORIZATION_MODE_CHANGE : $model::AUTHORIZATION_MODE_LOGGED;
                    }
                    
                    echo Html::activeHiddenInput($model, 'mode');
                ?>
            
                <?php 
                    $loginContent[] = $form->field($model, 'username')->textInput(['autofocus' => true]);
                    $loginContent[] = $form->field($model, 'password')->passwordInput();
                    $items[] = [
                        'label' => $user->getIsGuest() ? 'Login' : 'Change User',
                        'content' => Html::tag('p', implode('', $loginContent)),
                        'linkOptions' => ['data-authorization-mode' => $model::AUTHORIZATION_MODE_CHANGE],
                        'active' => $model->mode === $model::AUTHORIZATION_MODE_CHANGE ? true : false,
                    ];
                    
                    if (!$user->getIsGuest()) {
                        $userIdentity = $user->getIdentity();
                        $authorizationContent[] = Html::staticControl('User ID: ' . $userIdentity->getId());
                        $items[] = [
                            'label' => 'Authorization',
                            'content' => Html::tag('p', implode('', $authorizationContent)),
                            'linkOptions' => ['data-authorization-mode' => $model::AUTHORIZATION_MODE_LOGGED],
                            'active' => $model->mode === $model::AUTHORIZATION_MODE_LOGGED ? true : false,
                        ];
                    }
                    
                    echo Tabs::widget([
                        'items' => $items,
                        'clientEvents' => [
                            'shown.bs.tab' => 'function (e) {
                                $("#' . Html::getInputId($model, 'mode') . '").val($(e.target).data("authorizationMode"));
                            }',
                        ],
                    ]);
                ?>
            
                <div class="form-group">
                    <div class="col-lg-offset-1 col-lg-11">
                        <?= Html::submitButton('Authorization', ['class' => 'btn btn-primary', 'id' => 'authorization-button']) ?>
                    </div>
                </div>
        
                <?php 
                    $items = [];
                    foreach ($scopeEntities as $scope) {
                        $items[$scope->getIdentifier()] = $scope->name;
                    }

                    if ($model->scopes === null) {
                        $model->scopes = array_keys($items);
                    }
                    
                    echo $form->field($model, 'scopes')->checkboxList($items);
                ?>
                
                <div style='color:red; display:none;'>
                    <?= $form->errorSummary($model) ?>
                </div>
                
            <?php ActiveForm::end(); ?>
            
            <?= $this->render('_footer') ?>
            
        </div>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>