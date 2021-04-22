<?php

namespace Aweklin\Paystack\Models;

use Aweklin\Paystack\Abstracts\IResponse;
use Aweklin\Paystack\Concrete\Response;
use Aweklin\Paystack\ConcreteAbstract\PaymentMethod;
use Aweklin\Paystack\Exceptions\EmptyValueException;
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * Allows you to carry out a mobile money transaction based on available providers.
 */
class MobileMoneyPayment extends PaymentMethod {

    const PROVIDER_MTN = 'mtn';
    const PROVIDER_VODAFONE = 'vod';
    const PROVIDER_AIRTEL_TIGO = 'tgo';

    private $_currency;
    private $_phone;
    private $_provider;

    public function __construct(string $email, float $amount, string $currency, string $phone, string $provider) {
        parent::__construct($email, $amount);

        $this->_setCurrency($currency);
        $this->_setPhone($phone);
        $this->_setProvider($provider);
    }

    private function _setCurrency(string $currency) : void {
        if (Utility::isEmpty($currency))
            throw new EmptyValueException('Currency');
        if (\mb_strlen($currency) != 3)
            throw new \OutOfRangeException("Currency must be a 3 character letters.");
        if (Utility::containsNumber($currency))
            throw new \InvalidArgumentException("Currency cannot contain a number.");
        if (!Utility::isAlphabetOnly($currency))
            throw new \InvalidArgumentException("Currency is expected to be only alphabet.");

        $this->_currency = $currency;
    }

    private function _setPhone(string $phone) : void {
        if (Utility::isEmpty($phone))
            throw new EmptyValueException('Phone');

        $this->_phone = $phone;
    }

    private function _setProvider(string $provider) : void {
        if (Utility::isEmpty($provider))
            throw new EmptyValueException('Provider');

        $this->_provider = $provider;
    }

    public function getCurrency() : string {
        if (Utility::isEmpty($this->_currency))
            return '';

        return Utility::parseString($this->_currency);
    }

    public function getPhone() : string {
        if (Utility::isEmpty($this->_phone))
            return '';

        return Utility::parseString($this->_phone);
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
        if (!$this->getCurrency())
            return new Response(true, 'Currency is required.');
        if (!$this->getPhone())
            return new Response(true, 'Phone number is required.');
        if (!$this->getProvider())
            return new Response(true, 'Provider is required.');

        return new Response(false, 'OK');
    }

}