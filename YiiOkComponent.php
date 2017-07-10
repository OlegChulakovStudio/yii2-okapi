<?php
namespace  OlegChulakovStudio\okapi;

use sem\helpers\ArrayHelper;
use VK\VK;
use yii\base\Component;

/**
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */
class YiiOkComponent extends Component
{
    /**
     * Типа HTTP-запроса GET
     */
    const REQUEST_TYPE_GET = 'get';
    /**
     * Типа HTTP-запроса POST
     */
    const REQUEST_TYPE_POST = 'post';
    /**
     * Типа HTTP-запроса HEAD
     */
    const REQUEST_TYPE_HEAD = 'head';
    /**
     * Типа HTTP-запроса PUT
     */
    const REQUEST_TYPE_PUT = 'put';
    /**
     * Типа HTTP-запроса PATCH
     */
    const REQUEST_TYPE_PATCH = 'patch';
    /**
     * Типа HTTP-запроса DELETE
     */
    const REQUEST_TYPE_DELETE = 'delete';

    /**
     * Неизвестная ошибка
     */
    const ERROR_UNAVAILABLE = 'unavailable';
    const UNKNOWN = 1;
    const SERVICE = 2;
    const METHOD = 3;
    const REQUEST = 4;
    const ACTION_BLOCKED = 7;
    const FLOOD_BLOCKED = 8;
    const IP_BLOCKED = 9;
    const PERMISSION_DENIED = 10;
    const LIMIT_REACHED = 11;
    const CANCELLED = 12;
    const NOT_MULTIPART = 21;
    const NOT_ACTIVATED = 22;
    const NOT_YET_INVOLVED = 23;
    const NOT_OWNER = 24;
    const NOT_ACTIVE = 25;
    const TOTAL_LIMIT_REACHED = 26;
    const NETWORK = 30;
    const NETWORK_TIMEOUT = 31;
    const NOT_ADMIN = 50;
    const PARAM = 100;
    const PARAM_API_KEY = 101;
    const PARAM_SESSION_EXPIRED = 102;
    const PARAM_SESSION_KEY = 103;
    const PARAM_SIGNATURE = 104;
    const PARAM_RESIGNATURE = 105;
    const PARAM_ENTITY_ID = 106;
    const PARAM_USER_ID = 110;
    const PARAM_ALBUM_ID = 120;
    const PARAM_PHOTO_ID = 121;
    const PARAM_WIDGET = 130;
    const PARAM_MESSAGE_ID = 140;
    const PARAM_COMMENT_ID = 141;
    const PARAM_HAPPENING_ID = 150;
    const PARAM_HAPPENING_PHOTO_ID = 151;
    const PARAM_GROUP_ID = 160;
    const PARAM_PERMISSION = 200;
    const PARAM_APPLICATION_DISABLED = 210;
    const PARAM_DECISION = 211;
    const PARAM_BADGE_ID = 212;
    const PARAM_PRESENT_ID = 213;
    const PARAM_RELATION_TYPE = 214;

    /**
     * ID приложения
     * @var string
     */
    public $applicationId;

    /**
     * Публичный ключ приложения
     * @var string
     */
    public $applicationKey;

    /**
     * Секретынй ключ приложения
     * @var string
     */
    public $applicationSecretKey;

    /**
     * Токен для запросов где требуется авторизация
     * @var string
     */
    public $accessToken;

    /**
     * Объект для работы с vk сервисом
     * @var VK
     */
    protected $api;

    /**
     * Ошибки последнего запроса
     * @var array
     */
    protected $lastRequestErrors = [];


    const API_SERVER = 'https://api.ok.ru/fb.do';

    /**
     * Сделать запрос к апи
     * @param $method
     * @param $parameters
     * @param string $requestMethod
     * @return []
     */
    public function request($method, $parameters, $requestMethod = 'get')
    {
        if (!in_array($requestMethod, self::getHttpTypes())) {
            throw new \yii\base\InvalidCallException("Неверный тип HTTP-запроса!");
        }

        $this->flushRequestErrors();

        $client = new \GuzzleHttp\Client();

        $parameters = ArrayHelper::merge([
            'application_key' => $this->applicationKey,
            'format' => 'json',
            'method' => $method,
            'access_token' => $this->accessToken,
        ], ((array) $parameters));

        $parameters['sig'] = $this->getSig($parameters);

        $response = $client->request($requestMethod, static::API_SERVER,  [
            'query' => $parameters
        ]);

        $result = json_decode($response->getBody(), true);

        if (isset($result['error_code'])) {
            $this->addRequestError($result['error']['error_code']);
            \Yii::error(var_export($result['error'], 1));
            return [];
        }

        return $result;
    }

    /**
     * Производит сброс ошибок запроса перед.
     * Вызывается перед непосредственным запросом к API
     */
    protected function flushRequestErrors()
    {
        $this->lastRequestErrors;
    }

    /**
     * Возвращает список ошибок (если они были) в виде массива с описанием
     * @return array
     */
    public function getRequestErrors()
    {
        return $this->lastRequestErrors;
    }

    /**
     * Возвращает список возможных HTTP-запросов
     * @return array
     */
    protected static function getHttpTypes()
    {
        return [
            static::REQUEST_TYPE_DELETE,
            static::REQUEST_TYPE_GET,
            static::REQUEST_TYPE_HEAD,
            static::REQUEST_TYPE_PATCH,
            static::REQUEST_TYPE_POST,
            static::REQUEST_TYPE_PUT
        ];
    }

