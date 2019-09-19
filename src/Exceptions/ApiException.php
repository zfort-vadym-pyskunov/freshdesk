<?php

namespace KuznetsovZfort\Freshdesk\Exceptions;

use Exception;

class ApiException extends Exception
{
    /**
     * @var string
     */
    private $response;

    /**
     * @param string $message
     * @param int $code
     * @param string $response
     */
    public function __construct($message = '', $code = 0, $response = '')
    {
        parent::__construct($message, $code);

        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Freshdesk API error';
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}
