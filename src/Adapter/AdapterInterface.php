<?php
/**
 * Copyright (c) 2019.
 */

namespace DelirehberiWebFinger\Adapter;


use DelirehberiWebFinger\JsonRD;
use DelirehberiWebFinger\ResourceDescriptorInterface;

interface AdapterInterface
{
    /**
     * @param $data string  gets from request
     * @return ResourceDescriptorInterface       single object by request with filter all resources
     */
    public function getObject($data):?ResourceDescriptorInterface;
    /**
     * @param $scheme string    Resource scheme. eg: acct: , http:// , mailto::
     * @return AdapterInterface
     */
    public function setScheme(string $scheme):AdapterInterface;

    /**
     * @return string
     */
    public function getScheme():string;
}