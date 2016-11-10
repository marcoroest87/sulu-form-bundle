<?php

namespace L91\Sulu\Bundle\FormBundle\Media;

use L91\Sulu\Bundle\FormBundle\DependencyInjection\L91SuluFormExtension;
use Sulu\Bundle\MediaBundle\Collection\Manager\CollectionManagerInterface;
use Sulu\Component\Media\SystemCollections\SystemCollectionManagerInterface;

/**
 * Tree strategy to create foreach form and page a collection.
 */
class CollectionStrategyTree implements CollectionStrategyInterface
{
    /**
     * @var CollectionManagerInterface
     */
    protected $collectionManager;

    /**
     * @var SystemCollectionManagerInterface
     */
    protected $systemCollectionManager;

    /**
     * CollectionTreeStrategy constructor.
     *
     * @param CollectionManagerInterface $collectionManager
     * @param SystemCollectionManagerInterface $systemCollectionManager
     */
    public function __construct(
        CollectionManagerInterface $collectionManager,
        SystemCollectionManagerInterface $systemCollectionManager
    ) {
        $this->collectionManager = $collectionManager;
        $this->systemCollectionManager = $systemCollectionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollectionId(
        $formId,
        $formTitle,
        $type,
        $typeId,
        $title,
        $locale
    ) {
        $collectionFormKey = L91SuluFormExtension::SYSTEM_COLLECTION_ROOT . '.' . $formId;
        $collectionKey = $collectionFormKey . '.' . $type . '_' . $typeId;

        try {
            $collection = $this->collectionManager->getByKey($collectionKey, $locale);

            if (!$collection) {
                // Not sure if getByKey will always throw an exception in any version of sulu

                throw new \Exception('Need to create system collection form form bundle.');
            }

            $collectionId = $collection->getId();
        } catch (\Exception $e) {
            // SystemCollection not exists

            try {
                $parentCollection = $this->collectionManager->getByKey($collectionKey, $locale);

                if (!$parentCollection) {
                    // Not sure if getByKey will always throw an exception in any version of sulu

                    throw new \Exception('Need to create parent system collection form form bundle.');
                }

                $parentCollectionId = $parentCollection->getId();
            } catch (\Exception $e) {
                // Create Type Collection
                $rootCollectionId = $this->systemCollectionManager->getSystemCollection(
                    L91SuluFormExtension::SYSTEM_COLLECTION_ROOT
                );

                $parentCollection = $this->collectionManager->save([
                    'title' => $formTitle,
                    'type' => ['id' => 2],
                    'parent' => $rootCollectionId,
                    'key' => $collectionFormKey,
                    'locale' => $locale,
                ], null);

                $parentCollectionId = $parentCollection->getId();
            }

            $collection = $this->collectionManager->save([
                'title' => $title,
                'type' => ['id' => 2],
                'parent' => $parentCollectionId,
                'key' => $collectionKey,
                'locale' => $locale,
            ], null);

            $collectionId = $collection->getId();
        }

        return $collectionId;
    }
}
