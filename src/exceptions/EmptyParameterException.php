<?php

namespace Aweklin\Paystack\Exceptions;

/**
 * Exception thrown if the specified parameter is empty.
 */
class EmptyParameterException extends \LogicException {

    public function __construct() {
        parent::__construct("Parameter name is required.");
    }

}