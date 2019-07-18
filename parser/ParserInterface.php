<?php

namespace SessionPackage\parser;

/**
 * Interface ParserInterface
 * @package SessionPackage
 */
interface ParserInterface
{
    /**
     * 数据编码
     *
     * @param $data
     * @return mixed
     */
    public function encode($data);

    /**
     * 数据解码
     *
     * @param $data
     * @return mixed
     */
    public function decode($data);
}