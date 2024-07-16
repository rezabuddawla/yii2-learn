<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%access_token}}`.
 */
class m240715_061035_create_access_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%access_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token' => $this->string(64)->notNull()->unique(),
            'expires_at' => $this->timestamp()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex(
            'idx-access_token-user_id',
            '{{%access_token}}',
            'user_id'
        );

        $this->addForeignKey(
            'fk-access_token-user_id',
            '{{%access_token}}',
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
            'fk-access_token-user_id',
            '{{%access_token}}'
        );

        $this->dropIndex(
            'idx-access_token-user_id',
            '{{%access_token}}'
        );
        $this->dropTable('{{%access_token}}');
    }
}
