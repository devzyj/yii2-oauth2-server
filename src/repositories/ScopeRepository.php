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
use devzyj\yii2\oauth2\server\interfaces\OAuthClientEntityInterface;
use devzyj\yii2\oauth2\server\interfaces\OAuthUserEntityInterface;
use devzyj\yii2\oauth2\server\entities\ScopeEntity;

/**
 * ScopeRepository class.
 * 
 * 使用类必须实现以下条件：
 * 1. `ClientEntity` 必须实现 [[OAuthClientEntityInterface]] 接口。
 * 2. `UserEntity` 必须实现 [[OAuthUserEntityInterface]] 接口。
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
        if (empty($scopes)) {
            return [];
        }
        
        if ($grantType === 'client_credentials') {
            // 客户端权限授予模式，确认客户端的权限。
            return $this->ensureClientCredentials($scopes, $client);
        } elseif ($user !== null) {
            // 确认用户的权限。
            return $this->ensureUserCredentials($scopes, $client, $user);
        }
        
        return $scopes;
    }
    
    /**
     * 确认客户端的权限。
     * 
     * @param ScopeEntityInterface[] $scopes 请求的权限列表。 
     * @param OAuthClientEntityInterface $client 客户端。
     * @return ScopeEntityInterface[] 有效的权限列表。
     */
    protected function ensureClientCredentials(array $scopes, OAuthClientEntityInterface $client)
    {
        $clientScopes = $client->getScopeEntities();
        if (!is_array($clientScopes)) {
            return $scopes;
        }
        
        return $this->ensureScopes($scopes, $clientScopes);
    }
    
    /**
     * 确认用户的权限。
     * 
     * @param ScopeEntityInterface[] $scopes 请求的权限列表。 
     * @param OAuthClientEntityInterface $client 客户端。
     * @param OAuthUserEntityInterface $user 用户。
     * @return ScopeEntityInterface[] 有效的权限列表。
     */
    protected function ensureUserCredentials(array $scopes, OAuthClientEntityInterface $client, OAuthUserEntityInterface $user)
    {
        $userScopes = $user->getScopeEntities();
        if (!is_array($userScopes)) {
            return $scopes;
        }
        
        return $this->ensureScopes($scopes, $userScopes);
    }

    /**
     * 确认客户端或者用户的有效权限。
     *
     * @param ScopeEntityInterface[] $scopes 请求的权限列表。
     * @param ScopeEntityInterface[] $entityScopes 客户端或者用户的全部权限列表。
     * @return ScopeEntityInterface[] 有效的权限列表。
     */
    protected function ensureScopes(array $scopes, array $entityScopes)
    {
        $indexScopes = [];
        foreach ($scopes as $scope) {
            $indexScopes[$scope->getIdentifier()] = $scope;
        }
        
        $indexEntityScopes = [];
        foreach ($entityScopes as $entityScope) {
            $indexEntityScopes[$entityScope->getIdentifier()] = $entityScope;
        }
        
        // 检查权限是否有效。
        $result = [];
        /* @var $scope ScopeEntityInterface */
        foreach ($indexScopes as $identifier => $scope) {
            if (isset($indexEntityScopes[$identifier])) {
                $result[] = $scope;
            }
        }
        
        // 返回有效的权限。
        return $result;
    }
}