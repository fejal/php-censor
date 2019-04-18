<?php

namespace PHPCensor\Exception\HttpException;

use PHPCensor\Exception\HttpException;

class NotAuthorizedException extends HttpException
{
    /**
     * @var int
     */
    protected $errorCode = 401;

    /**
     * @var string
     */
    protected $statusMessage = 'Not Authorized';
}
