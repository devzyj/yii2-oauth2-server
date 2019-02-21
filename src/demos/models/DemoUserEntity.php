<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\demos\models;

use devzyj\oauth2\server\interfaces\UserEntityInterface;

/**
 * DemoUserEntity class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class DemoUserEntity extends DemoUserModel implements UserEntityInterface
{
    /******************************** UserEntityInterface ********************************/
    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->id;
    }
}