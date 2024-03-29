<?php

namespace SessionPackage\store;

/**
 * Class Session
 * @package SessionPackage\store
 */
class Session implements SessionInterface
{
    /**
     * @var string session名字
     */
    public $sesssionName = '';

    /**
     * Session constructor.
     *
     * @param null $key
     * @param null $pos
     * @param null $expire
     * @param null $option
     */
    public function __construct($key = null, $lockKey = null, $pos = null, $expire = null, $lockExpire = null, $option = null)
    {
        if ($key !== null) {
            $this->sesssionName = $key;
        }
        $this->sessionStatus();
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
        if (!$this->sessionStatus() || !isset($_SESSION[$key])) {
            return false;
        }
        $data = $_SESSION[$key];
        if (!is_array($data) || !isset($data['expire']) || $data['expire'] < 0) {
            return false;
        }
        if ($data['expire'] !== 0) {
            if (time() > $data['expire']) {
                return false;
            }
        }
        return true;
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
        if (!$this->has($key)) {
            return null;
        }
        $data = $_SESSION[$key];
        return isset($data['data']) ? $data['data'] : null;
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
        if (!$this->sessionStatus()) {
            return false;
        }
        $nowExpire = false;
        if (is_numeric($expire) && $expire >= 0) {
            $nowExpire = $expire;
        }

        /*$oldData = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        if (!empty($oldData) && isset($oldData['expire']) && $nowExpire === false) {
            $nowExpire = $oldData['expire'];
        }*/
        if ($nowExpire === false) {
            $nowExpire = 0;
        }
        $sessionData    = [
            'data'   => $data,
            'expire' => $nowExpire
        ];
        $_SESSION[$key] = $sessionData;
        return true;
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
        if (!$this->sessionStatus()) {
            return false;
        }
        $nowStamp  = time();
        $oldData   = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        $nowExpire = empty($oldData) ? $nowStamp - 1 : isset($oldData['expire']) ? $oldData['expire'] : $nowStamp - 1;
        if ($nowExpire > $nowStamp) {
            $nowExpire = $nowStamp - 1;
        }
        if (is_numeric($expire) && $expire >= 0) {
            if ($expire < $nowStamp) {
                $nowExpire = $expire;
            }
        }
        if (empty($oldData)) {
            $oldData = [
                'data'   => '',
                'expire' => $nowExpire
            ];
        } else {
            $oldData['expire'] = $nowExpire;
        }
        $_SESSION[$key] = $oldData;
        return true;
    }

    /**
     * 删除数据
     *
     * @param $key
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return bool|mixed
     */
    public function deleteData($key = null, $pos = null, $expire = null, $options = null)
    {
        // TODO: Implement deleteData() method.
        if ($key === null || $key == '') {
            $key = $this->sesssionName;
        }
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
        return true;
    }

    /**
     * 查看session状态
     *
     * @return bool
     */
    public function sessionStatus()
    {
        switch (session_status()) {
            case PHP_SESSION_DISABLED:
                return false;
                break;
            case PHP_SESSION_NONE:
                session_start();
                return true;
                break;
            case PHP_SESSION_ACTIVE:
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * 加锁
     *
     * @param null $key
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return mixed|void
     */
    public function lock($key = null, $pos = null, $expire = null, $options = null)
    {
        // TODO: Implement lock() method.

        return true;
    }

    /**
     * 解锁
     *
     * @param null $key
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return mixed|void
     */
    public function unlock($key = null, $pos = null, $expire = null, $options = null)
    {
        // TODO: Implement unlock() method.
        return true;
    }
}