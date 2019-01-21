<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server;

use Yii;
use devzyj\oauth2\server\authorizes\CodeAuthorize;
use devzyj\oauth2\server\authorizes\ImplicitAuthorize;
use devzyj\oauth2\server\grants\AuthorizationCodeGrant;
use devzyj\oauth2\server\grants\ClientCredentialsGrant;
use devzyj\oauth2\server\grants\PasswordGrant;
use devzyj\oauth2\server\grants\RefreshTokenGrant;

/**
 * OAuth2 Server Module.
 * 
 * ```php
 * return [
 *     'bootstrap' => ['oauth2'],
 *     'modules' => [
 *         'oauth2' => [
 *             'class' => 'devzyj\yii2\oauth2\server\Module',
 *             'defaultScopes' => ['basic', 'basic2', 'basic3'], // 默认权限。
 *             'accessTokenCryptKey' => [
 *                 'privateKey' => '@app/path/to/private.key', // 访问令牌的私钥路径。
 *                 'passphrase' => '', // 访问令牌的私钥密码。没有密码可以为 `null`。
 *                 'publicKey' => '@app/path/to/public.key', // 访问令牌的公钥路径。
 *                 //'signKey' => 'test-sign-key', // 字符串签名密钥。
 *             ],
 *             'authorizationCodeCryptKey' => [
 *                 'ascii' => 'def0000086937b.....', // 使用 `vendor/bin/generate-defuse-key` 生成的字符串。
 *                 //'path' => '/path/to/ascii.txt', // 保存了 `vendor/bin/generate-defuse-key` 生成的字符串的文件路径。
 *                 //'password' => 'test-password', // 字符串密钥。
 *             ],
 *             'refreshTokenCryptKey' => [
 *                 'ascii' => 'def000008......', // 使用 `vendor/bin/generate-defuse-key` 生成的字符串。
 *                 //'path' => '/path/to/ascii.txt', // 保存了 `vendor/bin/generate-defuse-key` 生成的字符串的文件路径。
 *                 //'password' => 'test-password', // 字符串密钥。
 *             ],
 *             'validateAccessTokenQueryParam' => 'access-token', // 验证访问令牌时，在查询参数中的名称。
 *             'classMap' => [
 *                 'devzyj\yii2\oauth2\server\entities\ClientEntity' => 'app\models\ClientEntity',
 *                 ....
 *             ],
 *             'userRepositoryClass' => 'devzyj\yii2\oauth2\server\demos\models\DemoUserRepository',
 *             'user' => [
 *                 'class' => 'yii\web\User',
 *                 'identityClass' => 'devzyj\yii2\oauth2\server\demos\models\DemoUserIdentity',
 *             ],
 *             'controllerMap' => [
 *                 'demo' => 'devzyj\yii2\oauth2\server\demos\controllers\DemoController',
 *             ],
 *             'loginUrl' => ['/oauth2/demo/login'],
 *             'authorizationUrl' => ['/oauth2/demo/authorization'],
 *         ],
 *     ],
 * ]
 * ```
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{
    /**
     * @var array 类映射。
     */
    public $classMap = [];
    
    /**
     * @var array 授权类型类名。
     */
    public $authorizeTypeClasses = [
        CodeAuthorize::class,
        ImplicitAuthorize::class,
    ];
    
    /**
     * @var array 权限授予类型类名。
     */
    public $grantTypeClasses = [
        AuthorizationCodeGrant::class,
        ClientCredentialsGrant::class,
        PasswordGrant::class,
        RefreshTokenGrant::class,
    ];

    /**
     * @var string|array|callable 用户存储库。
     */
    public $userRepositoryClass;

    /**
     * @var array 默认权限。
     */
    public $defaultScopes = [];
    
    /**
     * @var integer 访问令牌的持续时间，默认一小时。
     */
    public $accessTokenDuration = 3600;
    
    /**
     * @var string|array 访问令牌密钥。
     */
    public $accessTokenCryptKey;

    /**
     * @var integer 授权码的持续时间，默认十分钟。
     */
    public $authorizationCodeDuration = 600;
    
    /**
     * @var array 授权码密钥。
     */
    public $authorizationCodeCryptKey;
    
    /**
     * @var integer 更新令牌的持续时间，默认三十天。
     */
    public $refreshTokenDuration = 2592000;
    
    /**
     * @var array 更新令牌密钥。
     */
    public $refreshTokenCryptKey;

    /**
     * @var string|array 授权用户的应用组件ID或配置。如果没有设置，则使用 `Yii::$app->getUser()`。
     */
    public $user;
    
    /**
     * @var string|array 登录地址。
     */
    public $loginUrl;

    /**
     * @var string|array 确认授权地址。
     */
    public $authorizationUrl;
    
    /**
     * @var string 验证访问令牌时，在查询参数中的名称。
     */
    public $validateAccessTokenQueryParam;
    
    /**
     * @var callable 在验证访问令牌时，根据访问令牌实例，构造返回结果。
     * 方法应该返回一个包函访问令牌内容的数组。
     * 
     * ```php
     * function (AccessTokenEntityInterface $accessToken) {
     *     return [
     *         'access_token_id' => $accessToken->getIdentifier(),
     *         'client_id' => $accessToken->getClientIdentifier(),
     *         'user_id' => $accessToken->getUserIdentifier(),
     *         'scopes' => $accessToken->getScopeIdentifiers(),
     *     ];
     * }
     * ```
     * 
     * @see ResourceController::validateAccessTokenResult()
     */
    public $validateAccessTokenResult;

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules([
                "<module:({$this->uniqueId})>/authorize" => "<module>/authorize/index",
                "<module:({$this->uniqueId})>/token" => "<module>/token/index",
                "<module:({$this->uniqueId})>/resource" => "<module>/resource/index",
            ], false);
        }
        
        // set definitions
        foreach ($this->classMap as $class => $definition) {
            Yii::$container->set($class, $definition);
        }
    }
}