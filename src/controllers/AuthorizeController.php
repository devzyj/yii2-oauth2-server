<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\controllers;

use devzyj\yii2\oauth2\server\actions\AuthorizeAction;

/**
 * AuthorizeController class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class AuthorizeController extends \yii\web\Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        /* @var $module \devzyj\yii2\oauth2\server\Module */
        $module = $this->module;
        
        return [
            'index' => [
                'class' => AuthorizeAction::class,
                'authorizeTypeClasses' => $module->authorizeTypeClasses,
                'userRepositoryClass' => $module->userRepositoryClass,
                'defaultScopes' => $module->defaultScopes,
                'accessTokenDuration' => $module->accessTokenDuration,
                'accessTokenCryptKey' => $module->accessTokenCryptKey,
                'authorizationCodeDuration' => $module->authorizationCodeDuration,
                'authorizationCodeCryptKey' => $module->authorizationCodeCryptKey,
                'user' => $module->user,
                'loginUrl' => $module->loginUrl,
                'authorizationUrl' => $module->authorizationUrl,
            ],
        ];
    }
}
