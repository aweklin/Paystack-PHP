<?php

namespace Aweklin\Paystack\Models;

use Aweklin\Paystack\Abstracts\IResponse;
use Aweklin\Paystack\Concrete\Response;
use Aweklin\Paystack\ConcreteAbstract\PaymentMethod;
use Aweklin\Paystack\Exceptions\EmptyValueException;
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * The QR option generates a QR code which allows customers to use their bank's mobile app to complete payments. We currently have only Visa QR option available. We'll have more options later.
 *
 * When the customer scans the code, they authenticate on their bank app to complete the payment. When the user pays, a response will be sent to your webhook. This means that you need to have webhooks set up on your Paystack Dashboard.
 */
class QRCodePayment extends PaymentMethod {

    const PROVIDER_VISA = 'visa';

    private $_provider;

    public function __construct(string $email, float $amount, string $provider) {
        parent::__construct($email, $amount);

        $this->_provider = $provider;
    }

    public function getProvider() : string {
        if (Utility::isEmpty($this->_provider))
            return '';
        
        return Utility::parseString($this->_provider);
    }

    public function validate() : IResponse {
        $preValidationResult = $this->preValidate();
        if ($preValidationResult->hasError())
            return $preValidationResult;
        if (!$this->getProvider())
            return new Response(true, 'Provider is required. Simply call one of the constants `QRCodePayment::PROVIDER_...` for the available options.');
        
        return new Response(false, 'OK');
    }

}