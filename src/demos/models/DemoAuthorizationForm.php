<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\demos\models;

use yii\helpers\Html;
use yii\web\User;

/**
 * DemoAuthorizationForm class.
 *
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class DemoAuthorizationForm extends DemoLoginForm
{
    /**
     * @var string 已登录用户授权模式。
     */
    const AUTHORIZATION_MODE_LOGGED = 'authorization-logged';

    /**
     * @var string 更改用户授权模式。
     */
    const AUTHORIZATION_MODE_CHANGE = 'authorization-change';
    
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        $when = function ($model) {
            return $model->mode === self::AUTHORIZATION_MODE_CHANGE;
        };
        
        $whenClient = "function (attribute, value) {
            return $('#" . Html::getInputId($this, 'mode') . "').val() === '" . self::AUTHORIZATION_MODE_CHANGE . "'
        }";
        
        return [
            [['mode', 'scopes'], 'required'],
            [['username', 'password'], 'required', 'when' => $when, 'whenClient' => $whenClient],
            [['username', 'password'], 'string', 'when' => $when, 'whenClient' => $whenClient],
            [['password'], 'validatePassword', 'when' => $when, 'whenClient' => $whenClient],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function authorization(User $user)
    {
        if (!$this->validate()) {
            return false;
        }
        
        if ($this->mode === self::AUTHORIZATION_MODE_CHANGE) {
            if (!$this->loginInternal($user)) {
                return false;
            }
        }
        
        $this->setApproved($user);
        return true;
    }
}
