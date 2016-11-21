<?php

namespace L91\Sulu\Bundle\FormBundle\Provider;

use L91\Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Component\Rest\ListBuilder\FieldDescriptor;

/**
 * Dynamic list provider interface.
 */
interface DynamicListProviderInterface
{
    /**
     * Get field descriptors.
     *
     * @param Form $form
     * @param string $locale
     *
     * @return FieldDescriptor[]
     */
    public function getFieldDescriptors(Form $form, $locale);

    /**
     * Get entries.
     *
     * @param Form $form
     * @param string $webspace
     * @param string $uuid
     * @param int $limit
     * @param int $page
     *
     * @return array
     */
    public function loadEntries(Form $form, $webspace, $uuid, $limit = 10, $page = 1);

    /**
     * Get total.
     *
     * @param string $webspace
     * @param string $uuid
     *
     * @return int
     */
    public function getTotal($webspace, $uuid);
}
