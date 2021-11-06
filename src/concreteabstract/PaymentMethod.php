<?php

namespace Aweklin\Paystack\ConcreteAbstract;

use Aweklin\Paystack\Abstracts\IResponse;
use Aweklin\Paystack\Infrastructures\Utility;
use Aweklin\Paystack\Concrete\Response;
use Aweklin\Paystack\Exceptions\{EmptyParameterException, EmptyValueException, ParameterExistsException};

/**
 * An abstract class for various payment methods. 
 * Child class must implement the validate method and ensure to call the `$this->preValidate()` method and then check if the preliminary validation succeed before proceeding.
 */
abstract class PaymentMethod {
    
    private $_amount;
    private $_email;
    private $_currency;
    private $_reference;
    private $_customFields;
    
    public function __construct(string $email, float $amount) {
        $this->_email = $email;
        $this->_amount = $amount * 100; // takes care of kobo if currency is NGN, pesewas, if currency is GHS, and cents, if currency is ZAR
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

    public function setCurrency(string $currency) {
        $this->_setCurrency($currency);
    }

    public function setReference(string $reference) {
        $this->_reference = $reference;
    }

    public function getAmount() : float {
        if (Utility::isEmpty($this->_amount) || (!Utility::isEmpty($this->_amount) && !\is_numeric($this->_amount)))
            return 0;
        
        return \floatval($this->_amount);
    }

    public function getEmail() : string {
        if (Utility::isEmpty($this->_email))
            return '';
        
        return Utility::parseString($this->_email);
    }

    public function getCurrency() : string {
        if (Utility::isEmpty($this->_currency))
            return '';

        return Utility::parseString($this->_currency);
    }

    public function getReference() : string {
        if (Utility::isEmpty($this->_reference))
            return '';

        return Utility::parseString($this->_reference);
    }

    public function addCustomField(string $field, string $value) : PaymentMethod {
        if (!$this->_customFields)
            $this->_customFields = [];
        
        if (Utility::isEmpty($field))
            throw new EmptyParameterException();
        if (Utility::isEmpty($value))
            throw new EmptyValueException($field);
        if (\array_key_exists($field, $this->_customFields))
            throw new ParameterExistsException($field);

        array_push($this->_customFields, [$field => $value]);

        return $this;
    }

    public function getCustomFields() {
        return $this->_customFields;
    }

    protected function preValidate() : IResponse {
        if ($this->getAmount() <= 0)
            return new Response(true, 'Amount must be grater than zero.');
        if (!$this->getEmail())
            return new Response(true, 'Email is required.');
        if (!\filter_var($this->getEmail(), \FILTER_VALIDATE_EMAIL))
            return new Response(true, 'Incorrect email address format.');

        return new Response(false, '');
    }

    public abstract function validate() : IResponse;

}