<?php

namespace Requestum\ApiBundle\Action\Extension;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Interface ValidationExtensionInterface
 */
interface ValidationExtensionInterface
{
    /**
     * @param Request $request
     * @param FormInterface $form
     */
    public function validate(Request $request, ValidatorInterface $validator, FormInterface $form);
}