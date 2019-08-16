<?php

namespace SessionPackage\src;

use SessionPackage\parser\ParserInterface;
use SessionPackage\store\SessionInterface;

/**
 * Class SessionBase
 * @package SessionPackage
 */
class SessionBase
{
    // 存储类型 session
    const STORE_SESSION = 'session';

    // 存储类型 redis
    const STORE_REDIS = 'redis';

    // 数据解析类型 json
    const PARSER_JSON = 'json';

    // 数据解析类型 serialize(序列化)
    const PARSER_SERIALIZE = 'serialize';

    /**
     * @var string session名称
     */
    protected $sessionName = '';

    /**
     * @var string 锁名
     */
    protected $lockKeyName = '';

    /**
     * @var int 锁过期时间
     */
    protected $lockExpire = 2;

    /**
     * @var null session位置
     */
    protected $pos = null;

    /**
     * @var null  options选项数据
     */
    protected $options = null;

    /**
     * @var int 过期时间
     */
    protected $expire = null;

    /**
     * @var array session数据
     */
    protected $sessionData = [];

    /**
     * @var array 参数键值
     */
    protected $paramsKey = [];

    /**
     * @var string 子类名
     */
    protected $childClass = '';

    /**
     * @var null 解析器
     */
    protected $store = null;

    /**
     * @var string 解析器类型
     */
    protected $storeType = self::STORE_SESSION;

    /**
     * @var null 数据解析器
     */
    protected $parser = null;

    /**
     * @var string 数据解析类型
     */
    protected $parserType = null;

    /**
     * SessionBase constructor.
     *
     * @param null $sessionName
     * @param null $pos
     * @param null $expire 过期时间(单位：秒) ，+100，-100
     * @param null $options
     */
    public function __construct(
        $key = null,
        $lockKey = null,
        $pos = null,
        $expire = null,
        $lockExpire = null,
        $options = null
    )
    {
        $key === null or $this->sessionName = $key;
        $lockKey === null or $this->lockKeyName = $lockKey;
        $pos === null or $this->pos = $pos;
        $options === null or $this->options = $options;
        ($expire === null || !is_numeric($expire)) or $this->expire = $expire;
        ($lockExpire === null || !is_numeric($lockExpire)) or $this->lockExpire = $lockExpire;

        if (!empty($this->parserType)) {
            $parserClass = "SessionPackage\\parser\\" . ucfirst($this->parserType);
            if (class_exists($parserClass)) {
                $parser = new $parserClass();
                if ($parser instanceof ParserInterface) {
                    $this->parser = $parser;
                }
            }
        }

        if (!empty($this->storeType)) {
            $storeClass = "SessionPackage\\store\\" . ucfirst($this->storeType);

            if (class_exists($storeClass)) {
                $store = new $storeClass(
                    $this->sessionName,
                    $this->lockKeyName,
                    $this->pos,
                    $this->expire,
                    $this->lockExpire,
                    $this->options
                );
                if ($store instanceof SessionInterface) {
                    $this->store = $store;
                    if ($this->store->has()) {
                        $data = $this->store->getData();
                        if (!empty($this->parser)) {
                            $this->setData($this->parser->decode($data));
                        } else {
                            $this->setData($data);
                        }
                    }
                }
            }
        }
    }

    /**
     * 设置数据
     *
     * @param $data
     * @param bool $replace
     * @return bool
     */
    public function setData($data, $replace = false)
    {
        if (is_array($data)) {
            if (!$replace && is_array($this->sessionData)) {
                $this->sessionData = array_merge($this->sessionData, $data);
            } else {
                $this->sessionData = $data;
            }
            foreach ($this->paramsKey as $key) {
                if (isset($data[$key])) {
                    $this->$key = $data[$key];
                }
            }
        }
        return true;
    }

    /**
     * 是否有此session
     *
     * @param null $options
     * @return bool|mixed
     */
    public function has($options = null)
    {
        if (!empty($this->store) && is_object($this->store)) {
            if (!is_string($this->sessionName) || $this->sessionName === '') {
                throw new \Exception("Session name can not be empty!");
            }
            return $this->store->has(
                $this->sessionName,
                $this->pos,
                $options === null ? $this->options : $options
            );
        }
        return false;
    }

