<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task}}`.
 */
class m240703_095511_create_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text()->null(),
            'slug' => $this->string()->notNull()->unique(),
            'priority' => "ENUM('Low', 'Medium', 'High') NOT NULL",
            'assignee' => $this->string(15)->null(),
            'status' => "ENUM('New', 'In Progress', 'Testing', 'Deployed') DEFAULT 'New'",
            'created_by' => $this->string(15)->null(),
            'updated_by' => $this->string(15)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);


        // creates index for column `slug`
        $this->createIndex(
            '{{%idx-task-slug}}',
            '{{%task}}',
            'slug',
            true
        );

        // creates index for column `assignee`
        $this->createIndex(
            '{{%idx-task-assignee}}',
            '{{%task}}',
            'assignee'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-task-assignee}}',
            '{{%task}}',
            'assignee',
            '{{%user}}',
            'username',
            'SET NULL'
        );

        // creates index for column `created_by`
        $this->createIndex(
            '{{%idx-task-created_by}}',
            '{{%task}}',
            'created_by'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-task-created_by}}',
            '{{%task}}',
            'created_by',
            '{{%user}}',
            'username',
            'SET NULL'
        );

        // creates index for column `updated_by`
        $this->createIndex(
            '{{%idx-task-updated_by}}',
            '{{%task}}',
            'updated_by'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-task-updated_by}}',
            '{{%task}}',
            'updated_by',
            '{{%user}}',
            'username',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-task-assignee}}',
            '{{%task}}'
        );

        // drops index for column `assignee`
        $this->dropIndex(
            '{{%idx-task-assignee}}',
            '{{%task}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-task-created_by}}',
            '{{%task}}'
        );

        // drops index for column `created_by`
        $this->dropIndex(
            '{{%idx-task-created_by}}',
            '{{%task}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-task-updated_by}}',
            '{{%task}}'
        );

        // drops index for column `updated_by`
        $this->dropIndex(
            '{{%idx-task-updated_by}}',
            '{{%task}}'
        );

        // drops index for column `slug`
        $this->dropIndex(
            '{{%idx-task-slug}}',
            '{{%task}}'
        );

        $this->dropTable('{{%task}}');
    }
}
