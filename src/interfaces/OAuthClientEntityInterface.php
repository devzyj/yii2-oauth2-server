<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\interfaces;

/**
 * 客户端实体接口。
 *
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
interface OAuthClientEntityInterface
{
    /**
     * 获取客户端的全部权限。
     *
     * @return \devzyj\oauth2\server\interfaces\ScopeEntityInterface[]
     */
    public function getScopeEntities();
}