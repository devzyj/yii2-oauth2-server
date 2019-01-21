<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\actions;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\User;
use yii\web\HttpException;
use devzyj\oauth2\server\AuthorizationServer;
use devzyj\oauth2\server\interfaces\ServerRequestInterface;
use devzyj\oauth2\server\authorizes\AuthorizeRequestInterface;
use devzyj\oauth2\server\interfaces\ScopeEntityInterface;
use devzyj\oauth2\server\exceptions\OAuthServerException;
use devzyj\yii2\oauth2\server\ServerRequest;
use devzyj\yii2\oauth2\server\repositories\AccessTokenRepository;
use devzyj\yii2\oauth2\server\repositories\AuthorizationCodeRepository;
use devzyj\yii2\oauth2\server\repositories\ClientRepository;
use devzyj\yii2\oauth2\server\repositories\ScopeRepository;
use devzyj\yii2\oauth2\server\interfaces\OAuthIdentityInterface;

/**
 * AuthorizeAction class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class AuthorizeAction extends \yii\base\Action
{
    /**
     * @var array 授权类型类名。
     */
    public $authorizeTypeClasses;

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
     * @var integer 授权码的持续时间。
     */
    public $authorizationCodeDuration;
    
    /**
     * @var array 授权码密钥。
     */
    public $authorizationCodeCryptKey;
    
    /**
     * @var string|array 授权用户的应用组件ID或配置。如果没有设置，则使用 `Yii::$app->getUser()`。
     */
    public $user;
    
    /**
     * @var string|array 登录地址。
     */
    public $loginUrl;
    
    /**
     * @var string|array 授权地址。
     */
    public $authorizationUrl;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    
        if ($this->authorizeTypeClasses === null) {
            throw new InvalidConfigException('The `authorizeTypeClasses` property must be set.');
        } elseif ($this->userRepositoryClass === null) {
            throw new InvalidConfigException('The `userRepositoryClass` property must be set.');
        } elseif ($this->loginUrl === null) {
            throw new InvalidConfigException('The `loginUrl` property must be set.');
        } elseif ($this->authorizationUrl === null) {
            throw new InvalidConfigException('The `authorizationUrl` property must be set.');
        }
    }
    
    /**
     * 用户请求授权。
     */
    public function run()
    {
        // 授权服务器实例。
        $authorizationServer = $this->getAuthorizationServer();

        // 服务器请求实例。
        $serverRequest = $this->getServerRequest();
        
        try {
            // 获取并验证授权请求。
            $authorizeRequest = $authorizationServer->getAuthorizeRequest($serverRequest);

            // 获取授权用户。
            $user = $this->getUser();
            
            // 判断用户是否登录。
            if ($user->getIsGuest()) {
                // 用户未登录，重定向到登录页面。
                return $this->controller->redirect($this->makeLoginUrl($authorizeRequest));
            }
            
            // 已登录的授权用户。
            $userIdentity = $user->getIdentity();
            if (!$userIdentity instanceof OAuthIdentityInterface) {
                throw new InvalidConfigException('The `User::identity` does not implement OAuthIdentityInterface.');
            }

            // 判断用户是否已确认授权。
            $isApproved = $userIdentity->getOAuthIsApproved();
            if ($isApproved === null) {
                // 用户未确认是否授权，重定向到授权页面。
                return $this->controller->redirect($this->makeAuthorizationUrl($authorizeRequest));
            }

            // 释放用户是否同意授权状态。保证每次都需要用户确认。
            $userIdentity->unsetOAuthIsApproved();
            
            // 设置运行授权时的参数。
            $authorizeRequest->setUserEntity($userIdentity->getOAuthUserEntity());
            $authorizeRequest->setIsApproved($isApproved);
            $scopeEntities = $userIdentity->getOAuthScopeEntities();
            if ($scopeEntities !== null) {
                $authorizeRequest->setScopeEntities($scopeEntities);
                $userIdentity->unsetOAuthScopeEntities();
            }
            
            // 运行并返回授权成功的回调地址。
            $redirectUri = $authorizationServer->runAuthorizeTypes($authorizeRequest);

            // 重定向到授权成功的回调地址。
            return $this->controller->redirect($redirectUri);
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
            'scopeRepository' => Yii::createObject(ScopeRepository::class),
            'userRepository' => Yii::createObject($this->userRepositoryClass),
            'defaultScopes' => $this->defaultScopes,
            'accessTokenDuration' => $this->accessTokenDuration,
            'accessTokenCryptKey' => $this->accessTokenCryptKey,
            'authorizationCodeDuration' => $this->authorizationCodeDuration,
            'authorizationCodeCryptKey' => $this->authorizationCodeCryptKey,
        ]);
        
        // 添加授权类型。
        foreach ($this->authorizeTypeClasses as $authorizeTypeClass) {
            $authorizationServer->addAuthorizeType(Yii::createObject($authorizeTypeClass));
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
        return Yii::createObject(ServerRequest::class);
    }
    
    /**
     * 获取授权用户。
     * 
     * @return User
     */
    protected function getUser()
    {
        if ($this->user === null) {
            return Yii::$app->getUser();
        } elseif (is_string($this->user)) {
            return Yii::$app->get($this->user);
        }
        
        return Yii::createObject($this->user);
    }
    
    /**
     * 构造用户登录地址。
     * 
     * @param AuthorizeRequestInterface $authorizeRequest
     * @return string
     */
    protected function makeLoginUrl(AuthorizeRequestInterface $authorizeRequest)
    {
        return $this->makeUrl($this->loginUrl, $authorizeRequest);
    }
    
    /**
     * 构造用户确认授权地址。
     * 
     * @param AuthorizeRequestInterface $authorizeRequest
     * @return string
     */
    protected function makeAuthorizationUrl(AuthorizeRequestInterface $authorizeRequest)
    {
        return $this->makeUrl($this->authorizationUrl, $authorizeRequest);
    }

    /**
     * 构造 URL。
     *
     * @param string|array $url
     * @param AuthorizeRequestInterface $authorizeRequest
     * @return string
     */
    protected function makeUrl($url, AuthorizeRequestInterface $authorizeRequest)
    {
        $params['client_id'] = $authorizeRequest->getClientEntity()->getIdentifier();
    
        $scopeEntities = $authorizeRequest->getScopeEntities();
        if ($scopeEntities) {
            $params['scope'] = implode(' ', array_map(function (ScopeEntityInterface $scopeEntity) {
                return $scopeEntity->getIdentifier();
            }, $scopeEntities));
        }
        
        $params['return_url'] = Yii::$app->getRequest()->getAbsoluteUrl();

        $url = Url::to($url);
        if (strpos($url, '?') === false) {
            return $url . '?' . http_build_query($params);
        } else {
            return $url . '&' . http_build_query($params);
        }
    }
}