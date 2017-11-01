<?php

namespace Requestum\ApiBundle\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ActionInterface
{
    /**
     * @param Request $request
     * @return Response
     */
    public function executeAction(Request $request);
}