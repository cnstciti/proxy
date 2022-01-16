<?php
namespace common\models\rep;

use Yii;
use common\models\ar\AddressAR;
/**
 *  Таблица "Список прокси"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class AddressRep
{

    public static function findDuplicate(string $host, string $port) : int
    {
        return AddressAR::find()
            ->where(['host' => $host, 'port' => $port])
            ->count();
    }

    public static function batchInsert(array $data) : void
    {
        Yii::$app->db->createCommand()->batchInsert(
            AddressAR::tableName(),
            ['host', 'port', 'type'],
            $data
        )->execute();
    }

    public static function findAddressWithoutType()
    {
        return AddressAR::find()
            ->where(['type' => ''])
            ->orderBy('verification_date ASC')
            ->one();
    }

    public static function getOne()
    {
        return AddressAR::find()
            ->where(['<>', 'type', ''])
            ->andWhere("work='good' or work is null")
            //->andWhere("work='good'")
            ->orderBy('rand()')
            ->asArray()
            ->one();
    }
}