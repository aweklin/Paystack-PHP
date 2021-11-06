<?php

namespace Aweklin\Paystack\Models;

use Aweklin\Paystack\ConcreteAbstract\SearchParameter;
use Aweklin\Paystack\Core\Transaction;
use Aweklin\Paystack\Exceptions\{EmptyValueException};
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * Used to specify fields to use when filtering/searching refunds.
 */
class RefundParameter extends SearchParameter {

    private $_status;
    private $_customerId;
    private $_amount;    
    private $_currency;

    public function setCurrency(string $currency) : RefundParameter {
        if (Utility::isEmpty($currency))
            throw new EmptyValueException("Currency");
        if (\mb_strlen($currency) != 3)
            throw new \InvalidArgumentException("Three-letter ISO currency is required.");

        $this->_currency = Utility::parseString($currency);

        return $this;
    }

    public function get() : array {
        $parameters = parent::get();

        if (!Utility::isEmpty($this->_currency))
            $parameters[Transaction::FILTER_PARAM_CURRENCY] = $this->_currency;

        if (empty($parameters))
            throw new \Exception("One or more parameters is required.");
        
        return $parameters;
    }

}