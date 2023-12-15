<?php

declare(strict_types=1);

namespace SmartAssert\UsersSecurityBundle\Exception;

class HttpClientException extends \Exception
{
    public function __construct(
        public readonly \Throwable $previousException
    ) {
        parent::__construct($previousException->getMessage(), $previousException->getCode(), $previousException);
    }
}
