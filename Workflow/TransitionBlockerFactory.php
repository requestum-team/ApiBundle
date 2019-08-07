<?php

namespace Requestum\ApiBundle\Workflow;

use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Workflow\TransitionBlocker;

/**
 * Class TransitionBlockerFactory
 */
class TransitionBlockerFactory
{
    /** @var TranslatorInterface  */
    protected $translator;

    /**
     * TransitionBlockerBuilder constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param $code
     * @param $message
     * @param array $parameters
     * @param $plural
     * @param $translationDomain
     * @return TransitionBlocker
     */
    public function createTransitionBlocker(
        $code,
        $message,
        $parameters = [],
        $plural = null,
        $translationDomain = null
    ) {
        if (null === $plural) {
            $translatedMessage = $this->translator->trans(
                $message,
                $parameters,
                $translationDomain
            );
        } else {
            try {
                $translatedMessage = $this->translator->transChoice(
                    $message,
                    $plural,
                    $parameters,
                    $translationDomain
                );
            } catch (InvalidArgumentException $e) {
                $translatedMessage = $this->translator->trans(
                    $message,
                    $parameters,
                    $translationDomain
                );
            }
        }

        return new TransitionBlocker($translatedMessage, $code, $parameters);
    }
}
