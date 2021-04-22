<?php

namespace Aweklin\Paystack\Models;

use Aweklin\Paystack\ConcreteAbstract\SearchParameter;
use Aweklin\Paystack\Core\Transaction;
use Aweklin\Paystack\Exceptions\{EmptyValueException};
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * Used to specify fields to use when filtering/searching transactions.
 */
class TransactionParameter extends SearchParameter {

    private $_status;
    private $_customerId;
    private $_amount;    
    private $_currency;

    public function setStatus(string $status) : TransactionParameter {
        if (Utility::isEmpty($status))
            throw new EmptyValueException("Status");

        $this->_status = Utility::parseString($status);

        return $this;
    }

    public function setCustomerId(int $customerId) : TransactionParameter {
        if ($customerId < 1)
            throw new EmptyValueException("Customer Id");

        $this->_customerId = $customerId;

        return $this;
    }

    public function setAmount(float $amount) : TransactionParameter {
        if ($amount < 1)
            throw new EmptyValueException("Amount");

        $this->_amount = $amount * 100;

        return $this;
    }

    public function setCurrency(string $currency) : TransactionParameter {
        if (Utility::isEmpty($currency))
            throw new EmptyValueException("Currency");
        if (\mb_strlen($currency) != 3)
            throw new \InvalidArgumentException("Three-letter ISO currency is required.");

        $this->_currency = Utility::parseString($currency);

        return $this;
    }

    public function get() : array {
        $parameters = parent::get();

        if (!Utility::isEmpty($this->_status))
            $parameters[Transaction::FILTER_PARAM_STATUS] = $this->_status;
        if (!Utility::isEmpty($this->_customerId))
            $parameters[Transaction::FILTER_PARAM_CUSTOMER_ID] = $this->_customerId;
        if (!Utility::isEmpty($this->_amount))
            $parameters[Transaction::FILTER_PARAM_AMOUNT] = $this->_amount;

        if (empty($parameters))
            throw new \Exception("One or more parameters is required.");
        
        return $parameters;
    }

}