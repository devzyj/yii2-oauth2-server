<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\models;

use Yii;

/**
 * This is the model class for table "{{%oauth_client}}".
 *
 * @property int $id ID
 * @property string $name 名称
 * @property string $description 描述
 * @property string $identifier 标识
 * @property string $secret 密钥
 * @property string $grant_types 授权类型（多个使用空隔符分隔）
 * @property string $redirect_uri 回调地址（多个使用空隔符分隔）
 * @property int $access_token_duration 访问令牌的持续时间
 * @property int $refresh_token_duration 更新令牌的持续时间
 * @property int $create_time 创建时间
 * @property int $status 状态（0=禁用；1=可用）
 *
 * @property OauthClientScope[] $oauthClientScopes 客户端与权限的关联关系
 * @property OauthScope[] $oauthScopes 客户端权限
 * @property OauthScope[] $defaultOauthScopes 客户端默认权限
 * 
 * @property boolean $isValid 客户端是否有效
 * @property string[] $grantTypes 客户端的授权类型
 * @property string[] $redirectUri 客户端的回调地址
 *
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class OauthClient extends \yii\db\ActiveRecord
{
    /**
     * @var integer 状态 - 禁用的。
     */
    const STATUS_DISABLED = 0;

    /**
     * @var integer 状态 - 启用的。
     */
    const STATUS_ENABLED = 1;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%oauth_client}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function getDb()
    {
        $module = Yii::$app->controller->module;
        if ($module instanceof \devzyj\yii2\oauth2\server\Module) {
            return $module->getDb();
        }
        
        return parent::getDb();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestampBehavior' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'createdAtAttribute' => 'create_time',
                'updatedAtAttribute' => null,
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'identifier', 'secret'], 'required'],
            [['access_token_duration', 'refresh_token_duration', 'status'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['description', 'redirect_uri'], 'string', 'max' => 255],
            [['identifier'], 'string', 'max' => 20],
            [['secret'], 'string', 'max' => 32],
            [['grant_types'], 'string', 'max' => 100],
            [['name'], 'unique'],
            [['identifier'], 'unique'],
            [['secret'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'identifier' => 'Identifier',
            'secret' => 'Secret',
            'grant_types' => 'Grant Types',
            'redirect_uri' => 'Redirect Uri',
            'access_token_duration' => 'Access Token Duration',
            'refresh_token_duration' => 'Refresh Token Duration',
            'create_time' => 'Create Time',
            'status' => 'Status',
        ];
    }

    /**
     * 获取客户端与权限关联查询对像。
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getOauthClientScopes()
    {
        return $this->hasMany(OauthClientScope::class, ['client_id' => 'id']);
    }

    /**
     * 获取客户端权限查询对像。
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getOauthScopes()
    {
        return $this->hasMany(OauthScope::class, ['id' => 'scope_id'])->viaTable(OauthClientScope::tableName(), ['client_id' => 'id']);
    }

    /**
     * 获取客户端默认权限查询对像。
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultOauthScopes()
    {
        return $this->hasMany(OauthScope::class, ['id' => 'scope_id'])->viaTable(OauthClientScope::tableName(), ['client_id' => 'id'], function ($query) {
            $query->andWhere(['is_default' => OauthClientScope::IS_DEFAULT_YES]);
        });
    }
    
    /**
     * 通过客户端标识，查询并返回一个客户端模型。
     * 
     * @param string $identifier 客户端标识。
     * @return static|null 客户端模型实例，如果没有匹配到，则为 `null`。
     */
    public static function findOneByIdentifier($identifier)
    {
        return static::findOne(['identifier' => $identifier]);
    }

    /**
     * 获取客户端是否有效。
     *
     * @return boolean
     */
    public function getIsValid()
    {
        return $this->status === self::STATUS_ENABLED;
    }
    
    /**
     * 获取客户端的授权类型。
     *
     * @return string[]
     */
    public function getGrantTypes()
    {
        $grantTypes = trim($this->grant_types);
        if ($grantTypes) {
            return explode(' ', $grantTypes);
        }
    
        return [];
    }

    /**
     * 获取客户端的回调地址。
     *
     * @return string[]
     */
    public function getRedirectUri()
    {
        $redirectUri = trim($this->redirect_uri);
        if ($redirectUri) {
            return explode(' ', $redirectUri);
        }
    
        return [];
    }
}
