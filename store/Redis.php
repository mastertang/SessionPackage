<?php

namespace SessionPackage\store;

use Predis\Client;

/**
 * Class Redis
 * @package SessionPackage\store
 */
class Redis implements SessionInterface
{
    /**
     * @var string session名字
     */
    public $sesssionName = '';

    /**
     * @var int 数据库索引 0-15
     */
    public $database = 0;

    /**
     * @var null 过期时间
     */
    public $expire = null;

    /**
     * @var null 默认option
     */
    public $option = null;

    /**
     * @var array 连接参数
     */
    public $connection = [];

    /**
     * @var array
     */
    public $connectionOptions = [];

    /**
     * Session constructor.
     *
     * @param null $key
     * @param null $pos
     * @param null $expire
     * @param null $option
     */
    public function __construct($key = null, $pos = null, $expire = null, $option = null)
    {
        if ($key !== null) {
            $this->sesssionName = $key;
        }
        if ($pos !== null && $pos >= 0 && $pos <= 15) {
            $this->database = $pos;
        }

        if ($expire !== null && is_numeric($expire)) {
            $this->expire = $expire;
        }

        if (!empty($option)) {
            $this->connection        = $this->handleConnection($option);
            $this->connectionOptions = $this->handleOptions($option);
            $this->option            = $option;
        }
    }

    /**
     * 处理option参数
     *
     * @param $option
     * @return null
     */
    public function handleOptions($option)
    {
        if (!empty($option)) {
            if (isset($option['options']) && is_array($option['options'])) {
                return $option['options'];
            }
        }
        return null;
    }

    /**
     * 处理option参数，返回连接字符串
     *
     * @param $option
     * @return array|null|string
     */
    public function handleConnection($option)
    {
        if (!empty($option)) {
            if (isset($option['con']) && is_array($option['con'])) {
                if (is_string($option['con'])) {
                    return $option['con'];
                }
                if (isset($option['con']['scheme'], $option['con']['host'], $option['con']['port'])
                    && !empty($option['con']['scheme'])
                    && !empty($option['con']['host'])
                    && !empty($option['con']['port'])) {
                    return [
                        'scheme' => $option['con']['scheme'],
                        'host'   => $option['con']['host'],
                        'port'   => $option['con']['port']
                    ];
                }
            }
        }
        return null;
    }

    /**
     * 创建客户端
     *
     * @param null $option
     * @return bool|Client
     */
    public function createClient($option = null)
    {
        $connection       = null;
        $connectionOption = [];
        if ($option !== null) {
            $connection       = $this->handleConnection($option);
            $connectionOption = $this->handleOptions($option);
        }
        if (empty($connection)) {
            $connection = $this->connection;
        }
        if (empty($connectionOption)) {
            $connectionOption = $this->connectionOptions;
        }
        if (empty($connection)) {
            return false;
        }
        $client = new Client($connection, $connectionOption);
        return $client;
    }

    /**
     * 查询session是否存在或未过期
     *
     * @param $key
     * @param null $pos
     * @param null $options
     * @return bool|mixed
     */
    public function has($key = null, $pos = null, $options = null)
    {
        // TODO: Implement has() method.
        if ($key === null || $key == '') {
            $key = $this->sesssionName;
        }
        $db = $this->database;
        if ($pos !== null && $pos >= 0 && $pos <= 15) {
            $db = $pos;
        }
        if (!($client = $this->createClient($options))) {
            return false;
        }

        if (!$client->select($db)) {
            return false;
        };
        return $client->exists($key) ? true : false;
    }

    /**
     * 获取session数据
     *
     * @param $key
     * @param null $pos
     * @param null $options
     * @return mixed|null
     */
    public function getData($key = null, $pos = null, $options = null)
    {
        // TODO: Implement getData() method.
        if ($key === null || $key == '') {
            $key = $this->sesssionName;
        }
        $db = $this->database;
        if ($pos !== null && $pos >= 0 && $pos <= 15) {
            $db = $pos;
        }
        if (!($client = $this->createClient($options))) {
            return false;
        }


        if (!$client->select($db)) {
            return false;
        };
        return $client->get($key);
    }

    /**
     * 保存session数据
     *
     * @param $key
     * @param $data
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return bool|mixed
     */
    public function setData($key = null, $data, $pos = null, $expire = null, $options = null)
    {
        // TODO: Implement setData() method.
        if ($key === null || $key == '') {
            $key = $this->sesssionName;
        }
        $db = $this->database;
        if ($pos !== null && $pos >= 0 && $pos <= 15) {
            $db = $pos;
        }
        if (!($client = $this->createClient($options))) {
            return false;
        }
        if (!$client->select($db)) {
            return false;
        };
        $nowExpire = null;
        if (is_numeric($expire) && $expire > 0) {
            $nowExpire = $expire - time();
            if ($expire < 0) {
                $nowExpire = 0;
            }
        }
        if ($nowExpire === null) {
            $nowExpire = abs($this->expire);
        }
        if ($nowExpire === 0) {
            $nowExpire = 20;
        }
        return $client->setex($key, $nowExpire, $data) ? true : false;
    }

    /**
     * 设置session过期
     *
     * @param $key
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return bool|mixed
     */
    public function setExpire($key = null, $pos = null, $expire = null, $options = null)
    {
        // TODO: Implement setExpire() method.
        if ($key === null || $key == '') {
            $key = $this->sesssionName;
        }
        $db = $this->database;
        if ($pos !== null && $pos >= 0 && $pos <= 15) {
            $db = $pos;
        }
        if (!($client = $this->createClient($options))) {
            return false;
        }
        if (!$client->select($db)) {
            return false;
        };
        return $client->expire($key, 0);
    }

    /**
     * 删除数据
     *
     * @param $key
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return bool|int|mixed
     */
    public function deleteData($key = null, $pos = null, $expire = null, $options = null)
    {
        // TODO: Implement deleteData() method.
        if ($key === null || $key == '') {
            $key = $this->sesssionName;
        }
        $db = $this->database;
        if ($pos !== null && $pos >= 0 && $pos <= 15) {
            $db = $pos;
        }
        if (!($client = $this->createClient($options))) {
            return false;
        }
        if (!$client->select($db)) {
            return false;
        };
        return $client->expire($key, 0);
    }
}