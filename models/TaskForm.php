<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Exception;

class TaskForm extends Model
{
    public string $assignee = "";
    public string $title = "";
    public string $description = "";
    public string $priority = "";
    public $images;


    public function rules(){
        return [
            [['assignee', 'title', 'description', 'priority'], 'required'],
            [['assignee', 'title', 'description'], 'string'],
            ['priority', 'in', 'range' => ['Low', 'Medium', 'High']],
            ['assignee', 'exist', 'targetClass' => User::class, 'targetAttribute' => 'username'],
            [['images'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 5, 'maxSize' => 6 * 1024 * 1024],
        ];
    }

    public function attributeLabels()
    {
        return [
            'assignee' => 'Assignee',
            'title' => 'Title',
            'description' => 'Description',
            'priority' => 'Priority',
            'images' => 'Issue Images', // Adjust as per your preference
        ];
    }


    /**
     * @throws \yii\base\Exception
     */
    private function generateUniqueSlug(): string
    {
        do {
            $slug = Yii::$app->security->generateRandomString(20);
        } while (Task::find()->where(['slug' => $slug])->exists());

        return $slug;
    }



    /**
     * @throws Exception
     */
    public function saveData()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$this->validate()) {
                return false;
            }

            $task = new Task();
            $task->title = $this->title;
            $task->description = $this->description;
            $task->slug = $this->generateUniqueSlug();
            $task->priority = $this->priority;
            $task->assignee = $this->assignee;
            $task->status = "New";
            $task->created_by = Yii::$app->user->identity->username;
            $task->updated_by = Yii::$app->user->identity->username;
            if (!$task->save()) {
                throw new \Exception("Failed to save task.");
            }

            if ($this->images && count($this->images) > 0) {
                foreach ($this->images as $file) {
                    $taskImages = new TaskImages();
                    $uploadsDir = Yii::getAlias('@webroot/uploads');
                    if (!is_dir($uploadsDir)) {
                        mkdir($uploadsDir, 0777, true);
                    }
                    $imagePath = $uploadsDir . '/' . $file->baseName . '.' . $file->extension;
                    echo $imagePath;
                    if (!$file->saveAs($imagePath)) {
                        throw new \Exception("Failed to save image.");
                    }
                    $taskImages->task_id = $task->id;
                    $taskImages->image_path = $file->baseName . '.' . $file->extension;
                    if (!$taskImages->save()) {
                        throw new \Exception("Failed to save task image.");
                    }
                }
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }


    public function uploadFile(int $task_id)
    {

    }
}