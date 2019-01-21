<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\entities;

use devzyj\oauth2\server\interfaces\ClientEntityInterface;
use devzyj\yii2\oauth2\server\interfaces\OAuthClientEntityInterface;
use devzyj\yii2\oauth2\server\models\OauthClient;
use devzyj\yii2\oauth2\server\models\OauthClientScope;

/**
 * ClientEntity class.
 * 
 * @property ScopeEntity[] $oauthScopes 客户端的权限
 * @property ScopeEntity[] $defaultOauthScopes 客户端的默认权限
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class ClientEntity extends OauthClient implements ClientEntityInterface, OAuthClientEntityInterface
{
    /**
     * 获取客户端的权限。
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getOauthScopes()
    {
        return $this->hasMany(ScopeEntity::class, ['id' => 'scope_id'])->viaTable(OauthClientScope::tableName(), ['client_id' => 'id']);
    }

    /**
     * 获取客户端的默认权限。
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultOauthScopes()
    {
        return $this->hasMany(ScopeEntity::class, ['id' => 'scope_id'])->viaTable(OauthClientScope::tableName(), ['client_id' => 'id'], function ($query) {
            $query->andWhere(['is_default'=>OauthClientScope::IS_DEFAULT_YES]);
        });
    }

    /******************************** ClientEntityInterface ********************************/
    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUri()
    {
        return parent::getRedirectUri();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getGrantTypes()
    {
        return parent::getGrantTypes();
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenDuration()
    {
        return $this->access_token_duration;
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshTokenDuration()
    {
        return $this->refresh_token_duration;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultScopeEntities()
    {
        return $this->defaultOauthScopes;
    }

    /******************************** OAuthClientEntityInterface ********************************/
    /**
     * {@inheritdoc}
     */
    public function getScopeEntities()
    {
        return $this->oauthScopes;
    }
}