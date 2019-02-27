# yii2-oauth2-server
在 Yii2 中实现 OAuth2 服务器模块。


# Installation

```bash
composer require --prefer-dist "devzyj/yii2-oauth2-server" "~1.0.0"
```

or add

```json
"devzyj/yii2-oauth2-server" : "~1.0.0"
```


# Usage

```bash
php yii migrate --migrationPath=@devzyj/yii2/oauth2/server/migrations
```

```php
// config.php
return [
    'bootstrap' => ['oauth2'],
    'modules' => [
        'oauth2' => [
            'class' => 'devzyj\yii2\oauth2\server\Module',
            'defaultScopes' => ['basic', 'basic2', 'basic3'], // 默认权限。
            'accessTokenCryptKey' => [
                'privateKey' => '@app/path/to/private.key', // 访问令牌的私钥路径。
                'passphrase' => '', // 访问令牌的私钥密码。没有密码可以为 `null`。
                'publicKey' => '@app/path/to/public.key', // 访问令牌的公钥路径。
                //'signKey' => 'test-sign-key', // 字符串签名密钥。
            ],
            'authorizationCodeCryptKey' => [
                'ascii' => 'def0000086937b.....', // 使用 `vendor/bin/generate-defuse-key` 生成的字符串。
                //'path' => '/path/to/ascii.txt', // 保存了 `vendor/bin/generate-defuse-key` 生成的字符串的文件路径。
                //'password' => 'test-password', // 字符串密钥。
            ],
            'refreshTokenCryptKey' => [
                'ascii' => 'def000008......', // 使用 `vendor/bin/generate-defuse-key` 生成的字符串。
                //'path' => '/path/to/ascii.txt', // 保存了 `vendor/bin/generate-defuse-key` 生成的字符串的文件路径。
                //'password' => 'test-password', // 字符串密钥。
            ],
            'validateAccessTokenQueryParam' => 'access-token', // 验证访问令牌时，在查询参数中的名称。
            'userRepositoryClass' => 'app\models\UserRepository',
            'user' => [
                'class' => 'yii\web\User',
                'identityClass' => 'app\models\UserIdentity',
            ],
            'loginUrl' => ['login'],
            'authorizationUrl' => ['authorization'],
            'classMap' => [ // 依赖注入。
                'devzyj\oauth2\server\authorizes\CodeAuthorize' => [
                    'class' => 'devzyj\oauth2\server\authorizes\CodeAuthorize',
                    'enableCodeChallenge' => true, // 使用 `code_challenge`，默认为 `false`。
                ],
                'devzyj\oauth2\server\grants\AuthorizationCodeGrant' => [
                    'class' => 'devzyj\oauth2\server\grants\AuthorizationCodeGrant',
                    'enableCodeChallenge' => true, // 使用 `code_challenge`，默认为 `false`。
                ],
                ....
                ....
            ],
        ],
    ],
];

// 使用模块方法，验证访问令牌。
$module = Yii::$app->getModule('oauth2');
$tokenInfo = $module->validateAccessToken($accessToken);
```

```php
// 演示版配置项。
return [
    'modules' => [
        'oauth2' => [
            'userRepositoryClass' => 'devzyj\yii2\oauth2\server\demos\models\DemoUserRepository',
            'user' => [
                'class' => 'yii\web\User',
                'identityClass' => 'devzyj\yii2\oauth2\server\demos\models\DemoUserIdentity',
            ],
            'controllerMap' => [
                'demo' => 'devzyj\yii2\oauth2\server\demos\controllers\DemoController',
            ],
            'loginUrl' => ['/oauth2/demo/login'],
            'authorizationUrl' => ['/oauth2/demo/authorization'],
        ],
    ],
]
```