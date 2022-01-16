<?php
namespace common\models;

use common\models\rep\AddressRep;
use common\models\rep\AddressLogRep;

class Proxy
{
    const CODE_NO_ERROR = 0;
    const MSG_NO_ERROR = '';
    const CODE_ERROR_DATA = 1;
    const MSG_ERROR_DATA = 'Proxy. Данные не прочитаны.';


    public static function readData(array $params) : array
    {
        try {
            $address     = AddressRep::getOne();
            $fullAddress = self::_fullAddress($address);
            $typeAddress = self::_typeAddress($address);
            $response    = self::_curlOpen($params['url'], $params['encodingContent'], $fullAddress, $typeAddress);
            $check       = self::_checkResponse($response);

            $insertData = [
                'idAddress'   => $address['id_address'],
                'url'         => $params['url'],
                'fullAddress' => $fullAddress,
                'type'        => $address['type'],
                'httpCode'    => $response['httpCode'],
                'check'       => $check,
            ];
            AddressLogRep::insert($insertData);

            if ($check) {
                $ret = [
                    'error'  => ['code' => self::CODE_NO_ERROR, 'description' => self::MSG_NO_ERROR],
                    'result' => ['data' => $response,],
                ];
            } else {
                $ret = [
                    'error'  => ['code' => self::CODE_ERROR_DATA, 'description' => self::MSG_ERROR_DATA],
                    'result' => ['data' => $response,],
                ];
            }
        } catch (\Exception $e) {
            $ret = [
                'error'  => ['code' => $e->getCode(), 'description' => $e->getMessage()],
                'result' => [],
            ];
        }

        return $ret;
    }

    private static function _fullAddress($address) : string
    {
        return trim($address['host'] . ':' . $address['port']);
    }

    private static function _typeAddress($address) : string
    {
        switch ($address['type']) {
            case 'HTTP':
                return CURLPROXY_HTTP;
            case 'SOCKS4':
                return CURLPROXY_SOCKS4;
            case 'SOCKS5':
                return CURLPROXY_SOCKS5;
            default:
                return '';
        }
    }

    private static function _curlOpen(string $url, string $encodingContent, string $proxyIp, int $proxyType) : array
    {
        $userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_PROXY, $proxyIp);
        curl_setopt($ch, CURLOPT_PROXYTYPE, $proxyType);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        /*
        if ($this->param['curloptEncoding']) {
            curl_setopt($ch, CURLOPT_ENCODING, $this->param['curloptEncoding']);
        }

        // если требуется авторизация на прокси-сервере
        //$proxyauth = 'user:password';
        // подключение к прокси-серверу
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
        // если требуется авторизация
        // curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
*/
        $content  = (string)curl_exec($ch);
        if ($encodingContent != 'utf-8') {
            $content = iconv($encodingContent, 'utf-8', $content);
        }
        /*
         iconv('windows-1251', 'utf-8', $content)

        echo (json_encode ([
            'status' => 'success',
            'data' => $ курсы
], JSON_UNESCAPED_UNICODE));
        */
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'content'  => $content,
            'httpCode' => $httpCode,
        ];
    }

    private static function _checkResponse(array $response) : bool
    {
        if (   $response['content'] === false
            || $response['httpCode'] != 200
            || strpos($response['content'], 'Content-Length: 0') !== false
        ) {
            return false;
        }
        /*
        if (strpos($response, 'Content-Length: 0') !== false) { // строка найдена
            return false;
        }
        /*
            if (strpos($response, 'HTTP/1.1 403') !== false) { // строка найдена
                return false;
            }

            if (strpos($response, 'HTTP/1.1 429') !== false) { // строка найдена
                return false;
            }
            preg_match('~Content-Length:(.*)\n~Uuis', $response, $res);
            $len = (int)$res[1];
            if (!$len) {
                return false;
            }
        */
        return true;
    }

}
