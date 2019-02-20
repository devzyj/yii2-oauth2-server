<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\repositories;

use devzyj\oauth2\server\interfaces\ScopeRepositoryInterface;
use devzyj\oauth2\server\interfaces\ScopeEntityInterface;
use devzyj\oauth2\server\interfaces\ClientEntityInterface;
use devzyj\oauth2\server\interfaces\UserEntityInterface;
use devzyj\oauth2\server\base\AbstractAuthorizeGrant;
use devzyj\yii2\oauth2\server\entities\ScopeEntity;
use devzyj\yii2\oauth2\server\entities\ClientEntity;

/**
 * ScopeRepository class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getScopeEntity($identifier)
    {
        return ScopeEntity::findOneByIdentifier($identifier);
    }
    
    /**
     * 根据请求的权限列表、权限授予类型、客户端、用户，确定最终授予的权限列表。
     * 
     * @param ScopeEntityInterface[] $scopes 请求的权限列表。
     * @param string $grantType 权限授予类型。
     * @param ClientEntityInterface $client 客户端。
     * @param UserEntityInterface $user 用户。
     * @return ScopeEntityInterface[] 最终授予的权限列表。
     */
    public function finalizeEntities(array $scopes, $grantType, ClientEntityInterface $client, UserEntityInterface $user = null)
    {
        if ($scopes) {
            if ($grantType === AbstractAuthorizeGrant::GRANT_TYPE_AUTHORIZATION_CODE) {
                // 授权码模式。
                return $this->ensureAuthorizationCode($scopes, $client, $user);
            } elseif ($grantType === AbstractAuthorizeGrant::GRANT_TYPE_IMPLICIT) {
                // 简单模式。
                return $this->ensureImplicit($scopes, $client, $user);
            } elseif ($grantType === AbstractAuthorizeGrant::GRANT_TYPE_PASSWORD) {
                // 用户名密码模式。
                return $this->ensurePassword($scopes, $client, $user);
            } elseif ($grantType === AbstractAuthorizeGrant::GRANT_TYPE_CLIENT_CREDENTIALS) {
                // 客户端模式。
                return $this->ensureClientCredentials($scopes, $client);
            } elseif ($grantType === AbstractAuthorizeGrant::GRANT_TYPE_REFRESH_TOKEN) {
                // 更新令牌。
                return $this->ensureRefreshToken($scopes, $client, $user);
            }
        }
        
        return [];
    }

    /**
     * 确认授权码模式的权限。
     *
     * @param ScopeEntityInterface[] $scopes 请求的权限列表。
     * @param ClientEntity $client 客户端。
     * @param UserEntityInterface $user 用户。
     * @return ScopeEntityInterface[] 有效的权限列表。
     */
    protected function ensureAuthorizationCode(array $scopes, $client, $user)
    {
        $scopeIdentifiers = array_map(function (ScopeEntityInterface $scope) {
            return $scope->getIdentifier();
        }, $scopes);
        
        return $client->getOauthScopes()->andWhere(['identifier' => $scopeIdentifiers])->all();
    }

    /**
     * 确认简单模式的权限。
     *
     * @param ScopeEntityInterface[] $scopes 请求的权限列表。
     * @param ClientEntity $client 客户端。
     * @param UserEntityInterface $user 用户。
     * @return ScopeEntityInterface[] 有效的权限列表。
     */
    protected function ensureImplicit(array $scopes, $client, $user)
    {
        $scopeIdentifiers = array_map(function (ScopeEntityInterface $scope) {
            return $scope->getIdentifier();
        }, $scopes);
        
        return $client->getOauthScopes()->andWhere(['identifier' => $scopeIdentifiers])->all();
    }

    /**
     * 确认用户名密码模式的权限。
     *
     * @param ScopeEntityInterface[] $scopes 请求的权限列表。
     * @param ClientEntity $client 客户端。
     * @param UserEntityInterface $user 用户。
     * @return ScopeEntityInterface[] 有效的权限列表。
     */
    protected function ensurePassword(array $scopes, $client, $user)
    {
        $scopeIdentifiers = array_map(function (ScopeEntityInterface $scope) {
            return $scope->getIdentifier();
        }, $scopes);
        
        return $client->getOauthScopes()->andWhere(['identifier' => $scopeIdentifiers])->all();
    }
    
    /**
     * 确认客户端模式的权限。
     * 
     * @param ScopeEntityInterface[] $scopes 请求的权限列表。 
     * @param ClientEntity $client 客户端。
     * @return ScopeEntityInterface[] 有效的权限列表。
     */
    protected function ensureClientCredentials(array $scopes, $client)
    {
        $scopeIdentifiers = array_map(function (ScopeEntityInterface $scope) {
            return $scope->getIdentifier();
        }, $scopes);
        
        return $client->getOauthScopes()->andWhere(['identifier' => $scopeIdentifiers])->all();
    }
    
    /**
     * 确认更新令牌时的权限。
     *
     * @param ScopeEntityInterface[] $scopes 请求的权限列表。
     * @param ClientEntity $client 客户端。
     * @param UserEntityInterface $user 用户。
     * @return ScopeEntityInterface[] 有效的权限列表。
     */
    protected function ensureRefreshToken(array $scopes, $client, $user)
    {
        $scopeIdentifiers = array_map(function (ScopeEntityInterface $scope) {
            return $scope->getIdentifier();
        }, $scopes);
        
        return $client->getOauthScopes()->andWhere(['identifier' => $scopeIdentifiers])->all();
    }
}