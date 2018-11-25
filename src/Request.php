<?php


namespace DelirehberiWebFinger;


/**
 * Class Request
 * @package DelirehberiWebFinger
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{
    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->get('resource');
    }
}