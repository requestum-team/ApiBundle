<?php

namespace Requestum\ApiBundle\Serializer\Normalizer;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Requestum\ApiBundle\Util\ErrorFactory;

/**
 * Class SymfonyFormErrorNormalizer
 *
 * @package Requestum\ApiBundle\Serializer\Normalizer
 */
class SymfonyFormErrorNormalizer implements NormalizerInterface
{
    /**
     * @var ErrorFactory
     */
    private $errorFactory;

    /**
     * SymfonyFormErrorNormalizer constructor.
     *
     * @param ErrorFactory $errorFactory
     */
    public function __construct(ErrorFactory $errorFactory)
    {
        $this->errorFactory = $errorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $errors = [];

        if ($object instanceof FormErrorIterator) {
            foreach ($object->getChildren() as $key => $error) {
                $errors[] = $this->convertFormToArray($error);
            }

            return $errors;
        } else {
            $errors = $this->convertFormToArray($object);
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof FormInterface;
    }

    // region Processing form errors

    /**
     * @param FormInterface $data
     *
     * @return array
     */
    private function convertFormToArray(FormInterface $data)
    {
        $form = $errors = [];

        foreach ($data->getErrors() as $error) {
            $errors[] = $this->getErrorMessage($error);
        }

        if ($errors) {
            $form['errors'] = $errors;
        }

        $children = [];
        foreach ($data->all() as $child) {
            if ($child instanceof FormInterface) {
                $subErrors = $this->convertFormToArray($child);
                if (count($subErrors)) {
                    $children[$child->getName()] = $subErrors;
                }
            }
        }

        if ($children) {
            $form['fields'] = $children;
        }

        return $form;
    }

    /**
     * @param FormError $error
     *
     * @return string
     */
    private function getErrorMessage(FormError $error)
    {
        return $this->errorFactory->formatError($error);
    }
}