<?php
/**
 * Copyright (c) 2019.
 */

namespace DelirehberiWebFinger;


use DelirehberiWebFinger\Adapter\AdapterInterface;

class WebFinger
{
    /**
     * @var array Adapter list
     */
    private $resources = [];

    /**
     * @param AdapterInterface $adapter
     * @return WebFinger
     */
    public function addResource(AdapterInterface $adapter): self
    {
        $this->resources[$adapter->getScheme()][] = $adapter;
        return $this;
    }

    /**
     * @param string $query
     * @return JsonRD|null
     * @throws \Exception
     */
    public function response(string $query): ?JsonRD
    {
        $response = null;
        $resolve = $this->resolve($query);
        foreach ($this->resources[$resolve['scheme']] as $adapter) {
            /** @var  AdapterInterface $adapter */
            $result = $adapter->getObject($resolve['data']);
            if ($result instanceof ResourceDescriptorInterface) {
                $response = $result;
                break;
            }
        }
        $transformedResponse = $response->transform();

        if (isset($resolve['rel'])) {
            //@todo it isn`t seems right. needs better solution.
            $transformedResponse = $this->filterByRel($transformedResponse, $resolve);
        }

        return $transformedResponse;
    }

    /**
     * @param $query
     * @return array
     * @throws \Exception
     */
    public function resolve($query): array
    {
        //fix
        $query = str_replace('?', '', $query);

        $result = [];
        $params = explode('&', $query);
        foreach ($params as $param) {
            list($key, $value) = explode('=', $param);
            $result[$key] = $value;
        }
        if (!isset($result['resource'])) {
            throw new \Exception("query component MUST include the
   \"resource\" parameter exactly once and set to the value of the URI for
   which information is being sought");
        }
        $url = parse_url($result['resource']);
        $result['scheme'] = $url['scheme'];

        // @todo needs better solution.
        switch ($url['scheme']) {
            case 'acct':
                $result['data'] = $url['path'];
                break;
            default:
                $result['data'] = $result['resource'];
                break;
        }

        return $result;
    }

    /**
     * @param JsonRD $response
     * @param array $resolve
     * @return JsonRD
     */
    private function filterByRel(JsonRD $response, array $resolve)
    {
        $filteredResponse = new JsonRD();
        $filteredResponse
            ->setSubject($response->getSubject());
        foreach ($response->getLinks() as $link) {
            /** @var JsonRDLink $link */
            if ($link->getRel() == $resolve['rel']) {
                $filteredResponse->addLink($link);
            }
        }

        return $filteredResponse;
    }
}