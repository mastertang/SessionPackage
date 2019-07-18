<?php

namespace SessionPackage\store;

/**
 * Interface SessionInterface
 * @package SessionPackage
 */
interface SessionInterface
{
    /**
     * SessionInterface constructor.
     *
     * @param null $key
     * @param null $pos
     * @param null $expire
     * @param null $option
     */
    public function __construct($key = null, $pos = null, $expire = null, $option = null);

    /**
     * 保存session数据
     *
     * @param $key
     * @param $data
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return mixed
     */
    public function setData($key = null, $data, $pos = null, $expire = null, $options = null);

    /**
     * 获取session数据
     *
     * @param $key
     * @param null $pos
     * @param null $options
     * @return mixed
     */
    public function getData($key = null, $pos = null, $options = null);

    /**
     *
     *
     * @param $key
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return mixed
     */
    /**
     * 查询是否存在
     *
     * @param $key
     * @param null $pos
     * @param null $options
     * @return mixed
     */
    public function has($key = null, $pos = null, $options = null);

    /**
     * 设置过期
     *
     * @param $key
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return mixed
     */
    public function setExpire($key = null, $pos = null, $expire = null, $options = null);


    /**
     * 删除数据
     *
     * @param $key
     * @param null $pos
     * @param null $expire
     * @param null $options
     * @return mixed
     */
    public function deleteData($key = null, $pos = null, $expire = null, $options = null);
}