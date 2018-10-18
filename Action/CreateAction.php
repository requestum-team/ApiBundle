<?php

namespace Requestum\ApiBundle\Action;

use Requestum\ApiBundle\Filter\EntityContextData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

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
        $newEntity = new $this->entityClass();

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        /**
         * @var string $field
         * @var EntityContextData $contextData
         */
        foreach ($this->getContexts() as $field => $contextData) {
            $propertyAccessor->setValue($newEntity, $contextData->getField(), $contextData->getValue());
        }

        return $newEntity;
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
