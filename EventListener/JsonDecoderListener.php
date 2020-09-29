<?php

namespace Requestum\ApiBundle\EventListener;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * JsonDecoderListener.
 */
class JsonDecoderListener
{
    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if ($request->headers->has('Content-Type')) {
            $contentType = $request->headers->get('Content-Type');

            if (substr_count($contentType, 'application/json') > 0) {
                $data = json_decode($request->getContent(), true);

                if (is_array($data)) {
                    $request->request = new ParameterBag($data);
                }
            }
        }
    }
}
