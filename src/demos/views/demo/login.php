<?php
/* @var $this \yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model devzyj\yii2\oauth2\server\demos\models\DemoLoginForm */
/* @var $clientEntity devzyj\yii2\oauth2\server\entities\ClientEntity  */
/* @var $scopeEntities devzyj\yii2\oauth2\server\entities\ScopeEntity[]  */
/* @var $authorizationUrl string */

use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;
use devzyj\yii2\oauth2\server\demos\assets\DemoAsset;

DemoAsset::register($this);
$this->title = 'OAuth2 Login Demo';
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
            
            <p>Client: <?= Html::encode($clientEntity->getIdentifier()) ?> | <?= Html::a('Authorization Page', $authorizationUrl) ?></p>
            
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
                    'labelOptions' => ['class' => 'col-lg-1 control-label'],
                ],
                'enableAjaxValidation' => true,
            ]); ?>
        
                <?php 
                    if ($model->mode === null) {
                        $model->mode = $model::LOGIN_MODE_NORMAL;
                    }
                    
                    echo Html::activeHiddenInput($model, 'mode');
                ?>
            
                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
        
                <?= $form->field($model, 'password')->passwordInput() ?>
        
                <div class="form-group">
                    <div class="col-lg-offset-1 col-lg-11">
                        <?php 
                            if ($model->mode === $model::LOGIN_MODE_AUTHORIZATION) {
                                $content = 'Authorization';
                            } else {
                                $content = 'Login';
                            }
                            
                            echo Html::submitButton($content, ['class' => 'btn btn-primary', 'id' => 'login-button']);
                        ?>
                        
                        <?php 
                            $checkboxID = 'login-authorization-checkbox';
                            if ($model->mode === $model::LOGIN_MODE_AUTHORIZATION) {
                                $selected = true;
                            } else {
                                $selected = false;
                            }
                            
                            $checkbox = Html::checkbox($checkboxID, $selected, ['id' => $checkboxID]);
                            $label = Html::label($checkbox . ' Login And Authorization');
                            echo Html::tag('div', $label, ['class' => 'checkbox']);
                            
                            $this->registerJs("
                                jQuery('#{$checkboxID}').click(function () {
                                    var modeElement = $('#" . Html::getInputId($model, 'mode') . "');
                                    var scopesElement = $('#scopes-div');
                                    var buttonElement = $('#login-button');
                
                                    if ($(this).is(':checked')) {
                                        modeElement.val('" . $model::LOGIN_MODE_AUTHORIZATION . "');
                                        scopesElement.removeClass('hidden');
                                        buttonElement.val('Authorization').html('Authorization');
                                    } else {
                                        modeElement.val('" . $model::LOGIN_MODE_NORMAL . "');
                                        scopesElement.addClass('hidden');
                                        buttonElement.val('Login').html('Login');
                                    }
                                });
                            ");
                        ?>
                    </div>
                </div>
                
                <?php
                    $checkboxListClass[] = 'form-group';
                    if ($model->mode === $model::LOGIN_MODE_NORMAL) {
                        $checkboxListClass[] = 'hidden';
                    }
                
                    $items = [];
                    foreach ($scopeEntities as $scope) {
                        $items[$scope->getIdentifier()] = $scope->name;
                    }
                    
                    if ($model->scopes === null) {
                        $model->scopes = array_keys($items);
                    }
                    
                    echo $form->field($model, 'scopes', ['options' => ['id' => 'scopes-div', 'class' => $checkboxListClass]])->checkboxList($items);
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