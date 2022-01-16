<?php
namespace console\controllers;

use common\models\Address;
use yii\console\Controller;

/**
 *
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class AddressController extends Controller
{
    /**
     * Определение типа прокси-адреса
     *
     * Вызов:
     *      php yii address/type-definition
     *
     * Вход:
     *      Нет
     *
     * Выход:
     *  [
     *      'error'  => [
     *          'code' => <int>,
     *          'description' => <string>
     *      ],
     *      'result' => [
     *          "check": <bool>    // результат проверки
     *      ]
     *  ]
     */
    public function actionTypeDefinition()
    {
        $result = Address::typeDefinition();
        print_r($result);
        //echo "OK" . PHP_EOL;
    }

}
