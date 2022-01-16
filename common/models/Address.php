<?php
namespace common\models;

use yii\db\Expression;
use common\models\rep\AddressRep;
use yii\web\UploadedFile;

class Address
{
    const NO_ERROR = 0;
    const MSG_NO_ERROR = '';


    /**
     * Чтение и запись в БД списка прокси-адресов из файла
     *
     * @param array $file - загруженный файл
     * @param string $type - тип прокси-адресов в файле
     * @return array:
     *  [
     *      'error'  => [
     *          'code' => <int>,
     *          'description' => <string>
     *      ],
     *      'result' => [
     *          "countDuplicate": <int>,    // количество дублируемых записей
     *          "countUpload": <int>,       // количество загруженных записей
     *          "countAll": <int>           // общее количество записей в файле
     *      ]
     *  ]
     */
    public static function loadFromFile(UploadedFile $file, string $type='') : array
    {
        $ret = [
            'error'  => ['code' => self::NO_ERROR, 'description' => self::MSG_NO_ERROR],
            'result' => [],
        ];
        $retVal['countAll'] = $retVal['countUpload'] = $retVal['countDuplicate'] = 0;
        try {
            if ($fd = fopen($file->tempName, 'r')) {
                $data = [];
                while (!feof($fd)) {
                    if ($proxy = fgets($fd)) {
                        list($host, $port) = explode(':', $proxy);
                        if (is_null($port)) {
                            $port = '';
                        }
                        if (AddressRep::findDuplicate($host, $port)) {
                            ++$retVal['countDuplicate'];
                        } else {
                            $data[] = [$host, $port, $type];
                            ++$retVal['countUpload'];
                        }
                        ++$retVal['countAll'];
                    }
                }
                fclose($fd);

                AddressRep::batchInsert($data);

                $ret['result'] = $retVal;
            }
        } catch (\Exception $e) {
            $ret = [
                'error' => ['code' => $e->getCode(), 'description' => $e->getMessage()],
                'result' => [],
            ];
        }

        return $ret;
    }

    /**
     * Определение типа прокси
     *
     * @return array
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
    public static function typeDefinition() : array
    {
        $ret = [
            'error'  => ['code' => self::NO_ERROR, 'description' => self::MSG_NO_ERROR],
            'result' => [],
        ];
        $resultCheck = false;
        $proxyType = '';
        $page      = 'http://httpbin.org/ip';
        $userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
        try {
            if ($proxy = AddressRep::findAddressWithoutType()) {
                $proxyIp   = $proxy['host'] . ':' . $proxy['port'];
                $proxyPass = $proxy['pass'];
                if ((strlen($proxyIp) > 4) and (strlen($proxyPass) > 4)) {
                    $result = self::_checkProxy($page, $userAgent, $proxyIp, $proxyPass, CURLPROXY_SOCKS5);
                    if ($result['check']) {
                        $resultCheck = true;
                        $proxyType = 'SOCKS5A';
                    }
                }
                if ((strlen($proxyIp) > 4) and (strlen($proxyPass) < 4)) {
                    $result = self::_checkProxy($page, $userAgent, $proxyIp, $proxyPass, CURLPROXY_SOCKS5);
                    if ($result['check']) {
                        $resultCheck = true;
                        $proxyType = 'SOCKS5';
                    } else {
                        $result = self::_checkProxy($page, $userAgent, $proxyIp, $proxyPass, CURLPROXY_SOCKS4);
                        if ($result['check']) {
                            $resultCheck = true;
                            $proxyType = 'SOCKS4';
                        } else {
                            $result = self::_checkProxy($page, $userAgent, $proxyIp, $proxyPass, CURLPROXY_HTTP);
                            if ($result['check']) {
                                $resultCheck = true;
                                $proxyType = 'HTTP';
                            }
                        }
                    }
                }
                if ($resultCheck) {
                    $proxy->type = $proxyType;
                    $proxy->response_time = $result['time'];
                }
                $proxy->verification_date = new Expression('NOW()');
                $proxy->save();
                $ret['result'] = [
                    'check' => $resultCheck
                ];
            }
        } catch (\Exception $e) {
            $ret = [
                'error' => ['code' => $e->getCode(), 'description' => $e->getMessage()],
                'result' => [],
            ];
        }

        return $ret;
    }

    /**
     * Проверка прокси-адреса
     *
     * @param string $page - страница, на которой проверяется прокси-адрес
     * @param string $userAgent - агент
     * @param string $proxyIp - IP прокси-адреса
     * @param string $proxyPass - пароль прокси-адреса
     * @param int $proxyType - тип прокси-адреса
     * @return array
     *  [
     *      "check": <bool>,    // результат проверки
     *      "time": <float>     // время проверки
     *  ]
     */
    private static function _checkProxy($page, $userAgent, $proxyIp, $proxyPass, $proxyType) : array
    {
        $timeStart = microtime(1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $page);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 7);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
        curl_setopt($ch, CURLOPT_PROXYTYPE, $proxyType);
        curl_setopt($ch, CURLOPT_PROXY, $proxyIp);
        if (strlen($proxyPass)) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyPass);
        }
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $timeFinish = microtime(1);
        $time = round($timeFinish - $timeStart, 2);
        if ($httpCode == 200) {
            return [
                'check' => true,
                'time'  => $time,
            ];
        }
        return [
            'check' => false,
            'time'  => $time,
        ];
    }

}
