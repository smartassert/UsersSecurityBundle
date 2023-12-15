<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Security;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use SmartAssert\UsersSecurityBundle\Exception\HttpClientException;
use SmartAssert\UsersSecurityBundle\Exception\HttpResponseException;
use SmartAssert\UsersSecurityBundle\Exception\UserIdMissingException;

readonly class ApiTokenVerifier
{
    public function __construct(
        private string $baseUrl,
        private ClientInterface $httpClient,
    ) {
    }

    /**
     * @return non-empty-string
     *
     * @throws HttpClientException
     * @throws HttpResponseException
     * @throws UserIdMissingException
     */
    public function verify(string $apiKey): ?string
    {
        $request = new Request(
            'GET',
            $this->baseUrl . '/api-token/verify',
            [
                'authorization' => 'Bearer ' . $apiKey,
            ]
        );

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new HttpClientException($e);
        }

        if (401 === $response->getStatusCode()) {
            return null;
        }

        if (200 !== $response->getStatusCode()) {
            throw new HttpResponseException($response, HttpResponseException::TYPE_NON_SUCCESS);
        }

        if ('application/json' !== $response->getHeaderLine('content-type')) {
            throw new HttpResponseException($response, HttpResponseException::TYPE_INCORRECT_CONTENT_TYPE);
        }

        $userData = json_decode($response->getBody()->getContents(), true);
        if (!is_array($userData)) {
            throw new HttpResponseException($response, HttpResponseException::TYPE_INCORRECT_CONTENT_FORMAT);
        }

        $id = $userData['id'] ?? null;
        $id = is_string($id) ? trim($id) : null;
        if ('' === $id || null === $id) {
            throw new UserIdMissingException($userData);
        }

        return $id;
    }
}
