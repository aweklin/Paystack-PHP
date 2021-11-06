<?php

namespace Aweklin\Paystack\Models;

use Aweklin\Paystack\ConcreteAbstract\SearchParameter;
use Aweklin\Paystack\Core\Transaction;
use Aweklin\Paystack\Exceptions\{EmptyValueException};
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * Used to specify fields to use when filtering/searching transfers.
 */
class TransferParameter extends SearchParameter {

    private $_customerId;

    public function setCustomerId(int $customerId) : TransferParameter {
        if ($customerId < 1)
            throw new EmptyValueException("Customer Id");

        $this->_customerId = $customerId;

        return $this;
    }

    public function get() : array {
        $parameters = parent::get();

        if (!Utility::isEmpty($this->_customerId))
            $parameters[Transaction::FILTER_PARAM_CUSTOMER_ID] = $this->_customerId;
        
        if (empty($parameters))
            throw new \Exception("One or more parameters is required.");
        
        return $parameters;
    }

}