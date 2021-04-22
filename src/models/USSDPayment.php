<?php

namespace Aweklin\Paystack\Models;

use Aweklin\Paystack\Abstracts\IResponse;
use Aweklin\Paystack\Concrete\Response;
use Aweklin\Paystack\ConcreteAbstract\PaymentMethod;
use Aweklin\Paystack\Exceptions\EmptyValueException;
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * This Payment method is specifically for Nigerian customers. Nigerian Banks provide USSD services that customers use to perform transactions, and we've integrated with some of them to enable customers complete payments.
 * 
 * The Pay via USSD channel allows your Nigerian customers to pay you by dialling a USSD code on their mobile device. This code is usually in the form of * followed by some code and ending with #. The user is prompted to authenticate the transaction with a PIN and then it is confirmed.
 */
class USSDPayment extends PaymentMethod {

    const BANK_GUARANTEE_TRUST = '737';
    const BANK_UNITED_BANK_FOR_AFRICA = '919';
    const BANK_STERLING = '822';
    const BANK_ZENITH = '966';

    private $_type;

    public function __construct(string $email, float $amount, string $type) {
        parent::__construct($email, $amount);

        $this->_type = $type;
    }

    public function getType() : string {
        if (Utility::isEmpty($this->_type))
            return '';
        
        return Utility::parseString($this->_type);
    }

    public function validate() : IResponse {
        $preValidationResult = $this->preValidate();
        if ($preValidationResult->hasError())
            return $preValidationResult;
        if (!$this->getType())
            return new Response(true, 'USSD type is required. Simply call one of the constants `USSDPayment::BANK_...` for the available options.');
        
        return new Response(false, 'OK');
    }

}