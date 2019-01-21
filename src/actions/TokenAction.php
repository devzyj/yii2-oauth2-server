<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\actions;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use devzyj\oauth2\server\AuthorizationServer;
use devzyj\oauth2\server\interfaces\ServerRequestInterface;
use devzyj\oauth2\server\exceptions\OAuthServerException;
use devzyj\yii2\oauth2\server\ServerRequest;
use devzyj\yii2\oauth2\server\repositories\AccessTokenRepository;
use devzyj\yii2\oauth2\server\repositories\AuthorizationCodeRepository;
use devzyj\yii2\oauth2\server\repositories\ClientRepository;
use devzyj\yii2\oauth2\server\repositories\RefreshTokenRepository;
use devzyj\yii2\oauth2\server\repositories\ScopeRepository;

/**
 * TokenAction class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class TokenAction extends \yii\base\Action
{
    /**
     * @var array 权限授予类型类名。
     */
    public $grantTypeClasses;
    
    /**
     * @var string|array|callable 用户存储库。
     */
    public $userRepositoryClass;

    /**
     * @var array 默认权限。
     */
    public $defaultScopes;
    
    /**
     * @var integer 访问令牌的持续时间。
     */
    public $accessTokenDuration;
    
    /**
     * @var string|array 访问令牌密钥。
     */
    public $accessTokenCryptKey;
    
    /**
     * @var array 授权码密钥。
     */
    public $authorizationCodeCryptKey;
    
    /**
     * @var integer 更新令牌的持续时间。
     */
    public $refreshTokenDuration;
    
    /**
     * @var array 更新令牌密钥。
     */
    public $refreshTokenCryptKey;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    
        if ($this->grantTypeClasses === null) {
            throw new InvalidConfigException('The `grantTypeClasses` property must be set.');
        }
    }
    
    /**
     * @return array
     */
    public function run()
    {
        // 授权服务器实例。
        $authorizationServer = $this->getAuthorizationServer();

        // 服务器请求实例。
        $serverRequest = $this->getServerRequest();
        
        try {
            // 运行并获取授予的认证信息。
            return $authorizationServer->runGrantTypes($serverRequest);
        } catch (OAuthServerException $e) {
            throw new HttpException($e->getHttpStatusCode(), $e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * 获取授权服务器实例。
     * 
     * @return AuthorizationServer
     */
    protected function getAuthorizationServer()
    {
        // 实例化对像。
        $authorizationServer = Yii::createObject([
            'class' => AuthorizationServer::class,
            'accessTokenRepository' => Yii::createObject(AccessTokenRepository::class),
            'authorizationCodeRepository' => Yii::createObject(AuthorizationCodeRepository::class),
            'clientRepository' => Yii::createObject(ClientRepository::class),
            'refreshTokenRepository' => Yii::createObject(RefreshTokenRepository::class),
            'scopeRepository' => Yii::createObject(ScopeRepository::class),
            'userRepository' => Yii::createObject($this->userRepositoryClass),
            'defaultScopes' => $this->defaultScopes,
            'accessTokenDuration' => $this->accessTokenDuration,
            'accessTokenCryptKey' => $this->accessTokenCryptKey,
            'authorizationCodeCryptKey' => $this->authorizationCodeCryptKey,
            'refreshTokenDuration' => $this->refreshTokenDuration,
            'refreshTokenCryptKey' => $this->refreshTokenCryptKey,
        ]);

        // 添加授予类型。
        foreach ($this->grantTypeClasses as $grantTypeClass) {
            $authorizationServer->addGrantType(Yii::createObject($grantTypeClass));
        }
        
        // 返回对像。
        return $authorizationServer;
    }
    
    /**
     * 获取服务器请求实例。
     * 
     * @return ServerRequestInterface
     */
    protected function getServerRequest()
    {
        $serverRequest = Yii::createObject(ServerRequest::class);
        $serverRequest->parsers = ArrayHelper::merge([
            'application/json' => 'yii\web\JsonParser',
        ], $serverRequest->parsers);
        
        return $serverRequest;
    }
}