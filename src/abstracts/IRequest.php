<?php

namespace Aweklin\Paystack\Abstracts;

use Aweklin\Paystack\Abstracts\IResponse;

/**
 * Encapsulates methods used to make network requests.
 */
interface IRequest {

    /**
     * Indicates a GET request method.
     */
    const TYPE_GET = 'get';

    /**
     * Indicates a POST request method
     */
    const TYPE_POST = 'post';

    /**
     * Executes the given request on Paystack server and returns a value, indicating the status, message and data gotten from the request.
     * 
     * @param string $type Indicates if the request is a get or post. Please use the constant value in IRequest::TYPE_... 
     * @param string $endpoint The endpoint to initiate on Paystack server. No need to specify the full URL for this parameter.
     * @param array $body Default is empty array and it is required when making a post request. Note that the value must be specified as associative array.
     * 
     * @return IResponse
     */
    function execute(string $type, string $url, array $parameters = []) : IResponse;

}