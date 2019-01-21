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
 * DemoLoginForm class.
 *
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class DemoLoginForm extends \yii\base\Model
{
    /**
     * @var string 登录模式。
     */
    const LOGIN_MODE_NORMAL = 'login-normal';

    /**
     * @var string 登录并授权模式。
     */
    const LOGIN_MODE_AUTHORIZATION = 'login-authorization';

    /**
     * @var string
     */
    public $mode;
    
    /**
     * @var string
     */
    public $username;
    
    /**
     * @var string
     */
    public $password;
    
    /**
     * @var array
     */
    public $scopes;
    
    /**
     * @var DemoUserIdentity|false
     */
    private $_userIdentity = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        $when = function ($model) {
            return $model->mode === self::LOGIN_MODE_AUTHORIZATION;
        };
        
        $whenClient = "function (attribute, value) {
            return $('#" . Html::getInputId($this, 'mode') . "').val() === '" . self::LOGIN_MODE_AUTHORIZATION . "'
        }";
        
        return [
            [['mode', 'username', 'password'], 'required'],
            [['scopes'], 'required', 'when' => $when, 'whenClient' => $whenClient],
            [['username', 'password'], 'string'],
            [['password'], 'validatePassword'],
        ];
    }

    /**
     * 验证用户密码。
     * 
     * @param string $attribute
     * @param array $params
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $userIdentity = $this->getUserIdentity();
            if (!$userIdentity || !$userIdentity->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }
    
    /**
     * 获取用户。
     * 
     * @return DemoUserIdentity
     */
    public function getUserIdentity()
    {
        if ($this->_userIdentity === false) {
            $this->_userIdentity = DemoUserIdentity::findByUsername($this->username);
        }
        
        return $this->_userIdentity;
    }
    
    /**
     * {@inheritdoc}
     */
    public function login(User $user)
    {
        if (!$this->validate()) {
            return false;
        }
        
        if (!$this->loginInternal($user)) {
            return false;
        }
        
        if ($this->mode === self::LOGIN_MODE_AUTHORIZATION) {
            $this->setApproved($user);
        }
        
        return true;
    }
    
    /**
     * 用户登录。
     * 
     * @param User $user
     * @return boolean
     */
    protected function loginInternal(User $user)
    {
        $userIdentity = $this->getUserIdentity();
        if (!$userIdentity) {
            $this->addError('username', 'User not found.');
            return false;
        }
        
        if (!$user->login($userIdentity)) {
            $this->addError('password', 'User login error.');
            return false;
        }
        
        return true;
    }
    
    /**
     * 设置用户同意授权的信息。
     * 
     * @param User $user
     */
    protected function setApproved(User $user)
    {
        /* @var $userIdentity DemoUserIdentity */
        $userIdentity = $user->getIdentity();
        $userIdentity->setOAuthIsApproved(true);
        if ($this->scopes !== null) {
            $userIdentity->setOAuthScopes($this->scopes);
        }
    }
}
