<?php

namespace L91\Sulu\Bundle\FormBundle\Provider;

use Doctrine\ORM\EntityRepository;
use L91\Sulu\Bundle\FormBundle\Entity\Dynamic;
use L91\Sulu\Bundle\FormBundle\Entity\Form;
use L91\Sulu\Bundle\FormBundle\Provider\Resolver\DynamicListResolverInterface;
use Sulu\Component\Rest\ListBuilder\FieldDescriptor;

/**
 * Dynamic list provider.
 */
class DynamicListProvider implements DynamicListProviderInterface
{
    /**
     * @var EntityRepository
     */
    protected $dynamicRepository;

    /**
     * @var DynamicListResolverInterface
     */
    protected $dynamicListResolver;

    /**
     * DynamicListProvider constructor.
     *
     * @param EntityRepository $dynamicRepository
     * @param DynamicListResolverInterface $dynamicListResolver
     */
    public function __construct(
        EntityRepository $dynamicRepository,
        DynamicListResolverInterface $dynamicListResolver
    ) {
        $this->dynamicRepository = $dynamicRepository;
        $this->dynamicListResolver = $dynamicListResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldDescriptors(Form $form, $locale)
    {
        $fieldDescriptors = [];

        foreach ($form->getFields() as $field) {
            $title = '';
            $translation = $field->getTranslation($locale);

            if (!$translation) {
                $title = $translation->getTitle();
            }

            $fieldDescriptors[$field->getKey()] = new FieldDescriptor(
                $field->getKey(),
                $title,
                false,
                true
            );
        }

        return $fieldDescriptors;
    }

    /**
     * {@inheritdoc}
     */
    public function loadEntries(Form $form, $webspace, $uuid, $limit = 10, $offset = 0)
    {
        $entries = [];

        /** @var Dynamic[] $dynamics */
        $dynamics = $this->loadDynamics($webspace, $uuid, $limit, $offset);

        foreach ($dynamics as $dynamic) {
            $entry = [];

            foreach ($form->getFields() as $field) {
                $entry[$field->getKey()] = $dynamic->getField($field->getKey());
            }

            $entries = $this->addEntry($entries, $this->dynamicListResolver->resolve($entry));
        }

        return $entries;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal($webspace, $uuid)
    {
        return count($this->dynamicRepository->findBy([
            'webspace' => $webspace,
            'uuid' => $uuid,
        ]));
    }

    /**
     * Add entry to entries array.
     *
     * @param $entries
     * @param $entry
     *
     * @return array
     */
    protected function addEntry($entries, $entry)
    {
        return array_merge(
            $entries,
            array_values(
                $this->dynamicListResolver->resolve($entry)
            )
        );
    }

    /**
     * Load Dynamics.
     *
     * @param string $webspace
     * @param string $uuid
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    protected function loadDynamics($webspace, $uuid, $limit = 10, $offset = 0)
    {
        return $this->dynamicRepository->findBy(
            [
                'webspace' => $webspace,
                'uuid' => $uuid,
            ],
            [
                'created' => 'asc'
            ],
            $limit,
            $offset
        );
    }
}
