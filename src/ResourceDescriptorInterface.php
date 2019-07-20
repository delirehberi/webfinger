<?php


namespace DelirehberiWebFinger;

/**
 * Interface ResourceDescriptorInterface
 * @package DelirehberiWebFinger
 */
interface ResourceDescriptorInterface
{
    /**
     * @return JsonRD
     */
    public function transform():JsonRD;
}