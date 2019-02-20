<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\models;

use Yii;

/**
 * This is the model class for table "{{%oauth_client_scope}}".
 *
 * @property int $client_id 客户端 ID
 * @property int $scope_id 权限 ID
 * @property int $is_default 是否默认（0=否；1=是）
 * @property int $create_time 创建时间
 *
 * @property OauthScope $oauthScope 权限
 * @property OauthClient $oauthClient 客户端
 *
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class OauthClientScope extends \yii\db\ActiveRecord
{
    /**
     * @var integer 不是默认。
     */
    const IS_DEFAULT_NO = 0;
    
    /**
     * @var integer 是默认。
     */
    const IS_DEFAULT_YES = 1;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%oauth_client_scope}}';
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
            [['client_id', 'scope_id'], 'required'],
            [['client_id', 'scope_id'], 'integer'],
            [['is_default'], 'boolean'],
            [['client_id', 'scope_id'], 'unique', 'targetAttribute' => ['client_id', 'scope_id']],
            [['scope_id'], 'exist', 'skipOnError' => true, 'targetClass' => OauthScope::class, 'targetAttribute' => ['scope_id' => 'id']],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => OauthClient::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'client_id' => 'Client ID',
            'scope_id' => 'Scope ID',
            'is_default' => 'Is Default',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOauthScope()
    {
        return $this->hasOne(OauthScope::class, ['id' => 'scope_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOauthClient()
    {
        return $this->hasOne(OauthClient::class, ['id' => 'client_id']);
    }
}
