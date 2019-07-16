<?php

namespace SessionPackage\parser;

use SessionPackage\ParserInterface;

/**
 * Class Json
 * @package SessionPackage\parser
 */
class Json implements ParserInterface
{
    /**
     * Json encode
     *
     * @param $data
     * @return mixed|string
     */
    public function encode($data)
    {
        // TODO: Implement encode() method.
        return json_encode($data);
    }

    /**
     * Json decode
     *
     * @param $data
     * @return mixed|string
     */
    public function decode($data)
    {
        // TODO: Implement decode() method.
        return json_encode($data, true);
    }
}