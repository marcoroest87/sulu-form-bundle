<?php

namespace L91\Sulu\Bundle\FormBundle\Cache;

trait CacheTrait
{
    /**
     * @var mixed[]
     */
    protected $cache = [];

    /**
     * @return mixed
     */
    protected function setCache($cacheId, $object)
    {
        $this->cache[$cacheId] = $object;
    }

    /**
     *
     *
     * @param $cacheId
     *
     * @return mixed
     */
    protected function getCache($cacheId)
    {
        return isset($this->cache[$cacheId]) ? $this->cache[$cacheId] : null;
    }
}
