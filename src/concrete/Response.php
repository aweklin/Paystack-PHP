<?php

namespace Aweklin\Paystack\Concrete;

use Aweklin\Paystack\Abstracts\IResponse;

/**
 * Abstracts the implementation of network call response/result gotten from Paystack server.
 */
class Response implements IResponse {
    
    /**
     * Stores a value indicating wether or not the response has an error.
     */
    private $_hasError = false;

    /**
     * Stores the response message.
     */
    private $_message = '';

    /**
     * Stores the result data.
     */
    private $_data = [];

    /**
     * Returns true if the request made contains or results in an error.
     * 
     * @return bool
     */
    function hasError(): bool {
        return $this->_hasError;
    }

    /**
     * Returns the response message obtained from the request.
     * 
     * @return string
     */
    function getMessage(): string {
        return $this->_message;
    }

    /**
     * Returns the response data obtained from the request.
     * 
     * @return array
     */
    function getData(): array {
        return $this->_data;
    }

    /**
     * Creates and returns a new instance of the Response object.
     * 
     * @param bool $hasError Specifies the status of the response being constructed.
     * @param string $message Specifies the message of the response being constructed.
     * @param array $data Specifies the data of the response being constructed. The default value is empty array.
     */
    public function __construct(bool $hasError, string $message, array $data = []) {
        $this->_hasError = $hasError;
        $this->_message = $message;
        $this->_data = $data;
    }

}