<?php

namespace Gie\Gateway\API\Request;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Response;

interface RequestManagerInterface
{
    public function sendRequest(RequestInterface $request, int $ttl): Response;

    public function sendConcurrentRequests(array $requests, int $ttl, int $nbRequests = 5): array;
}