<?php

declare(strict_types=1);

namespace Geocoder\Laravel\Http;

use Illuminate\Support\Facades\Http;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LaravelHttpClient implements ClientInterface
{
    public function __construct(
        public ?int $timeout = null,
        public ?int $connectTimeout = null,
        public ?array $retry = null,
        public array $options = [],
    ) {}

    // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh,SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $headers = [];

        foreach ($request->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }

        $body = (string) $request->getBody();
        $pending = Http::withHeaders($headers);

        if ($this->timeout !== null) {
            $pending = $pending->timeout($this->timeout);
        }

        if ($this->connectTimeout !== null) {
            $pending = $pending->connectTimeout($this->connectTimeout);
        }

        if ($this->retry !== null) {
            $pending = $pending->retry(...$this->retry);
        }

        if ($this->options !== []) {
            $pending = $pending->withOptions($this->options);
        }

        if ($body !== '') {
            $pending = $pending->withBody(
                $body,
                $request->getHeaderLine('Content-Type') ?: 'application/octet-stream',
            );
        }

        return $pending
            ->send($request->getMethod(), (string) $request->getUri())
            ->toPsrResponse();
    }
}
