<?php

namespace Requestum\ApiBundle\Action;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FetchAction.
 */
class FetchAction extends EntityAction
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function executeAction(Request $request)
    {
        $user = $this->getEntity($request, $this->options['fetch_field']);
        $this->checkAccess($user);

        return $this->handleResponse(
            $user,
            Response::HTTP_OK
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'access_attribute' => 'fetch',
        ]);
    }
}
