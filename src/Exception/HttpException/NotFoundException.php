<?php

namespace PHPCensor\Exception\HttpException;

use PHPCensor\Exception\HttpException;

class NotFoundException extends HttpException
{
    /**
     * @var int
     */
    protected $errorCode = 404;

    /**
     * @var string
     */
    protected $statusMessage = 'Not Found';
}
