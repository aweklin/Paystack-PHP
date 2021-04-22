<?php

namespace Aweklin\Paystack\Models;

use Aweklin\Paystack\Exceptions\EmptyValueException;
use Aweklin\Paystack\Infrastructures\Utility;

class PaymentTransfer {

    private $_amount;
    private $_recipient;
    private $_reference;

    public function __construct(float $amount, string $recipient, string $reference) {
        $this->_amount = $amount;
        $this->_recipient = $recipient;
        $this->_reference = $reference;
    }

    public function getAmount() : float {
        if (Utility::isEmpty($this->_amount))
            throw new EmptyValueException("Amount");
        if (!\is_numeric($this->_amount))
            throw new \InvalidArgumentException($this->_amount . " is not a valid amount.");

        return \floatval($this->_amount) * 100;
    }

    public function getRecipient() : string {
        if (Utility::isEmpty($this->_recipient))
            throw new EmptyValueException("Recipient");

        return Utility::parseString($this->_recipient);
    }

    public function getReference() : string {
        if (Utility::isEmpty($this->_reference))
            throw new EmptyValueException("Reference");

        return Utility::parseString($this->_reference);
    }

}