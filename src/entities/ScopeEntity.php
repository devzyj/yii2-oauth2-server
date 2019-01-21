<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\entities;

use devzyj\oauth2\server\interfaces\ScopeEntityInterface;
use devzyj\yii2\oauth2\server\models\OauthScope;

/**
 * ScopeEntity class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class ScopeEntity extends OauthScope implements ScopeEntityInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}