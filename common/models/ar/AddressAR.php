<?php
namespace common\models\ar;

use yii\db\ActiveRecord;

/**
 *  Таблица "Список прокси-адресов"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class AddressAR extends ActiveRecord
{

    /**
    * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
    */
    public static function tableName()
    {
        return '{{address}}';
    }
}