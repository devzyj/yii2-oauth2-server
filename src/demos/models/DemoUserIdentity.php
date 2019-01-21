<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\demos\models;

use Yii;
use yii\web\IdentityInterface;
use devzyj\yii2\oauth2\server\interfaces\OAuthIdentityInterface;

/**
 * DemoUserIdentity class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class DemoUserIdentity extends DemoUserModel implements IdentityInterface, OAuthIdentityInterface
{
    const OAUTH_IS_APPROVED_NAME = '__OAUTH_IS_APPROVED';
    const OAUTH_SCOPES_NAME = '__OAUTH_SCOPES';
    
    /**
     * 设置用户是否同意授权。
     *
     * @param boolean $value
     */
    public function setOAuthIsApproved($value)
    {
        Yii::$app->getSession()->set(self::OAUTH_IS_APPROVED_NAME, $value);
    }
    
    /**
     * 设置同意授权的权限。
     * 
     * @param string[] $value
     */
    public function setOAuthScopes($value)
    {
        Yii::$app->getSession()->set(self::OAUTH_SCOPES_NAME, $value);
    }
    
    /***************************** IdentityInterface *****************************/
    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findById($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {}

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {}

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {}

    /***************************** OAuthIdentityInterface *****************************/
    /**
     * {@inheritdoc}
     */
    public function getOAuthUserEntity()
    {
        /* @var $model DemoUserEntity */
        $model = Yii::createObject(DemoUserEntity::class);
        $model->id = $this->id;
        $model->username = $this->username;
        $model->password = $this->password;
        $model->scopes = $this->scopes;
        $model->defaultScopes = $this->defaultScopes;
        
        return $model;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOAuthIsApproved()
    {
        return Yii::$app->getSession()->get(self::OAUTH_IS_APPROVED_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetOAuthIsApproved()
    {
        Yii::$app->getSession()->remove(self::OAUTH_IS_APPROVED_NAME);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOAuthScopeEntities()
    {
        $scopes = Yii::$app->getSession()->get(self::OAUTH_SCOPES_NAME);
        if ($scopes === null) {
            return null;
        }

        $result = [];
        if ($scopes && is_array($scopes)) {
            // 获取权限，并且过滤与用户无关的权限。
            foreach ($this->getScopes() as $scopeEntity) {
                if (in_array($scopeEntity->getIdentifier(), $scopes)) {
                    $result[] = $scopeEntity;
                }
            }
        }
        
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetOAuthScopeEntities()
    {
        Yii::$app->getSession()->remove(self::OAUTH_SCOPES_NAME);
    }
}
