<?php

namespace app\models;

use app\models\helper\BaseActiveRecord;
use Yii;
use yii\db\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "task_images".
 *
 * @property int $id
 * @property int $task_id
 * @property string $image_path
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 *
 * @property Task $task
 */
class TaskImages extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task_images';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_id', 'image_path'], 'required'],
            [['task_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['image_path'], 'string', 'max' => 255],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Task ID',
            'image_path' => 'Image Path',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    public static function find()
    {
        return parent::find()->where(['task_images.deleted_at' => null]);
    }

    /**
     * @throws Exception
     */
    public function softDelete(): bool
    {
        $this->deleted_at = new Expression('NOW()');
        return $this->save(false, ['deleted_at']);
    }
}
