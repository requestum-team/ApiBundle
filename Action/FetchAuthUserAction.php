<?php

namespace Requestum\ApiBundle\Action;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class FetchAuthUserAction
 *
 * @package Requestum\ApiBundle\Action
 */
class FetchAuthUserAction extends FetchAction
{
    /**
     * {@inheritdoc}
     */
    protected function getEntity(Request $request, $param = 'id', $useLock = false)
    {
        return $this->getUser();
    }
}