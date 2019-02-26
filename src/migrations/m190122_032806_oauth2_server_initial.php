<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

use yii\db\Migration;

/**
 * Class m190122_032806_oauth2_server_initial
 * 
 * php yii migrate --migrationPath=@devzyj/yii2/oauth2/server/migrations
 */
class m190122_032806_oauth2_server_initial extends Migration
{
    /**
     * @var array 全部数据表名。
     */
    protected $tables = [
        'oauth_client' => '{{%oauth_client}}',
        'oauth_scope' => '{{%oauth_scope}}',
        'oauth_client_scope' => '{{%oauth_client_scope}}',
    ];
    
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create table: oauth_client
        $this->createTable($this->tables['oauth_client'], [
            'id' => $this->primaryKey(10)->unsigned()->comment('ID'),
            'name' => $this->string(50)->notNull()->unique()->comment('名称'),
            'description' => $this->string(255)->notNull()->defaultValue('')->comment('描述'),
            'identifier' => $this->string(20)->notNull()->unique()->comment('标识'),
            'secret' => $this->string(32)->notNull()->unique()->comment('密钥'),
            'grant_types' => $this->string(100)->notNull()->defaultValue('')->comment('授权类型（多个使用空隔符分隔：authorization_code implicit password client_credentials refresh_token）'),
            'redirect_uri' => $this->string(255)->notNull()->defaultValue('')->comment('回调地址（多个使用空隔符分隔）'),
            'access_token_duration' => $this->integer(10)->unsigned()->notNull()->defaultValue(0)->comment('访问令牌的持续时间'),
            'refresh_token_duration' => $this->integer(10)->unsigned()->notNull()->defaultValue(0)->comment('更新令牌的持续时间'),
            'create_time' => $this->integer(10)->unsigned()->notNull()->comment('创建时间'),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('状态（0=禁用；1=可用）'),
        ], "COMMENT='OAuth - 客户端表'");
        
        // create table: oauth_scope
        $this->createTable($this->tables['oauth_scope'], [
            'id' => $this->primaryKey(10)->unsigned()->comment('ID'),
            'identifier' => $this->string(255)->notNull()->unique()->comment('标识'),
            'name' => $this->string(50)->notNull()->comment('名称'),
            'description' => $this->string(255)->notNull()->defaultValue('')->comment('描述'),
            'create_time' => $this->integer(10)->unsigned()->notNull()->comment('创建时间'),
        ], "COMMENT='OAuth - 权限表'");

        // create table: oauth_client_scope
        $this->createTable($this->tables['oauth_client_scope'], [
            'client_id' => $this->integer(10)->unsigned()->notNull()->comment('客户端 ID'),
            'scope_id' => $this->integer(10)->unsigned()->notNull()->comment('权限 ID'),
            'is_default' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('是否默认（0=否；1=是）'),
            'create_time' => $this->integer(10)->unsigned()->notNull()->comment('创建时间'),
        ], "COMMENT='OAuth - 客户端与权限关联表'");
        $this->addPrimaryKey('PK_client_id_scope_id', $this->tables['oauth_client_scope'], ['client_id', 'scope_id']);
        $foreignKeyName = $this->getForeignKeyName($this->tables['oauth_client_scope'], 'client_id');
        $this->addForeignKey($foreignKeyName, $this->tables['oauth_client_scope'], 'client_id', $this->tables['oauth_client'], 'id', 'CASCADE', 'CASCADE');
        $foreignKeyName = $this->getForeignKeyName($this->tables['oauth_client_scope'], 'scope_id');
        $this->addForeignKey($foreignKeyName, $this->tables['oauth_client_scope'], 'scope_id', $this->tables['oauth_scope'], 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * 获取外键名称。
     */
    protected function getForeignKeyName($table, $column)
    {
        $schema = $this->db->getSchema();
        return 'FK_' . $schema->getRawTableName($table) . '_' . $column;
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tables['oauth_client_scope']);
        $this->dropTable($this->tables['oauth_scope']);
        $this->dropTable($this->tables['oauth_client']);
    }
}
