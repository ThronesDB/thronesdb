<?php

namespace App\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class CoreExceptionListener
{
    /**
     * Handles security related exceptions.
     *
     * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return;
        }
        $statusCode = $exception->getCode();
        if (!array_key_exists($statusCode, Response::$statusTexts)) {
            $statusCode = 500;
        }
        $content = [
            'success' => false,
            'message' => $exception->getMessage()
        ];
        $response = new JsonResponse($content, $statusCode, array('Content-Type' => 'application/json'));
        $event->setResponse($response);
    }
}
