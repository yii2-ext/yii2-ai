<?php

declare(strict_types=1);

namespace proweb\ai\tests\Support;

use AIAccess\Http\Client;
use AIAccess\Http\FormData;
use AIAccess\Http\Response;
use Closure;
use RuntimeException;

/**
 * Records requests and replays queued responses; feeds canned SSE chunks to streaming requests.
 */
final class MockHttpClient implements Client
{
    /**
     * @var list<array{url: string, payload: string|array<array-key, mixed>|FormData|null, headers: array<string, string>, method: string|null}>
     */
    private array $requests = [];

    /**
     * @var list<array{status: int, data: mixed}>
     */
    private array $responses = [];

    /**
     * @var string[]
     */
    private array $streamChunks = [];

    public function fetch(
        string $url,
        string|array|FormData|null $payload = null,
        array $headers = [],
        string|null $method = null,
        Closure|null $onChunk = null,
    ): Response {
        $this->requests[] = [
            'url' => $url,
            'payload' => $payload,
            'headers' => $headers,
            'method' => $method,
        ];

        if ($onChunk !== null) {
            foreach ($this->streamChunks as $chunk) {
                $onChunk($chunk);
            }
            $this->streamChunks = [];

            return new Response(200, [], null);
        }

        $next = array_shift($this->responses) ?? ['status' => 200, 'data' => []];

        return new Response($next['status'], [], $next['data']);
    }

    public function lastHeader(string $name): string|null
    {
        $request = $this->lastRequest();
        if ($request === null) {
            return null;
        }

        return $request['headers'][$name] ?? null;
    }

    /**
     * The payload of the last request, which must be a JSON object (string-keyed array).
     *
     * @return array<array-key, mixed>
     */
    public function lastJsonPayload(): array
    {
        $request = $this->lastRequest();
        if ($request === null || !is_array($request['payload'])) {
            throw new RuntimeException('The last request has no array payload.');
        }

        return $request['payload'];
    }

    /**
     * @return array{url: string, payload: string|array<array-key, mixed>|FormData|null, headers: array<string, string>, method: string|null}|null
     */
    public function lastRequest(): array|null
    {
        if ($this->requests === []) {
            return null;
        }

        return end($this->requests);
    }

    /**
     * Queues a canned JSON response for the next non-streaming request.
     */
    public function queueResponse(int $status, mixed $data): void
    {
        $this->responses[] = ['status' => $status, 'data' => $data];
    }

    /**
     * Queues raw SSE chunks for the next streaming request.
     */
    public function queueStream(string ...$chunks): void
    {
        $this->streamChunks = array_merge($this->streamChunks, $chunks);
    }

    /**
     * @return list<array{url: string, payload: string|array<array-key, mixed>|FormData|null, headers: array<string, string>, method: string|null}>
     */
    public function requests(): array
    {
        return $this->requests;
    }
}
