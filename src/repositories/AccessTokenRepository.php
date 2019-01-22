<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\repositories;

use Yii;
use devzyj\oauth2\server\interfaces\AccessTokenRepositoryInterface;
use devzyj\oauth2\server\interfaces\AccessTokenEntityInterface;
use devzyj\oauth2\server\traits\AccessTokenRepositoryTrait;
use devzyj\yii2\oauth2\server\entities\AccessTokenEntity;

/**
 * AccessTokenRepository class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    use AccessTokenRepositoryTrait {
        serializeAccessTokenEntity as protected parentSerializeAccessTokenEntity;
        unserializeAccessTokenEntity as protected parentUnserializeAccessTokenEntity;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createAccessTokenEntity()
    {
        return Yii::createObject(AccessTokenEntity::class);
    }

    /**
     * {@inheritdoc}
     */
    public function generateAccessTokenUniqueIdentifier()
    {}
    
    /**
     * {@inheritdoc}
     */
    public function saveAccessTokenEntity(AccessTokenEntityInterface $accessTokenEntity)
    {}
    
    /**
     * {@inheritdoc}
     */
    public function revokeAccessTokenEntity($identifier)
    {}

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenEntityRevoked($identifier)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeAccessTokenEntity(AccessTokenEntityInterface $accessTokenEntity, $cryptKey)
    {
        if (isset($cryptKey['privateKey'])) {
            $cryptKey['privateKey'] = Yii::getAlias($cryptKey['privateKey']);
        }
        
        return $this->parentSerializeAccessTokenEntity($accessTokenEntity, $cryptKey);
    }
    
    /**
     * {@inheritdoc}
     */
    public function unserializeAccessTokenEntity($serializedAccessToken, $cryptKey)
    {
        if (isset($cryptKey['publicKey'])) {
            $cryptKey['publicKey'] = Yii::getAlias($cryptKey['publicKey']);
        }
        
        return $this->parentUnserializeAccessTokenEntity($serializedAccessToken, $cryptKey);
    }
}