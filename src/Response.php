<?php

namespace DelirehberiWebFinger;

/**
 * Class Response
 * @package DelirehberiWebFinger
 */
class Response extends \Symfony\Component\HttpFoundation\Response
{
    /**
     * @var JsonRD
     */
    private $data;

    public function __construct($content = '', int $status = 200, array $headers = array())
    {
        $headers['Access-Control-Allow-Origin'] = '*';
        $headers['Content-Type'] = "application/jrd+json";
        parent::__construct($content, $status, $headers);
    }

    /**
     * @param ResourceDescriptorInterface $data
     * @return $this
     */
    public function setData(ResourceDescriptorInterface $data)
    {
        $this->data = $data->transform();
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->data->toJSON();
    }
}