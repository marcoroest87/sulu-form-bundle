<?php

namespace L91\Sulu\Bundle\FormBundle\Admin;

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationProviderInterface;
use Sulu\Bundle\AdminBundle\Navigation\DisplayCondition;

/**
 * Register new tab for dynamic list to specific template.
 */
class DynamicListNavigationProvider implements ContentNavigationProviderInterface
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $provider;

    /**
     * DynamicListNavigationProvider constructor.
     *
     * @param string $template
     * @param string $property
     * @param string $provider
     */
    public function __construct($template, $property = 'form', $provider = null)
    {
        $this->template = $template;
        $this->property = $property;
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getNavigationItems(array $options = [])
    {
        $item = new ContentNavigationItem('Formular');
        $item->setAction('form-list');
        $item->setDisplay(['edit']);
        $item->setComponent('content/dynamic-list@l91suluform');
        $item->setComponentOptions([
            'template' => $this->template,
            'property' => $this->property,
            'provider' => $this->provider,
        ]);

        $item->setDisplayConditions(
            [
                new DisplayCondition('template', DisplayCondition::OPERATOR_EQUAL, $this->template),
            ]
        );

        return [ $item ];
    }
}
