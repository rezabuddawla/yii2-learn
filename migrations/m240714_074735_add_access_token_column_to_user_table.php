<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m240714_074735_add_access_token_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
//        $this->addColumn('{{%user}}', 'access_token', $this->string()->after('password_hash'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        $this->dropColumn('{{%user}}', 'access_token');
    }
}
