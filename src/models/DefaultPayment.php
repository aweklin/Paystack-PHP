<?php

namespace Aweklin\Paystack\Models;

use Aweklin\Paystack\Abstracts\IResponse;
use Aweklin\Paystack\Concrete\Response;
use Aweklin\Paystack\ConcreteAbstract\PaymentMethod;

class DefaultPayment extends PaymentMethod {

    public function __construct(string $email, float $amount) {
        parent::__construct($email, $amount);
    }

    public function validate(): IResponse {
        $preValidationResult = $this->preValidate();
        if ($preValidationResult->hasError())
            return $preValidationResult;
        
        return new Response(false, 'OK');
    }

}