<?php
/**
 * Copyright (c) 2019.
 */

namespace DelirehberiWebFinger\Adapter;


abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected $scheme;

    /**
     * @param string $scheme
     * @return AdapterInterface
     */
    public function setScheme(string $scheme): AdapterInterface
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

}