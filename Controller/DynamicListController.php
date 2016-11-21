<?php

namespace L91\Sulu\Bundle\FormBundle\Controller;

use FOS\RestBundle\Routing\ClassResourceInterface;
use L91\Sulu\Bundle\FormBundle\Entity\Form;
use L91\Sulu\Bundle\FormBundle\Provider\DynamicListProviderInterface;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller to create dynamic form entries list.
 */
class DynamicListController extends RestController implements ClassResourceInterface
{
    /**
     * Return dynamic form entries.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $provider = $this->getProvider($request);
        $form = $this->loadForm($request);

        $webspace = $request->get('webspace');
        $uuid = $request->get('uuid');
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit');
        $offset = (($page - 1) * $limit);

        $entries = $provider->loadEntries(
            $form,
            $webspace,
            $uuid,
            $limit,
            $offset
        );

        // avoid total request when entries < limit
        if (count($entries) == $limit) {
            $total = $provider->getTotal(
                $webspace,
                $uuid
            );
        } else {
            // calculate total
            $total = count($entries) + $offset;
        }

        // create list representation
        $representation = new ListRepresentation(
            $entries,
            'entries',
            $request->get('_route'),
            $request->query->all(),
            $page,
            $limit,
            $total
        );

        return $this->handleView($this->view($representation));
    }

    /**
     * Returns the fields for a dynamic form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cgetFieldsAction(Request $request)
    {
        $provider = $this->getProvider($request);
        $form = $this->loadForm($request);
        $locale = $request->getLocale();

        $fieldDescriptors = $provider->getFieldDescriptors($form, $locale);

        return $this->handleView($this->view(array_values($fieldDescriptors)));
    }

    /**
     * Get provider.
     *
     * @param Request $request
     *
     * @return DynamicListProviderInterface
     */
    protected function getProvider(Request $request)
    {
        $providerName = $request->get('provider');

        if (!$providerName) {
            throw new NotFoundHttpException('"provider" is required parameter');
        }

        return $this->get('l91_sulu_form.dynamic_provider.pool')->getProvider($providerName);
    }

    /**
     * Get form.
     *
     * @param Request $request
     *
     * @return Form
     */
    protected function loadForm(Request $request)
    {
        $formId = (int) $request->get('form');

        if (!$formId) {
            throw new NotFoundHttpException('"form" is required parameter');
        }

        return $this->get('l91_sulu_form.repository.form')->findById($formId);
    }
}
