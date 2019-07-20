<?php
/**
 * Copyright (c) 2019.
 */

namespace DelirehberiWebFinger\Adapter;


use DelirehberiWebFinger\JsonRD;
use DelirehberiWebFinger\ResourceDescriptorInterface;

class ArrayAdapter extends AbstractAdapter
{
    private $items;
    private $filter;
    private $modifiers = [];

    public function set(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    public function add(ResourceDescriptorInterface $item, string $key = null): self
    {
        if ($key !== null) {
            $this->items[$key] = $item;
        } else {
            $this->items[] = $item;
        }
        return $this;
    }

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

    public function setFilter(callable $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    public function addModifier(callable $modifier): self
    {
        $this->modifiers[] = $modifier;
        return $this;
    }
}