    /**
     * 数据是否存在session中
     *
     * @param $key
     * @return bool
     */
    public function dataHas($key)
    {
        return isset($this->sessionData[$key]);
    }

    /**
     * 获取SESSION数据
     *
     * @param null $options
     * @return mixed|null
     */
    public function getData($options = null)
    {
        if ($this->has($options)) {
            $data = $this->store->getData(
                $this->sessionName,
                $this->pos,
                $options === null ? $this->options : $options
            );
            if (!empty($this->parser)) {
                return $this->parser->decode($data);
            }
            return $data;
        }
        return NULL;
    }

    /**
     * 获取当前sessionData
     *
     * @return array
     */
    public function getCurrentData()
    {
        return $this->sessionData;
    }


    /**
     * 写session
     */
    public function write($pos = null, $expire = null, $options = null)
    {
        if (!empty($this->store) && is_object($this->store)) {
            if ($pos === null) {
                $pos = $this->pos;
            }
            if ($expire === null) {
                $expire = $this->expire !== null ? time() + $this->expire : null;
            }
            if (!is_numeric($expire) || $expire < 0) {
                $expire = null;
            }
            $session = [];
            foreach ($this->paramsKey as $key) {
                if (property_exists($this->childClass, $key)) {
                    $session[$key] = $this->$key;
                }
            }
            $session = array_merge($this->sessionData, $session);
            if (!empty($this->parser)) {
                $session = $this->parser->encode($session);
            }
            if (!is_string($this->sessionName) || $this->sessionName === '') {
                throw new \Exception("Session name can not be empty!");
            }
            return $this->store->setData(
                $this->sessionName,
                $session,
                $pos,
                $expire,
                $options === null ? $this->options : $options
            );
        } else {
            return false;
        }
    }

    /**
     * 加锁
     *
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return bool|mixed
     */
    public function lock($pos = null, $expire = false, $options = null)
    {
        if (!empty($this->store) && is_object($this->store)) {
            if ($pos === null) {
                $pos = $this->pos;
            }
            if ($expire != null && (!is_numeric($expire) || $expire < 0)) {
                $expire = null;
            }
            if (!is_string($this->sessionName) || $this->sessionName === '') {
                throw new \Exception("Session name can not be empty!");
            }
            return $this->store->lock(
                $this->lockKeyName,
                $pos,
                $expire,
                $options === null ? $this->options : $options
            );
        } else {
            return false;
        }
    }

    /**
     * @param null $pos
     * @param null $expire
     * @param null $options
     */
    public function unlock($pos = null, $expire = null, $options = null)
    {
        if (!empty($this->store) && is_object($this->store)) {
            if ($pos === null) {
                $pos = $this->pos;
            }
            if ($expire != null && (!is_numeric($expire) || $expire < 0)) {
                $expire = null;
            }
            if (!is_string($this->sessionName) || $this->sessionName === '') {
                throw new \Exception("Session name can not be empty!");
            }
            return $this->store->unlock(
                $this->lockKeyName,
                $pos,
                $expire,
                $options === null ? $this->options : $options
            );
        } else {
            return false;
        }
    }

    /**
     * 魔法方法
     *
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->sessionData[$name])) {
            return $this->sessionData[$name];
        } else {
            return NULL;
        }
    }

    /**
     * 魔法方法
     *
     * @param $name
     * @param $value
     * @return bool
     */
    public function __set($name, $value)
    {
        $this->sessionData[$name] = $value;
        if (property_exists($this->childClass, $name)) {
            $this->$name = $this->$value;
        }
        return true;
    }

    /**
     * 设置过期
     *
     * @param null $expire 过期时间戳
     * @param null $options
     * @return mixed
     */
    public function setExpire($expire = null, $options = null)
    {
        return $this->store->setExpire(
            $this->sessionName,
            $this->pos,
            $expire === null ? null : $expire,
            $options === null ? $this->options : $options
        );
    }
}