<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Exception;

use Psr\Http\Message\ResponseInterface;

class HttpResponseException extends \Exception
{
    public const TYPE_NON_SUCCESS = 'non-success-response';
    public const TYPE_INCORRECT_CONTENT_TYPE = 'incorrect-content-type';
    public const TYPE_INCORRECT_CONTENT_FORMAT = 'incorrect-content-format';

    /**
     * @param self::TYPE_* $errorType
     */
    public function __construct(
        public readonly ResponseInterface $response,
        public readonly string $errorType,
    ) {
        parent::__construct($errorType . ': ' . $response->getReasonPhrase(), $response->getStatusCode());
    }
}
