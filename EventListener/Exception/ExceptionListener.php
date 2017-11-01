<?php

namespace Requestum\ApiBundle\EventListener\Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as BaseExceptionListener;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * ExceptionListener.
 */
class ExceptionListener extends BaseExceptionListener
{
    /**
     * @var string
     */
    private $environment;

    /**
     * Prepare response for exception.
     *
     * @param GetResponseForExceptionEvent $event event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $this->logException(
            $exception,
            sprintf(
                'Uncaught PHP Exception %s: "%s" at %s line %s',
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            )
        );
        if ($exception instanceof HttpExceptionInterface) {
            $message = $event->getException()->getMessage();
        } else {
            if ('prod' === $this->environment) {
                $message = 'Internal Server Error';
            } else {
                $message = $exception->getMessage();
            }
        }

        if ($message) {
            $response = new JsonResponse(['message' => $message]);
            $response->setEncodingOptions(JSON_UNESCAPED_SLASHES);
        } else {
            $response = new Response();
        }

        $event->setResponse($response);
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }
}