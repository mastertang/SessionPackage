<?php

namespace SessionPackage\parser;

/**
 * Class Serialize
 * @package SessionPackage\parser
 */
class Serialize implements ParserInterface
{
    /**
     * Serialize encode
     *
     * @param $data
     * @return mixed|string
     */
    public function encode($data)
    {
        // TODO: Implement encode() method.
        return serialize($data);
    }

    /**
     * Serialize decode
     *
     * @param $data
     * @return mixed|string
     */
    public function decode($data)
    {
        // TODO: Implement decode() method.
        return unserialize($data);
    }
}