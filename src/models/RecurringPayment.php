<?php

namespace Aweklin\Paystack\Models;

use Aweklin\Paystack\Abstracts\IResponse;
use Aweklin\Paystack\Concrete\Response;
use Aweklin\Paystack\ConcreteAbstract\PaymentMethod;
use Aweklin\Paystack\Exceptions\EmptyValueException;
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * Allows you to process a recurring payment by charging the authorization code earlier received.
 */
class RecurringPayment extends PaymentMethod {

    private $_authorizationCode;

    public function __construct(string $email, float $amount, string $authorizationCode) {
        parent::__construct($email, $amount);

        $this->_authorizationCode = $authorizationCode;
    }

    public function getAuthorizationCode() : string {
        if (Utility::isEmpty($this->_authorizationCode))
            return '';
        
        return Utility::parseString($this->_authorizationCode);
    }

    public function validate() : IResponse {
        $preValidationResult = $this->preValidate();
        if ($preValidationResult->hasError())
            return $preValidationResult;
        if (!$this->getAuthorizationCode())
            return new Response(true, 'Authorization code is required.');
        
        return new Response(false, 'OK');
    }

}