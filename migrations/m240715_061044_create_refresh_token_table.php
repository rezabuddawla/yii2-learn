<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%refresh_token}}`.
 */
class m240715_061044_create_refresh_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%refresh_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token' => $this->string(64)->notNull()->unique(),
            'expires_at' => $this->timestamp()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex(
            'idx-refresh_token-user_id',
            '{{%refresh_token}}',
            'user_id'
        );

        $this->addForeignKey(
            'fk-refresh_token-user_id',
            '{{%refresh_token}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-refresh_token-user_id',
            '{{%refresh_token}}'
        );

        $this->dropIndex(
            'idx-refresh_token-user_id',
            '{{%refresh_token}}'
        );

        $this->dropTable('{{%refresh_token}}');
    }
}
