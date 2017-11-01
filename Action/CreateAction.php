<?php

namespace Requestum\ApiBundle\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CreateAction.
 */
class CreateAction extends AbstractFormAction
{
    /**
     * {@inheritdoc}
     */
    protected function provideEntity(Request $request)
    {
        return new $this->entityClass(); // @codingStandardsIgnoreLine
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'success_status_code' => Response::HTTP_CREATED,
            'access_attribute' => 'create',
        ]);
    }
}
