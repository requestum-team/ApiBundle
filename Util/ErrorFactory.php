<?php

namespace Requestum\ApiBundle\Util;

use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class ErrorFactory.
 */
class ErrorFactory
{
    /**
     * Can accept different count of arguments, where 0 - string or FormError (code), 1 - string (description)
     *
     * @return array|mixed
     */
    public function formatError()
    {
        $args = func_get_args();

        $error = array_shift($args);
        $description = array_shift($args);

        if ($error instanceof FormError) {
            $error = $this->createFromFormError($error);
        } else if (is_string($error) && is_string($description)) {
            $error = [
                'error' => $error,
                'description' => $description,
            ];
        } else {
            throw new \InvalidArgumentException('Expected type FormError or two string parameters');
        }

        return $error;
    }

    /**
     * @param FormError $error
     *
     * @return array
     */
    public function createFromFormError(FormError $error)
    {
        return [
            'error' => $this->formatFormErrorCode($error),
            'description' => $error->getMessage(),
        ];
    }

    /**
     * @param FormError $error
     *
     * @return mixed
     */
    protected function formatFormErrorCode(FormError $error)
    {
        $cause = $error->getCause();

        if ($cause instanceof ConstraintViolation) {
            $errorCode = 'error.constraint';

            if ($cause->getCode()) {
                // use violation code if exists for better errors determination
                try {
                    $code = $cause->getConstraint()->getErrorName($cause->getCode());
                } catch (\InvalidArgumentException $exception) {
                    $code = $cause->getCode();
                }

                $code = strtolower($code);

                $errorCode .= '.'.$code;
            } else {
                // fallback to constraint name, convert violation class name to snake_case
                $reflectionClass = new \ReflectionClass($cause->getConstraint());
                $constraintPart = $this->inflectString($reflectionClass->getShortName());

                $errorCode .= '.'.$constraintPart;
            }
        } else {
            $errorCode = $error->getMessageTemplate();
        }

        return $errorCode;
    }

    protected function inflectString($string)
    {
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $string));
    }
}
