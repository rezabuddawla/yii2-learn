<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m240702_104706_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_images}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->unsignedBigInteger(),
            'image_path' => $this->string()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Add foreign key constraint
        $this->addForeignKey(
            '{{%fk-task_images-task_id-tasks-id}}',
            '{{%task_images}}',
            'task_id',
            '{{%tasks}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first to avoid errors on drop table
        $this->dropForeignKey('{{%fk-task_images-task_id-tasks-id}}', '{{%task_images}}');

        $this->dropTable('{{%task_images}}');
    }
}
