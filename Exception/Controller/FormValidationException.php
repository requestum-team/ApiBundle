<?php

namespace Requestum\ApiBundle\Exception\Controller;

use Symfony\Component\Form\FormError;

/**
 * Class FormValidationException
 */
class FormValidationException extends \RuntimeException
{
    protected $errors;

    /**
     * FormValidationException constructor.
     *
     * @param FormError $error
     * @param string    $path
     */
    public function __construct($error, $path = null)
    {
        if (count(func_get_args()) === 1 && is_array($error)) {
            $this->errors = $error;
        } else {
            if ($path) {
                $this->errors[$path] = $error;
            } else {
                $this->errors[] = $error;
            }
        }

        parent::__construct('Form controller validation exception');
    }

    /**
     * @return FormError
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
