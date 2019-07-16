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
     * @var array 连接option
     */
    public $connectionOption = [];

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
            $this->connectionOption = $this->handleOption($option);
            $this->option           = $option;
        }
    }

    /**
     * 处理option参数，返回连接字符串
     *
     * @param $option
     * @return array|null|string
     */
    public function handleOption($option)
    {
        if (!empty($option)) {
            if (is_string($option)) {
                return $option;
            }
            if (is_array($option)) {
                if (isset($option['scheme'], $option['host'], $option['port'])
                    && !empty($option['scheme'])
                    && !empty($option['host'])
                    && !empty($option['port'])) {
                    return [
                        'scheme' => $option['scheme'],
                        'host'   => $option['host'],
                        'port'   => $option['port']
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
        $connection = null;
        if ($option !== null) {
            $connection = $this->handleOption($option);
        }
        if (empty($connection)) {
            $connection = $this->connectionOption;
        }
        if (empty($connection)) {
            return false;
        }
        $client = new Client($connection);
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
    public function has($key, $pos = null, $options = null)
    {
        // TODO: Implement has() method.
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
    public function getData($key, $pos = null, $options = null)
    {
        // TODO: Implement getData() method.
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
    public function setData($key, $data, $pos = null, $expire = null, $options = null)
    {
        // TODO: Implement setData() method.
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
            $nowExpire = -1;
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
    public function setExpire($key, $pos = null, $expire = null, $options = null)
    {
        // TODO: Implement setExpire() method.
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
    public function deleteData($key, $pos = null, $expire = null, $options = null)
    {
        // TODO: Implement deleteData() method.
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