<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CorsListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $allowedOrigins = [
            'http://localhost:4200',
            'https://gestiondestache.vercel.app',
        ];

        $origin = $request->headers->get('Origin');

        if ($origin && in_array($origin, $allowedOrigins, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        $response->headers->set(
            'Access-Control-Allow-Methods',
            'GET, POST, PUT, DELETE, OPTIONS'
        );

        $response->headers->set(
            'Access-Control-Allow-Headers',
            'Content-Type, Authorization'
        );

        $response->headers->set(
            'Access-Control-Allow-Credentials',
            'true'
        );
    }
}
