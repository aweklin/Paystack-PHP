<?php

namespace Aweklin\Paystack\ConcreteAbstract;

use Aweklin\Paystack\Core\Transaction;
use Aweklin\Paystack\Exceptions\{EmptyValueException};
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * The base class for filtering transactions, refunds, beneficiaries.
 */
class SearchParameter {
    
    protected $_startDate;
    protected $_endDate;

    public function setStartDate(string $startDate) : TransactionParameter {
        if (Utility::isEmpty($startDate))
            throw new EmptyValueException("Start date");
        if (!Utility::isValidDate($startDate))
            throw new \InvalidArgumentException("The value specified for start date: {$startDate} is invalid. Please specify a timestamp from which to start listing transaction e.g. 2016-09-24T00:00:05.000Z, 2016-09-21.");

        $this->_startDate = Utility::parseString($startDate);

        return $this;
    }

    public function setEndDate(string $endDate) : TransactionParameter {
        if (Utility::isEmpty($endDate))
            throw new EmptyValueException("End date");
        if (!Utility::isValidDate($endDate))
            throw new \InvalidArgumentException("The value specified for end date: {$endDate} is invalid. Please specify a timestamp at which to stop listing transaction e.g. 2016-09-24T00:00:05.000Z, 2016-09-21.");

        $this->_endDate = Utility::parseString($endDate);

        return $this;
    }

    /**
     * Returns the start and end dates parameters.
     */
    public function get() : array {
        $parameters = [];

        if (!Utility::isEmpty($this->_startDate))
            $parameters[Transaction::FILTER_PARAM_START_DATE] = $this->_startDate;
        if (!Utility::isEmpty($this->_endDate))
            $parameters[Transaction::FILTER_PARAM_END_DATE] = $this->_endDate;

        if (empty($parameters))
            throw new \Exception("One or more parameters is required.");
        
        return $parameters;
    }

}