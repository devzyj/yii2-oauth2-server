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
        $this->createTables();
        $this->insertRows();
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
    
    /**
     * 创建数据表。
     */
    protected function createTables()
    {
        $tables = $this->tables;
        
        // oauth_client
        $this->createTable($tables['oauth_client'], [
            'id' => $this->primaryKey(10)->unsigned()->comment('ID'),
            'name' => $this->string(50)->notNull()->unique()->comment('名称'),
            'description' => $this->string(255)->notNull()->defaultValue('')->comment('描述'),
            'identifier' => $this->string(20)->notNull()->unique()->comment('标识'),
            'secret' => $this->string(32)->notNull()->unique()->comment('密钥'),
            'grant_types' => $this->string(100)->notNull()->defaultValue('')->comment('授权类型'),
            'redirect_uri' => $this->string(255)->notNull()->defaultValue('')->comment('回调地址'),
            'access_token_duration' => $this->integer(10)->unsigned()->notNull()->defaultValue(0)->comment('访问令牌的持续时间'),
            'refresh_token_duration' => $this->integer(10)->unsigned()->notNull()->defaultValue(0)->comment('更新令牌的持续时间'),
            'create_time' => $this->integer(10)->unsigned()->notNull()->comment('创建时间'),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('状态（0=禁用；1=可用）'),
        ], "COMMENT='OAuth - 客户端表'");
        
        // oauth_scope
        $this->createTable($tables['oauth_scope'], [
            'id' => $this->primaryKey(10)->unsigned()->comment('ID'),
            'identifier' => $this->string(255)->notNull()->unique()->comment('标识'),
            'name' => $this->string(50)->notNull()->comment('名称'),
            'description' => $this->string(255)->notNull()->defaultValue('')->comment('描述'),
            'create_time' => $this->integer(10)->unsigned()->notNull()->comment('创建时间'),
        ], "COMMENT='OAuth - 权限表'");

        // oauth_client_scope
        $this->createTable($tables['oauth_client_scope'], [
            'client_id' => $this->integer(10)->unsigned()->notNull()->comment('客户端 ID'),
            'scope_id' => $this->integer(10)->unsigned()->notNull()->comment('权限 ID'),
            'is_default' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('是否默认（0=否；1=是）'),
            'create_time' => $this->integer(10)->unsigned()->notNull()->comment('创建时间'),
        ], "COMMENT='OAuth - 客户端与权限关联表'");
        $this->addPrimaryKey('PK_client_id_scope_id', $tables['oauth_client_scope'], ['client_id', 'scope_id']);
        
        $foreignKeyName = $this->getForeignKeyName($tables['oauth_client_scope'], 'client_id');
        $this->addForeignKey($foreignKeyName, $tables['oauth_client_scope'], 'client_id', $tables['oauth_client'], 'id', 'CASCADE', 'CASCADE');
        
        $foreignKeyName = $this->getForeignKeyName($tables['oauth_client_scope'], 'scope_id');
        $this->addForeignKey($foreignKeyName, $tables['oauth_client_scope'], 'scope_id', $tables['oauth_scope'], 'id', 'CASCADE', 'CASCADE');
        
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
     * 插入数据。
     */
    protected function insertRows()
    {
        // oauth_client
        $this->insert($this->tables['oauth_client'], [
            'name' => '测试客户端',
            'description' => '用于测试授权的客户端',
            'identifier' => substr(md5(microtime().rand(1000, 9999)), 8, 16),
            'secret' => md5(microtime().rand(1000, 9999)),
            'grant_types' => 'authorization_code implicit password client_credentials refresh_token',
            'redirect_uri' => '',
            'access_token_duration' => 3600, // 1 hours
            'refresh_token_duration' => 2592000, // 30 days
            'create_time' => time(),
            'status' => 1,
        ]);
        $clientId = $this->db->getLastInsertID();

        // oauth_scope
        $this->insert($this->tables['oauth_scope'], [
            'identifier' => 'any',
            'name' => '全部权限',
            'description' => '用户或者客户端的全部权限',
            'create_time' => time(),
        ]);
        $scopeId = $this->db->getLastInsertID();

        // oauth_client_scope
        $this->insert($this->tables['oauth_client_scope'], [
            'client_id' => $clientId,
            'scope_id' => $scopeId,
            'is_default' => 1,
            'create_time' => time(),
        ]);
        $scopeId = $this->db->getLastInsertID();

        // oauth_scope
        $this->insert($this->tables['oauth_scope'], [
            'identifier' => 'basic',
            'name' => '基础权限',
            'description' => '用户或者客户端的基础权限',
            'create_time' => time(),
        ]);
        $scopeId = $this->db->getLastInsertID();
        
        // oauth_client_scope
        $this->insert($this->tables['oauth_client_scope'], [
            'client_id' => $clientId,
            'scope_id' => $scopeId,
            'is_default' => 0,
            'create_time' => time(),
        ]);
        $scopeId = $this->db->getLastInsertID();
        
    }
}
