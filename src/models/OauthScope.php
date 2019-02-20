<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\models;

use Yii;

/**
 * This is the model class for table "{{%oauth_scope}}".
 *
 * @property int $id ID
 * @property string $identifier 标识
 * @property string $name 名称
 * @property string $description 描述
 * @property int $create_time 创建时间
 *
 * @property OauthClientScope[] $oauthClientScopes 客户端与权限的关联关系
 * @property OauthClient[] $oauthClients 客户端
 *
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class OauthScope extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%oauth_scope}}';
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
            [['identifier', 'name'], 'required'],
            [['identifier', 'description'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 50],
            [['identifier'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'identifier' => 'Identifier',
            'name' => 'Name',
            'description' => 'Description',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOauthClientScopes()
    {
        return $this->hasMany(OauthClientScope::class, ['scope_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOauthClients()
    {
        return $this->hasMany(OauthClient::class, ['id' => 'client_id'])->viaTable(OauthClientScope::tableName(), ['scope_id' => 'id']);
    }
    
    /**
     * 通过权限标识，查询并返回一个权限模型。
     * 
     * @param string $identifier 权限标识。
     * @return static|null 权限模型实例，如果没有匹配到，则为 `null`。
     */
    public static function findOneByIdentifier($identifier)
    {
        return static::findOne(['identifier' => $identifier]);
    }
}
