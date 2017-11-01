<?php

namespace Requestum\ApiBundle\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UpdateAction.
 */
class UpdateAction extends AbstractFormAction
{
    /**
     * {@inheritdoc}
     */
    protected function provideEntity(Request $request)
    {
        return $this->getEntity($request, null, $this->options['use_lock']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'http_method' => Request::METHOD_PATCH,
            'access_attribute' => 'update',
        ]);
    }
}
