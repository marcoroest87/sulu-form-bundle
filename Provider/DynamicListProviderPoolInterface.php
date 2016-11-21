<?php

namespace L91\Sulu\Bundle\FormBundle\Provider;

/**
 * Defines the methods for a dynamic list provider pool.
 */
interface DynamicListProviderPoolInterface
{
    /**
     * Add a new provider.
     *
     * @param string $alias
     * @param DynamicListProviderInterface $provider
     */
    public function add($alias, DynamicListProviderInterface $provider);

    /**
     * Get dynamic list provider.
     *
     * @param string $alias
     *
     * @return DynamicListProviderInterface
     *
     * @throws \Exception
     */
    public function get($alias);
}
