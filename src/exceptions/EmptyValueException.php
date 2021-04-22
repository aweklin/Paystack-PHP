<?php

namespace Aweklin\Paystack\Exceptions;

/**
 * Exception thrown if the given value is empty.
 */
class EmptyValueException extends \LogicException {

    public function __construct(string $parameterName) {
        parent::__construct("$parameterName is required.");
    }

}