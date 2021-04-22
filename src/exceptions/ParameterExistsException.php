<?php

namespace Aweklin\Paystack\Exceptions;

/**
 * Exception thrown if the specified parameter already exists in the $_customFields field of the Charge class.
 */
class ParameterExistsException extends \LogicException {

    public function __construct(string $parameterName) {
        parent::__construct("{$parameterName} already specified.");
    }

}