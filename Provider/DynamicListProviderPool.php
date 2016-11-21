<?php

namespace L91\Sulu\Bundle\FormBundle\Provider;

/**
 * Collection all available dynamic list providers.
 */
class DynamicListProviderPool implements DynamicListProviderPoolInterface
{
    /**
     * @var DynamicListProviderInterface
     */
    protected $providers;

    /**
     * @var string
     */
    protected $defaultProvider;

    /**
     * DynamicListProviderPool constructor.
     *
     * @param string $defaultProvider
     */
    public function __construct(
        $defaultProvider
    ) {
        $this->defaultProvider = $defaultProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function add($alias, DynamicListProviderInterface $provider)
    {
        $this->providers[$alias] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function get($alias)
    {
        if (!$alias) {
            $alias = $this->defaultProvider;
        }

        if (!isset($this->providers[$alias])) {
            throw new \Exception(sprintf('The dynamic list provider "%s" was not found.', $alias));
        }

        return $this->providers[$alias];
    }
}
