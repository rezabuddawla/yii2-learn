<?php

namespace app\models;

use app\models\helper\BaseActiveRecord;
use Yii;
use yii\db\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "task".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $slug
 * @property string $priority
 * @property string|null $assignee
 * @property string|null $status
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 * @property User $assignee0
 * @property User $createdBy
 * @property User $updatedBy
 */
class Task extends BaseActiveRecord
{

    public function fields()
    {
        return ['title', 'description', 'slug', 'priority', 'assignee', 'status', 'created_by', 'updated_by', 'taskImages'];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'priority', 'assignee'], 'required'],
            [['description', 'priority', 'status'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'slug'], 'string', 'max' => 255],
            [['assignee', 'created_by', 'updated_by'], 'string', 'max' => 15],
            [['slug'], 'unique'],
            [['assignee'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['assignee' => 'username']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'username']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'username']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'slug' => 'Slug',
            'priority' => 'Priority',
            'assignee' => 'Assignee',
            'status' => 'Status',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            [['deleted_at'], 'safe'],
        ];
    }

    /**
     * Gets query for [[Assignee0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssignee0()
    {
        return $this->hasOne(User::class, ['username' => 'assignee']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['username' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['username' => 'updated_by']);
    }

    /**
     * Gets query for [[TaskImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskImages()
    {
        return $this->hasMany(TaskImages::class, ['task_id' => 'id']);
    }


    public static function find()
    {
        return parent::find()->where(['task.deleted_at' => null]);
    }


    /**
     * @throws Exception
     */
    public function softDelete()
    {
        $this->deleted_at = new Expression('NOW()');
        return $this->save(false, ['deleted_at']);
    }
}
