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
     * @var array Callables
     * It works before filter operation. You can use the modifier to modify data from the request.
     * eg: https://site.com/blog/news
     * You can fetch your slug or id from the URL.
     */
    protected $modifiers = [];

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

    /**
     * @return array
     */
    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    /**
     * @param array $modifiers
     * @return AbstractAdapter
     */
    public function setModifiers(array $modifiers): self
    {
        $this->modifiers = $modifiers;
        return $this;
    }

    /**
     * @param callable $modifier
     * @return AbstractAdapter
     */
    public function addModifier(callable $modifier): self
    {
        $this->modifiers[] = $modifier;
        return $this;
    }

}