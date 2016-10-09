<?php

namespace L91\Sulu\Bundle\FormBundle\Content\Types;

use Doctrine\ORM\NoResultException;
use L91\Sulu\Bundle\FormBundle\Entity\Dynamic;
use L91\Sulu\Bundle\FormBundle\Event\DynFormSavedEvent;
use L91\Sulu\Bundle\FormBundle\Form\DynamicFormBuilder;
use L91\Sulu\Bundle\FormBundle\Form\HandlerInterface;
use L91\Sulu\Bundle\FormBundle\Form\Type\DynamicFormType;
use L91\Sulu\Bundle\FormBundle\Repository\FormRepository;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;
use Sulu\Component\Media\SystemCollections\SystemCollectionManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * ContentType for selecting a form.
 */
class FormSelect extends SimpleContentType
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var HandlerInterface
     */
    private $formHandler;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var DynamicFormBuilder
     */
    private $dynamicFormBuilder;

    /**
     * FormSelect constructor.
     *
     * @param string $template
     * @param HandlerInterface $formHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param DynamicFormBuilder $dynamicFormBuilder
     */
    public function __construct(
        $template,
        HandlerInterface $formHandler,
        EventDispatcherInterface $eventDispatcher,
        DynamicFormBuilder $dynamicFormBuilder
    ) {
        parent::__construct('FormSelect', '');
        $this->template = $template;
        $this->formHandler = $formHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->dynamicFormBuilder = $dynamicFormBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentData(PropertyInterface $property)
    {
        $id = (int) $property->getValue();

        if (!$id) {
            return;
        }

        $property->getStructure()->getNodeType()

        $form = $this->dynamicFormBuilder->build(
            $id,
            $property->getStructure()->getUuid(),
            'page',  // TODO FIXME GET PAGE TYPE
            $property->getStructure()->getLanguageCode(),
            $property->getStructure()->getView(),
            $property->getStructure()->getWebspaceKey(),
            $property->getName()
        );

        if (!$form) {
            return;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Dynamic $dynamic */
            $dynamic = $form->getData();
            $serializedObject = $dynamic->serialize();

            // save
            $this->formHandler->handle(
                $form,
                [
                    '_form_type' => $formType, // TODO
                    'formEntity' => $serializedObject,
                ]
            );

            $event = new DynFormSavedEvent($serializedObject);
            $this->eventDispatcher->dispatch(DynFormSavedEvent::NAME, $event);

            // Do redirect after success
            throw new HttpException(302, null, null, ['Location' => '?send=true']);
        }

        return $form->createView();
    }

    /**
     * {@inheritdoc}
     */
    public function getViewData(PropertyInterface $property)
    {
        $id = (int) $property->getValue();

        return [
            'id' => $id,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
