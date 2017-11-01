<?php

namespace Requestum\ApiBundle\Exception\Controller;

use Symfony\Component\Form\FormError;

/**
 * Class FormValidationException
 */
class FormValidationException extends \RuntimeException
{
    /**
     * @var FormError
     */
    protected $error;

    /**
     * @var null|string
     */
    protected $path;

    /**
     * FormValidationException constructor.
     *
     * @param FormError $error
     * @param string    $path
     */
    public function __construct(FormError $error, $path = null)
    {
        $this->error = $error;
        $this->path = $path;

        parent::__construct('Form controller validation exception');
    }

    /**
     * @return FormError
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
