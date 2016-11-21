<?php

namespace L91\Sulu\Bundle\FormBundle\Provider\Resolver;

/**
 * Define the methods for dynamic list resolvers.
 */
interface DynamicListResolverInterface
{
    /**
     * Resolve an entry the return value need to be an array per line of the export.
     *
     * @param array $entry
     *
     * @return array
     */
    public function resolve(array $entry);
}
