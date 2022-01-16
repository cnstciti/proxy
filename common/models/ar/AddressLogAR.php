<?php
namespace common\models\ar;

use yii\db\ActiveRecord;

/**
 *  Таблица "Логирование вызовов прокси-адресов"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class AddressLogAR extends ActiveRecord
{

    /**
    * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
    */
    public static function tableName()
    {
        return '{{address_log}}';
    }
}