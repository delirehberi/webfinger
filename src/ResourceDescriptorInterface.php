<?php


namespace DelirehberiWebFinger;

/**
 * Interface ResourceDescriptorInterface
 * @package DelirehberiWebFinger
 */
interface ResourceDescriptorInterface
{
    public function transform():JsonRD;
}