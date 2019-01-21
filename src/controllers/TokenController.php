<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\controllers;

use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;
use devzyj\yii2\oauth2\server\actions\TokenAction;

/**
 * TokenController class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class TokenController extends \yii\web\Controller
{
    /**
     * {@inheritdoc}
     */
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::class,
                'actions' => $this->verbs(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        /* @var $module \devzyj\yii2\oauth2\server\Module */
        $module = $this->module;
        
        return [
            'index' => [
                'class' => TokenAction::class,
                'grantTypeClasses' => $module->grantTypeClasses,
                'userRepositoryClass' => $module->userRepositoryClass,
                'defaultScopes' => $module->defaultScopes,
                'accessTokenDuration' => $module->accessTokenDuration,
                'accessTokenCryptKey' => $module->accessTokenCryptKey,
                'authorizationCodeCryptKey' => $module->authorizationCodeCryptKey,
                'refreshTokenDuration' => $module->refreshTokenDuration,
                'refreshTokenCryptKey' => $module->refreshTokenCryptKey,
            ],
        ];
    }
    
    /**
     * Declares the allowed HTTP verbs.
     * Please refer to [[VerbFilter::actions]] on how to declare the allowed verbs.
     *
     * @return array the allowed HTTP verbs.
     */
    protected function verbs()
    {
        return [
            'index' => ['POST'],
        ];
    }
}
