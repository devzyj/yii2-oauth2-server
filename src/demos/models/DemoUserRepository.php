<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\demos\models;

use devzyj\oauth2\server\interfaces\UserRepositoryInterface;

/**
 * DemoUserRepository class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class DemoUserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserEntity($identifier)
    {
        return DemoUserEntity::findById($identifier);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUserEntityByCredentials($username, $password)
    {
        /* @var $model DemoUserEntity */
        $model = DemoUserEntity::findByUsername($username);
        if ($model && $model->validatePassword($password)) {
            return $model;
        }
    }
}