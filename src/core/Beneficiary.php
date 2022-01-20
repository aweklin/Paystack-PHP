<?php

namespace Aweklin\Paystack\Core;

use Aweklin\Paystack\Abstracts\{IRequest, IResponse};
use Aweklin\Paystack\Concrete\{Request, Response};
use Aweklin\Paystack\ConcreteAbstract\Filterable;
use Aweklin\Paystack\Core\DataProvider;
use Aweklin\Paystack\Exceptions\{EmptyValueException};
use Aweklin\Paystack\Infrastructures\Utility;
use Aweklin\Paystack\Models\PaymentTransfer;

/**
 * Allows you create and manage beneficiaries that you send money to
 */
class Beneficiary extends Filterable {

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
            return Request::getInstance()->execute(IRequest::TYPE_GET, "transferrecipient/?perPage={$pageSize}&page={$pageNumber}{$customParams}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Get details of a beneficiary.
     * 
     * @param string $id An ID or code for the recipient whose details you want to receive.
     * 
     * @return IResponse
     */
    public function get(string $id) : IResponse {
        try {
            if (Utility::isEmpty($id))
                return new Response(true, 'An ID or code for the recipient whose details you want to receive is required.');
            
            return Request::getInstance()->execute(IRequest::TYPE_GET, "transferrecipient/{$id}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Creates a new recipient. A duplicate account number will lead to the retrieval of the existing record.
     * 
     * @param string $accountNumber Beneficiary's account number.
     * @param string $bankCode Bank code. You can get the list of Bank Codes by calling the `Aweklin\Paystack\Core\DataProvider::getBanks()` method.
     * @param string $accountName A name for the recipient.
     * @param string $description (Optional) A description for this plan.
     * @param string $currency Currency for the account receiving the transfer.
     * 
     * @return IResponse
     */
    public function create(string $accountNumber, string $bankCode, string $accountName, string $description = '', string $currency = 'NGN') : IResponse {
        try {
            // validate
            if (Utility::isEmpty($accountNumber))
                return new Response(true, "Account number is required.");
            if (!\is_numeric($accountNumber))
                return new Response(true, "Invalid account number format.");
            if (Utility::isEmpty($bankCode))
                return new Response(true, "Bank code is required.");
            if (Utility::isEmpty($accountName))
                return new Response(true, "Account name is required.");
            if (!Utility::isEmpty($currency) && \mb_strlen($currency) != 3)
                return new Response(true, 'Invalid currency.');

            // process
            $body = [
                'type' => 'nuban',
                'name' => $accountName,
                'account_number' => $accountNumber,
                'bank_code' => $bankCode                
            ];

            if (!Utility::isEmpty($currency))
                $body['currency'] = $currency;
            if (!Utility::isEmpty($description))
                $body['description'] = $description;

            return Request::getInstance()->execute(IRequest::TYPE_POST, 'transferrecipient', $body);
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Updates an existing recipient. An duplicate account number will lead to the retrieval of the existing record.
     * 
     * @param string $id An ID or code for the recipient whose details you want to receive.
     * @param string $name A name for the recipient.
     * @param string $email Email address of the recipient.
     * @param string $description (Optional) A description for this plan.
     */
    public function update(string $id, string $name, string $email, string $description = '') : IResponse {
        try {
            // validate
            if (Utility::isEmpty($id))
                return new Response(true, "Recipient id or code is required.");
            if (Utility::isEmpty($name))
                return new Response(true, "Recipient name is required.");
            if (Utility::isEmpty($email))
                return new Response(true, "Recipient email is required.");

            // process
            $body = [
                'id_or_code' => $id,
                'name' => $name,
                'email' => $email               
            ];

            if (!Utility::isEmpty($description))
                $body['description'] = $description;

            return Request::getInstance()->execute(IRequest::TYPE_PUT, "transferrecipient/{$id}", $body);
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Deletes a beneficiary.
     * 
     * @param string $id An ID or code for the recipient whose details you want to delete.
     * 
     * @return IResponse
     */
    public function delete(string $id) : IResponse {
        try {
            if (Utility::isEmpty($id))
                return new Response(true, 'An ID or code for the recipient whose details you want to delete is required.');
            
            return Request::getInstance()->execute(IRequest::TYPE_GET, "transferrecipient/{$id}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

}