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
     * @param FormError|FormError[] $error
     * @param string|null           $path
     */
    public function __construct($error, $path = null)
    {
        if (count(func_get_args()) === 1 && is_array($error)) {
            $this->errors = $error;
        } else {
            if ($path) {
                $this->errors[$path] = is_array($error) ? $error : [$error];
            } else {
                $this->errors[0][] = $error;
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

    /**
     * @return FormError
     */
    public function getError()
    {
        reset($this->errors);

        return current($this->errors);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        reset($this->errors);
        $key = key($this->errors);

        return is_string($key) ? $key : null;
    }
}
