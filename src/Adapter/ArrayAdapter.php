<?php
/**
 * Copyright (c) 2019.
 */

namespace DelirehberiWebFinger\Adapter;


use DelirehberiWebFinger\ResourceDescriptorInterface;

class ArrayAdapter extends AbstractAdapter
{
    /**
     * @var array ResourceDescriptorInterface items
     */
    private $items;
    private $filter;

    /**
     * @param array $items
     * @return ArrayAdapter
     */
    public function set(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @param ResourceDescriptorInterface $item
     * @param string|null $key
     * @return ArrayAdapter
     */
    public function add(ResourceDescriptorInterface $item, string $key = null): self
    {
        if ($key !== null) {
            $this->items[$key] = $item;
        } else {
            $this->items[] = $item;
        }
        return $this;
    }

    /**
     * @param string $data
     * @return ResourceDescriptorInterface|null
     */
    public function getObject($data): ?ResourceDescriptorInterface
    {
        foreach ($this->modifiers as $modifier) {
            $data = call_user_func_array($modifier, [$data]);
        }

        $filter = $this->filter;

        $result = array_filter($this->items, function ($el) use ($data, $filter) {
            return call_user_func_array($filter, [$el, $data]);
        });
        if (count($result) == 0) {
            return null;
        }
        /** @var ResourceDescriptorInterface $object */
        $object = $result[0];
        return $object;
    }

    /**
     * @param callable $filter
     * @return ArrayAdapter
     */
    public function setFilter(callable $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

}