<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%task}}`.
 */
class m240707_102446_add_delete_column_to_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%task}}', 'deleted_at', $this->timestamp()->null()->defaultValue(null));
        $this->addColumn('{{%task_images}}', 'deleted_at', $this->timestamp()->null()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%task}}', 'deleted_at');
        $this->dropColumn('{{%task_images}}', 'deleted_at');
    }
}
