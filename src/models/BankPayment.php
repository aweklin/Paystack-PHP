<?php

namespace Aweklin\Paystack\Models;

use Aweklin\Paystack\Abstracts\IResponse;
use Aweklin\Paystack\Concrete\Response;
use Aweklin\Paystack\ConcreteAbstract\PaymentMethod;
use Aweklin\Paystack\Exceptions\EmptyValueException;
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * Allows you to carry out a bank transaction either via USSD or by transfer.
 */
class BankPayment extends PaymentMethod {

    private $_code;
    private $_accountNumber;
    private $_birthDate;

    public function __construct(string $email, float $amount, string $code, string $accountNumber, string $birthDate = '') {
        parent::__construct($email, $amount);

        $this->_setCode($code);
        $this->_setAccountNumber($accountNumber);
        $this->_birthDate = $birthDate;
    }

    private function _setCode(string $code) : void {
        if (Utility::isEmpty($code))
            throw new EmptyValueException('Code');

        $this->_code = $code;
    }

    private function _setAccountNumber(string $accountNumber) : void {
        if (Utility::isEmpty($accountNumber))
            throw new EmptyValueException('accountNumber');

        $this->_accountNumber = $accountNumber;
    }

    public function getCode() : string {
        if (Utility::isEmpty($this->_code))
            return '';

        return Utility::parseString($this->_code);
    }

    public function getAccountNumber() : string {
        if (Utility::isEmpty($this->_accountNumber))
            return '';

        return Utility::parseString($this->_accountNumber);
    }

    public function getBirthDate() : string {
        if (Utility::isEmpty($this->_birthDate))
            return '';
        if (!Utility::isValidDate($this->_birthDate))
            throw new \InvalidArgumentException($this->_birthDate . ' is not a valid date.');

        return Utility::parseString($this->_birthDate);
    }

    public function validate() : IResponse {
        $preValidationResult = $this->preValidate();
        if ($preValidationResult->hasError())
            return $preValidationResult;
        if (!$this->getCode())
            return new Response(true, 'Bank code is required.');
        if (!$this->getAccountNumber())
            return new Response(true, 'Account number is required.');

        return new Response(false, 'OK');
    }

}