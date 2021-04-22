<?php

namespace Aweklin\Paystack\Core;

use Aweklin\Paystack\Abstracts\{IRequest, IResponse};
use Aweklin\Paystack\Concrete\{Request, Response};
use Aweklin\Paystack\ConcreteAbstract\Filterable;
use Aweklin\Paystack\Exceptions\{EmptyValueException};
use Aweklin\Paystack\Infrastructures\Utility;
use Aweklin\Paystack\Models\TransactionParameter;

/**
 * Allows you create and manage transaction refunds.
 */
class Refund extends Filterable {

    /**
     * Initiates a refund.
     * 
     * @param string $reference Transaction reference or id.
     * @param float $amount Amount to be refunded to the customer. Amount is optional(defaults to original transaction amount) and cannot be more than the original transaction amount.
     * @param string $currency Three-letter ISO currency.
     * @param string $customerNote Customer reason.
     * @param string $merchantNote Merchant reason.
     * 
     * @return IResponse
     */
    public function initiate(string $reference, float $amount = 0, string $currency = 'NGN', string $customerNote = '', string $merchantNote = '') : IResponse {
        try {
            if (Utility::isEmpty($reference))
                return new Response(true, 'Transaction ID or reference is required.');
            if ($amount < 0)
                return new Response(true, 'Amount cannot be less than zero.');
            if (!Utility::isEmpty($currency) && \mb_strlen($currency) != 3)
                return new Response(true, 'Invalid currency.');
            
            $body = ['transaction' => Utility::parseString($reference)];
            if ($amount > 0) {
                $body['amount'] = $amount * 100;
            }
            if (!Utility::isEmpty($currency)) {
                $body['currency'] = Utility::parseString($currency);
            }
            if (!Utility::isEmpty($customerNote)) {
                $body['customer_note'] = Utility::parseString($customerNote);
            }
            if (!Utility::isEmpty($merchantNote)) {
                $body['merchant_note'] = Utility::parseString($merchantNote);
            }

            return Request::getInstance()->execute(IRequest::TYPE_POST, 'refund', $body);
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Get details of a refund.
     * 
     * @param string $reference Identifier for transaction to be refunded.
     * 
     * @return IResponse
     */
    public function get(string $reference) : IResponse {
        try {
            if (Utility::isEmpty($reference))
                return new Response(true, 'Transaction ID or reference is required.');
            
            return Request::getInstance()->execute(IRequest::TYPE_GET, "refund/{$reference}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Performs a transaction search based on the parameters specified.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * @param string $customParams Custom parameters to filter transactions with.
     * 
     * @return IResponse
     */
    protected function search(int $pageNumber = 1, int $pageSize = 50, string $customParams = '') : IResponse {
        try {
            $this->normalizePagingParams($pageNumber, $pageSize);
            return Request::getInstance()->execute(IRequest::TYPE_GET, "refund/?perPage={$pageSize}&page={$pageNumber}{$customParams}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }
}