<?php

namespace Requestum\ApiBundle\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ActionContextDecorator
 *
 * @package Requestum\ApiBundle\Action
 */
class ActionContextDecorator extends BaseAction
{
    private $decoratedAction;

    /**
     * ActionContextDecorator constructor.
     *
     * @param BaseAction $decoratedAction
     */
    public function __construct(BaseAction $decoratedAction)
    {
        parent::__construct();

        $this->decoratedAction = $decoratedAction;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function executeAction(Request $request)
    {
        return $this->decoratedAction->executeAction($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'request_attr' => 'parent_id',
        ]);
    }
}
