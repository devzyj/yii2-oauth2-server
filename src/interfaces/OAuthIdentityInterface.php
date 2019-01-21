<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\interfaces;

/**
 * OAuthIdentityInterface 需要用户身份证验实例实现的接口。
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
interface OAuthIdentityInterface
{
    /**
     * 获取授权用户实体对像。
     * 
     * @return \devzyj\oauth2\server\interfaces\UserEntityInterface
     */
    public function getOAuthUserEntity();
    
    /**
     * 获取用户是否同意授权。
     * 
     * @return boolean|null 返回 `null` 表示未进行同意或拒绝授权的操作。
     */
    public function getOAuthIsApproved();
    
    /**
     * 释放用户是否同意授权状态。
     */
    public function unsetOAuthIsApproved();
    
    /**
     * 获取同意授权的权限实体列表。
     * 
     * @return \devzyj\oauth2\server\interfaces\ScopeEntityInterface[]|null 返回 `null` 表示请求的全部权限。
     */
    public function getOAuthScopeEntities();

    /**
     * 释放同意授权的权限实体列表。
     */
    public function unsetOAuthScopeEntities();
}