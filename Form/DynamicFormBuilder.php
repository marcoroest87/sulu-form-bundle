<?php

namespace L91\Sulu\Bundle\FormBundle\Form;

use Doctrine\ORM\NoResultException;
use L91\Sulu\Bundle\FormBundle\Cache\CacheTrait;
use L91\Sulu\Bundle\FormBundle\Entity\Dynamic;
use L91\Sulu\Bundle\FormBundle\Entity\Form;
use L91\Sulu\Bundle\FormBundle\Form\Type\DynamicFormType;
use L91\Sulu\Bundle\FormBundle\Repository\FormRepository;
use Sulu\Component\Media\SystemCollections\SystemCollectionManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Dynamic Form Builder.
 */
class DynamicFormBuilder
{
    use CacheTrait;

    /**
     * @var FormRepository
     */
    protected $formRepository;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var SystemCollectionManagerInterface
     */
    protected $systemCollectionManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * DynamicBuilder constructor.
     *
     * @param FormRepository $formRepository
     * @param FormFactoryInterface $formFactory
     * @param SystemCollectionManagerInterface $systemCollectionManager
     * @param RequestStack $requestStack
     */
    public function __construct(
        FormRepository $formRepository,
        FormFactoryInterface $formFactory,
        SystemCollectionManagerInterface $systemCollectionManager,
        RequestStack $requestStack
    ) {
        $this->formRepository = $formRepository;
        $this->formFactory = $formFactory;
        $this->systemCollectionManager = $systemCollectionManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Build dynamic form.
     *
     * @param int $formId
     * @param string $template
     * @param string $webspaceKey
     * @param string $locale
     * @param string $uuid
     * @param string $name
     * @param string $type
     *
     * @return FormInterface
     */
    public function build(
        $formId,
        $uuid,
        $type,
        $locale,
        $template,
        $webspaceKey,
        $name = 'form'
    ) {
        $locale = $this->getLocale($locale);

        $cacheId = implode(func_get_args(), '_');
        $form = $this->getCache($cacheId);

        if (!$form) {
            $form = $this->buildForm($formId, $template, $webspaceKey, $locale, $uuid, $name, $type);

            if ($form instanceof FormInterface) {
                $form->handleRequest($this->requestStack->getCurrentRequest());
            }

            $this->setCache($cacheId, $form);
        }

        return $form;
    }

    /**
     * Build form.
     *
     * @param $formId
     * @param $template
     * @param $webspaceKey
     * @param $locale
     * @param $uuid
     * @param $name
     * @param $type
     *
     * @return FormInterface|void
     */
    protected function buildForm(
        $formId,
        $template,
        $webspaceKey,
        $locale,
        $uuid,
        $name,
        $type
    ) {
        try {
            $formEntity = $this->formRepository->findById($formId, $locale);
        } catch (NoResultException $e) {
            return;
        }

        $defaults = $this->getDefaults($formEntity, $locale);

        $formType = $this->createFormType(
            $formEntity,
            $locale,
            $name,
            $template,
            $this->systemCollectionManager->getSystemCollection('l91_sulu_form.attachments')
        );

        return $this->formFactory->create(
            $formType,
            new Dynamic($uuid, $locale, $formEntity, $webspaceKey, $defaults) // TODO add type
        );
    }

    /**
     * Create form type.
     *
     * @param Form $formEntity
     * @param string $locale
     * @param string $name
     * @param string $template
     * @param int $collectionId
     *
     * @return DynamicFormType
     */
    protected function createFormType(
        $formEntity,
        $locale,
        $name,
        $template,
        $collectionId
    ) {
        return new DynamicFormType(
            $formEntity,
            $locale,
            $name,
            $template,
            $collectionId
        );
    }

    /**
     * Get form defaults.
     *
     * @param Form $formEntity
     * @param string $locale
     *
     * @return array
     */
    protected function getDefaults(Form $formEntity, $locale)
    {
        $defaults = [];
        foreach ($formEntity->getFields() as $field) {
            $translation = $field->getTranslation($locale);

            if ($translation && $translation->getDefaultValue()) {
                $value = $translation->getDefaultValue();

                // handle special types
                switch ($field->getType()) {
                    case Dynamic::TYPE_DATE:
                        $value = new \DateTime($value);
                        break;
                    case Dynamic::TYPE_DROPDOWN_MULTIPLE:
                    case Dynamic::TYPE_CHECKBOX_MULTIPLE:
                        $value = preg_split('/\r\n|\r|\n/', $value, -1, PREG_SPLIT_NO_EMPTY);
                        break;
                }

                $defaults[$field->getKey()] = $value;
            }
        }

        return $defaults;
    }

    /**
     * Return the form locale.
     *
     * @param $givenLocale
     *
     * @return string
     */
    protected function getLocale($givenLocale)
    {
        if ($givenLocale) {
            return $givenLocale;
        }

        $request = $this->requestStack->getCurrentRequest();

        return $request->getLocale();
    }
}
