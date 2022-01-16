<?php
namespace common\models\rep;

use yii\db\Expression;
use common\models\ar\AddressLogAR;

/**
 *  Репозиторий "Логирование вызовов прокси-адресов"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class AddressLogRep
{

    public static function insert(array $data) : void
    {
        $log             = new AddressLogAR;
        $log->id_address = $data['idAddress'];
        $log->url        = $data['url'];
        $log->proxy      = $data['fullAddress'];
        $log->type       = $data['type'];
        $log->http_code  = $data['httpCode'];
        $log->result     = $data['check'];
        $log->create_at  = new Expression('NOW()');
        $log->save();
    }
}