    /**
     * Добавляет ошибку
     * @param $code
     */
    private function addRequestError($code)
    {
        $this->lastRequestErrors[$code] = static::getErrorMessage($code);
    }

    /**
     * Описания ошибок
     * @see https://apiok.ru/dev/errors
     * @return array
     */
    public static function getErrorsDescription()
    {
        return [
            static::ERROR_UNAVAILABLE => 'Неизвестная ошибка',
            static::UNKNOWN => 'Неизвестная ошибка',
            static::SERVICE => 'Сервис временно недоступен',
            static::METHOD => '	Метод не существует',
            static::REQUEST => 'Не удалось обработать запрос, так как он неверный',
            static::ACTION_BLOCKED => 'Запрошенное действие временно заблокировано для текущего пользователя',
            static::FLOOD_BLOCKED => 'Выполнение метода заблокировано вследствие флуда',
            static::IP_BLOCKED => 'Выполнение метода заблокировано по IP-адресу вследствие подозрительных действий текущего пользователя или вследствие прочих ограничений, распространяющихся на конкретный метод',
            static::PERMISSION_DENIED => 'Отказ в разрешении. Возможная причина - пользователь не авторизовал приложение на выполнение операции',
            static::LIMIT_REACHED => 'Достигнут предел вызовов метода',
            static::CANCELLED => 'Операция прервана пользователем',
            static::NOT_MULTIPART => 'Не multi-part запрос при добавлении фотографий',
            static::NOT_ACTIVATED => 'Пользователь должен активировать свой аккаунт',
            static::NOT_YET_INVOLVED => 'Пользователь не вовлечён в приложение',
            static::NOT_OWNER => 'Пользователь не является владельцем объекта',
            static::NOT_ACTIVE => 'Ошибка рассылки нотификаций. Пользователь неактивен в приложении',
            static::TOTAL_LIMIT_REACHED => 'Ошибка рассылки нотификаций. Достигнут лимит нотификаций для приложения',
            static::NETWORK => 'Слишком большое тело запроса или проблема в обработке заголовков',
            static::NETWORK_TIMEOUT => 'Клиент слишком долго передавал тело запроса',
            static::NOT_ADMIN => 'У пользователя нет административных прав для выполнения данного метода',
            static::PARAM => 'Отсутствующий или неверный параметр',
            static::PARAM_API_KEY => 'Параметр application_key не указан или указан неверно',
            static::PARAM_SESSION_EXPIRED => 'Истек срок действия ключа сессии',
            static::PARAM_SESSION_KEY => 'Неверный ключ сессии',
            static::PARAM_SIGNATURE => 'Неверная подпись',
            static::PARAM_RESIGNATURE => 'Неверная повторная подпись',
            static::PARAM_ENTITY_ID => 'Неверный идентификатор дискуссии',
            static::PARAM_USER_ID => 'Неверный идентификатор пользователя',
            static::PARAM_ALBUM_ID => 'Неверный идентификатор альбома',
            static::PARAM_PHOTO_ID => 'Неверный идентификатор фотографии',
            static::PARAM_WIDGET => 'Неверный идентификатор виджета',
            static::PARAM_MESSAGE_ID => 'Неверный идентификатор сообщения',
            static::PARAM_COMMENT_ID => 'Неверный идентификатор комментария',
            static::PARAM_HAPPENING_ID => 'Неверный идентификатор события',
            static::PARAM_HAPPENING_PHOTO_ID => 'Неверный идентификатор фотографии события',
            static::PARAM_GROUP_ID => 'Неверный идентификатор группы',
            static::PARAM_PERMISSION => 'Приложение не может выполнить операцию. В большинстве случаев причиной является попытка получения доступа к операции без авторизации от пользователя.',
            static::PARAM_APPLICATION_DISABLED => 'Приложение отключено',
            static::PARAM_DECISION => 'Неверный идентификатор выбора',
            static::PARAM_BADGE_ID => 'Неверный идентификатор значка',
            static::PARAM_PRESENT_ID => 'Неверный идентификатор подарка',
            static::PARAM_RELATION_TYPE => 'Неверный идентификатор типа связи'

        ];
    }

    /**
     * Получить описание ошибки по коду
     * @param $code
     * @return mixed
     */
    public static function getErrorMessage($code)
    {
        $errors = self::getErrorsDescription();
        return isset($errors[$code]) ? $errors[$code] : $errors[static::ERROR_UNAVAILABLE];
    }

    /**
     * Получить подпись к запросу
     * @param $parameters
     * @return mixed|string
     */
    private function getSig($parameters)
    {
        $secretKey = $this->accessToken ? md5($this->accessToken . $this->applicationSecretKey) : md5($this->applicationSecretKey);
        $secretKey = mb_strtolower($secretKey);

        if (isset($parameters['access_token'])) {
            unset($parameters['access_token']);
        }
        if (isset($parameters['session_key'])) {
            unset($parameters['session_key']);
        }

        ksort($parameters, SORT_STRING);

        $res = '';
        foreach ($parameters as $code => $parameter) {
            $res.= $code . '=' . $parameter;
        }

        return mb_strtolower(md5($res . $secretKey));
    }
}
