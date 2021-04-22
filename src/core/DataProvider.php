<?php

namespace Aweklin\Paystack\Core;

use Aweklin\Paystack\Abstracts\{IRequest, IResponse};
use Aweklin\Paystack\Concrete\{Request, Response};
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * Use to get list of data (such as Banks, Providers), and verifying identities (such as verify BVN, obtain BVN details, obtain account details).
 */
class DataProvider {

    public function getBalance() : IResponse {
        try {
            return Request::getInstance()->execute(IRequest::TYPE_GET, 'balance');
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Get a list of all Nigerian banks and their properties
     * 
     * @return IResponse
     */
    public function getBanks() : IResponse {
        try {
            return Request::getInstance()->execute(IRequest::TYPE_GET, 'bank');
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Get a list of all providers for Dedicated NUBAN
     * 
     * @return IResponse
     */
    public function getProviders() : IResponse {
        try {
            return Request::getInstance()->execute(IRequest::TYPE_GET, 'bank/?pay_with_bank_transfer=true');
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Validates the given Bank Verification Number, BVN against the account number and bank code provided.
     * 
     * @param string $bvn The 11 digits BVN being verified.
     * @param string $bankCode Bank code available by calling the `getBanks()` method.
     * @param string $accountNumber Bank Account Number.
     * 
     * @return IResponse
     */
    public function verifyBVN(string $bvn, string $bankCode, string $accountNumber) : IResponse {
        try {
            // validate
            if (Utility::isEmpty($bvn))
                return new Response(true, 'BVN is required.');
            if (Utility::isEmpty($bankCode))
                return new Response(true, 'Bank is required.');
            if (Utility::isEmpty($accountNumber))
                return new Response(true, 'Bank account number is required.');

            // verify bvn
            $body = [
                'bvn' => $bvn,
                'account_number' => $accountNumber,
                'bank_code' => $bankCode
            ];
            return Request::getInstance()->execute(IRequest::TYPE_POST, 'bvn/match', $body);
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Get a customer's information by using the Bank Verification Number.
     * 
     * @param string $bvn The 11 digits BVN being verified.
     * 
     * @return IResponse
     */
    public function getBVNDetails(string $bvn) : IResponse {
        try {
            // validate
            if (Utility::isEmpty($bvn))
                return new Response(true, 'BVN is required.');

            // verify and get bvn details
            return Request::getInstance()->execute(IRequest::TYPE_GET, "bank/resolve_bvn/{$bvn}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Confirm an account belongs to the right customer.
     * 
     * @param string $accountNumber Bank account number being verified.
     * @param string $bankCode Bank code available by calling the `getBanks()` method.
     * 
     * @return IResponse
     */
    public function getAccountDetails(string $accountNumber, string $bankCode) : IResponse {
        try {
            // validate
            if (Utility::isEmpty($accountNumber))
                return new Response(true, 'Account number is required.');
            if (Utility::isEmpty($bankCode))
                return new Response(true, 'Bank code is required.');

            // verify and get bvn details
            return Request::getInstance()->execute(IRequest::TYPE_GET, "bank/resolve?account_number={$accountNumber}&bank_code={$bankCode}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

}