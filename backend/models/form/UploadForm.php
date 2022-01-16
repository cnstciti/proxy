<?php
namespace backend\models\form;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $loadFile;

    public function rules()
    {
        return [
            [['loadFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'txt'],
        ];
    }

}
