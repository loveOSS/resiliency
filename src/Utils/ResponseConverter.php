<?php

namespace Resiliency\Utils;

use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyResponseInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;

/**
 * Helper to convert SymfonyHttpClient Responses to PSR-7 Responses.
 */
final class ResponseConverter
{
    public static function convertToPsr7(SymfonyResponseInterface $response): ResponseInterface
    {
        return new Response();
    }
}