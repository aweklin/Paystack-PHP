<?php

namespace Aweklin\Paystack;

use Aweklin\Paystack\Abstracts\IResponse;
use Aweklin\Paystack\ConcreteAbstract\{PaymentMethod, SearchParameter};
use Aweklin\Paystack\Concrete\{Request, Response};
use Aweklin\Paystack\Core\{Transaction, DataProvider, Refund, Transfer, Beneficiary};
use Aweklin\Paystack\Models\{RecurringPayment, PaymentTransfer, TransactionParameter, RefundParameter};

/**
 * Encapsulates all the Paystack operations implemented in this SDK.
 */
final class Paystack {

    /**
     * Nigeria Naira
     */
    const CURRENCY_NGN = 'NGN';
    
    /**
     * Ghana Cedi
     */
    const CURRENCY_GHS = 'GHS';

    /**
     * US Dollar
     */
    const CURRENCY_USD = 'USD';

    /**
     * Specifies the secrete key to use to make requests to Paystack server.
     */
    private static $_apiKey;

    /**
     * Initializes the Paystack SDK with the given API (secrete) key.
     * 
     * @param string $apiKey A string value, representing the API (secrete) key from your Paystack dashboard or settings page.
     * 
     * @return void
     */
    public static function initialize(string $apiKey, bool $enableLogging = true, bool $enableTelemetry = true) : void {
        self::$_apiKey = $apiKey;

        Request::getInstance()
            ->setApiKey($apiKey)
            ->enableLogging($enableLogging)
            ->enableTelemetry($enableTelemetry);
    }

    //============================== TRANSACTIONS =========================

    /**
     * Initiates a transaction based on the payment method model specified.
     * 
     * @param PaymentMethod $paymentMethod A model class that inherits from the PaymentMethod base class.
     * 
     * @return IResponse
     */
    public static function initiateTransaction(PaymentMethod $paymentMethod) : IResponse {
        $transaction = new Transaction();
        $result = $transaction->initiate($paymentMethod);
        unset($transaction);

        return $result;
    }

    /**
     * Concludes a payment process initiated via bank payment method.
     * 
     * @param string $reference Transaction reference number.
     * @param string $otp One-Time-Password (OTP).
     * 
     * @return IResponse
     */
    public static function completeTransactionWithOTP(string $reference, string $otp) : IResponse {
        $transaction = new Transaction();
        $result = $transaction->sendOTP($reference, $otp);
        unset($transaction);

        return $result;
    }

    /**
     * Verifies transaction after payment has been made using the transaction reference number.
     * 
     * @param string $reference The transaction reference number to verify.
     * 
     * @return IResponse
     */
    public static function verifyTransaction(string $reference) : IResponse {
        $transaction = new Transaction();
        $verificationResult = $transaction->verify($reference);
        unset($transaction);

        return $verificationResult;
    }

    public static function checkAuthorization(RecurringPayment $recurringPayment) : IResponse {
        $transaction = new Transaction();
        $authorizationCheckResult = $transaction->checkAuthorization($recurringPayment);
        unset($transaction);

        return $authorizationCheckResult;
    }

    /**
     * Get details of a transaction.
     * 
     * @param int $id Identifier for transaction to be retrieved.
     * 
     * @return IResponse
     */
    public static function getTransaction(int $id) : IResponse {
        try {
            $transaction = new Transaction();
            $transactionResult = $transaction->get($id);
            unset($transaction);

            return $transactionResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all transactions from inception till date.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getTransactions(int $pageNumber = 1, int $pageSize = 50) {
        try {
            $transaction = new Transaction();
            $transactionList = $transaction->all($pageNumber, $pageSize);
            unset($transaction);

            return $transactionList;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Filters and returns all transactions carried out by status, from inception till date.
     * Simply pass in one of the class constants Transaction::STATUS_... to get the right transaction status to use for the filter.
     * 
     * @param string $status Filter transactions by status ('failed', 'success', 'abandoned'). Please invoke one of the status constants in Transaction::STATUS_...
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getTransactionsByStatus(string $status, int $pageNumber = 1, int $pageSize = 50) {
        try {
            $transaction = new Transaction();
            $transactionList = $transaction->getByStatus($status, $pageNumber, $pageSize);
            unset($transaction);

            return $transactionList;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all transactions carried out by a specific amount, from inception till date.
     * 
     * @param float $amount Specify the amount. Note that this API converts it to its kobo if currency is NGN and pesewas if currency is GHS equivalent.
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getTransactionsByAmount(float $amount, int $pageNumber = 1, int $pageSize = 50) {
        try {
            $transaction = new Transaction();
            $transactionList = $transaction->getByAmount($amount, $pageNumber, $pageSize);
            unset($transaction);

            return $transactionList;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all transactions carried out with a specific customer, from inception till date.
     * 
     * @param int $customerId Specify an ID for the customer whose transactions you want to retrieve.
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getTransactionsByCustomerId(int $customerId, int $pageNumber = 1, int $pageSize = 50) {
        try {
            $transaction = new Transaction();
            $transactionList = $transaction->getByCustomerId($amount, $pageNumber, $pageSize);
            unset($transaction);

            return $transactionList;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all transactions between two dates.
     * 
     * @param string $startDate A timestamp from which to start listing transaction e.g. 2016-09-24T00:00:05.000Z, 2016-09-21.
     * @param string $endDate A timestamp at which to stop listing transaction e.g. 2016-09-24T00:00:05.000Z, 2016-09-21.
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getTransactionsByDates(string $startDate, string $endDate, int $pageNumber = 1, int $pageSize = 50) {
        try {
            $transaction = new Transaction();
            $transactionList = $transaction->getByDates($startDate, $endDate, $pageNumber, $pageSize);
            unset($transaction);

            return $transactionList;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all transactions from inception till date.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getTransactionsBy(TransactionParameter $transactionParameter, int $pageNumber = 1, int $pageSize = 50) {
        try {
            $transaction = new Transaction();
            $transactionList = $transaction->filter($transactionParameter, $pageNumber, $pageSize);
            unset($transaction);

            return $transactionList;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    //============================== STATIC DATA & IDENTITIES =========================

    public static function getBalance() : IResponse {
        $dataProvider = new DataProvider();
        $result = $dataProvider->getBalance();
        unset($dataProvider);

        return $result;
    }

    /**
     * Get a list of all Nigerian banks and their properties
     * 
     * @return IResponse
     */
    public static function getBanks() : IResponse {
        $dataProvider = new DataProvider();
        $result = $dataProvider->getBanks();
        unset($dataProvider);

        return $result;
    }

    /**
     * Get a list of all providers for Dedicated NUBAN
     * 
     * @return IResponse
     */
    public static function getProviders() : IResponse {
        $dataProvider = new DataProvider();
        $result = $dataProvider->getProviders();
        unset($dataProvider);

        return $result;
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
    public static function verifyBVN(string $bvn, string $bankCode, string $accountNumber) : IResponse {
        $dataProvider = new DataProvider();
        $verificationResult = $dataProvider->verifyBVN($bvn, $bankCode, $accountNumber);
        unset($dataProvider);

        return $verificationResult;
    }

    /**
     * Get a customer's information by using the Bank Verification Number.
     * 
     * @param string $bvn The 11 digits BVN being verified.
     * 
     * @return IResponse
     */
    public static function getBVNDetails(string $bvn) : IResponse {
        $dataProvider = new DataProvider();
        $verificationResult = $dataProvider->getBVNDetails($bvn);
        unset($dataProvider);

        return $verificationResult;
    }

    /**
     * Confirm an account belongs to the right customer.
     * 
     * @param string $accountNumber Bank account number being verified.
     * @param string $bankCode Bank code available by calling the `getBanks()` method.
     * 
     * @return IResponse
     */
    public static function getAccountDetails(string $accountNumber, string $bankCode) : IResponse {
        $dataProvider = new DataProvider();
        $verificationResult = $dataProvider->getAccountDetails($accountNumber, $bankCode);
        unset($dataProvider);

        return $verificationResult;
    }

    //============================== REFUNDS =========================

    /**
     * Initiates a refund.
     * 
     * @param string $reference Transaction reference or id
     * @param float $amount Amount to be refunded to the customer. Amount is optional(defaults to original transaction amount) and cannot be more than the original transaction amount.
     * @param string $currency Three-letter ISO currency.
     * @param string $customerNote Customer reason.
     * @param string $merchantNote Merchant reason.
     * 
     * @return IResponse
     */
    public static function initiateRefund(string $reference, float $amount = 0, string $currency = 'NGN', string $customerNote = '', string $merchantNote = '') : IResponse {
        $refund = new Refund();
        $refundResult = $refund->initiate($reference, $amount, $currency, $customerNote, $merchantNote);
        unset($refund);

        return $refundResult;
    }
    /**
     * Get details of a refund.
     * 
     * @param string $reference Identifier for transaction to be refunded.
     * 
     * @return IResponse
     */
    public static function getRefund(string $reference) : IResponse {
        try {
            $refund = new Refund();
            $refundResult = $refund->get($reference);
            unset($refund);

            return $refundResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all refunds from inception till date.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getRefunds(int $pageNumber = 1, int $pageSize = 50) : IResponse {
        try {
            $refund = new Refund();
            $refundList = $refund->all($pageNumber, $pageSize);
            unset($refund);

            return $refundList;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all refunds between two dates.
     * 
     * @param string $startDate A timestamp from which to start listing transaction e.g. 2016-09-24T00:00:05.000Z, 2016-09-21.
     * @param string $endDate A timestamp at which to stop listing transaction e.g. 2016-09-24T00:00:05.000Z, 2016-09-21.
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getRefundsByDates(string $startDate, string $endDate, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        try {
            $refund = new Refund();
            $refundList = $refund->getByDates($startDate, $endDate, $pageNumber, $pageSize);
            unset($refund);

            return $refundList;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all refunds from inception till date.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getRefundsBy(RefundParameter $transactionParameter, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        try {
            $refund = new Refund();
            $refundList = $refund->filter($transactionParameter, $pageNumber, $pageSize);
            unset($refund);

            return $refundList;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    //============================== BENEFICIARIES =========================
    
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
    public static function createBeneficiary(string $accountNumber, string $bankCode, string $accountName, string $description = '', string $currency = 'NGN') : IResponse {
        try {
            $beneficiary = new Beneficiary();
            $beneficiaryCreationResult = $beneficiary->create($accountNumber, $bankCode, $accountName, $description, $currency);
            unset($beneficiary);

            return $beneficiaryCreationResult;
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
    public static function updateBeneficiary(string $id, string $name, string $email, string $description = '') {
        try {
            $beneficiary = new Beneficiary();
            $beneficiaryUpdateResult = $beneficiary->update($id, $name, $email, $description);
            unset($beneficiary);

            return $beneficiaryUpdateResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all beneficiaries created from inception till date.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getBeneficiaries(int $pageNumber = 1, int $pageSize = 50) : IResponse {
        try {
            $beneficiary = new Beneficiary();
            $beneficiaries = $beneficiary->all($pageNumber, $pageSize);
            unset($beneficiary);

            return $beneficiaries;
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
    public static function getBeneficiary(int $id) : IResponse {
        try {
            $beneficiary = new Beneficiary();
            $beneficiaryDetails = $beneficiary->get($id);
            unset($beneficiary);

            return $beneficiaryDetails;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all beneficiaries created between two dates.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getBeneficiariesByDates(string $startDate, string $endDate, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        try {
            $beneficiary = new Beneficiary();
            $beneficiaries = $beneficiary->getByDates($startDate, $endDate, $pageNumber, $pageSize);
            unset($beneficiary);

            return $beneficiaries;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }


    //============================== TRANSFERS =========================
    
    /**
     * Initiates a single transfer request to a beneficiary.
     * 
     * Status of transfer object returned will be pending if OTP is disabled. In the event that an OTP is required, status will read otp.
     * 
     * @param string $transactionCode Transaction code for recipient.
     * @param float $amount Amount to be transferred.
     * @param string $reference If specified, the field should be a unique identifier (in lowercase) for the object. Only -,_ and alphanumeric characters allowed.
     * @param string $currency Three-letter ISO currency.
     * @param string $remark The reason for the transfer.
     * 
     * @return IResponse
     */
    public static function initiateTransferToBeneficiary(string $recipient, float $amount, string $reference = '', string $currency = 'NGN', string $remark = '') : IResponse {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->initiateToRecipient($recipient, $amount, $reference, $currency, $remark);
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Initiates a single transfer request to an account number.
     * 
     * Note that this method will have to validate the account number, create the beneficiary, then transfer to the beneficiary.
     * Status of transfer object returned will be pending if OTP is disabled. In the event that an OTP is required, status will read otp.
     * 
     * @param string $accountNumber Receiver's account number.
     * @param string $accountName Receiver's account name.
     * @param string $bankCode Receiver's bank code. You can get the list of Bank Codes by calling the `Aweklin\Paystack\Core\DataProvider::getBanks()` method.
     * @param float $amount Amount to be transferred.
     * @param string $reference If specified, the field should be a unique identifier (in lowercase) for the object. Only -,_ and alphanumeric characters allowed.
     * @param string $currency Three-letter ISO currency.
     * @param string $remark The reason for the transfer.
     * 
     * @return IResponse
     */
    public static function initiateTransferToAccount(string $accountNumber, string $accountName, string $bankCode, float $amount, string $reference = '', string $currency = 'NGN', string $remark = '') : IResponse  {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->initiateToAccount($accountNumber, $accountName, $bankCode, $amount, $reference, $currency, $remark);
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Initiates a bulk transfer request.
     * 
     * You need to disable the Transfers OTP requirement to use this endpoint.
     * 
     * @param array $transfers An array of `\Aweklin\Paystack\Models\PaymentTransfer`, each containing: amount, recipient, and reference.
     * @param string $currency Three-letter ISO currency.
     * 
     * @return IResponse
     */
    public static function initiateBulkTransfers(array $transfers, string $currency = 'NGN') {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->initiateBulk($transfers, $currency);
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Finalizes an initiated transfer with OTP.
     * 
     * @param string $transferCode Transaction code for recipient.
     * @param string $otp OTP sent to business phone to verify transfer.
     * 
     * @return IResponse
     */
    public static function completeTransferWithOTP(string $transferCode, string $otp) : IResponse {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->finalizeTransferWithOTP($transferCode, $otp);
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all transfers carried out from inception till date.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getTransfers(int $pageNumber = 1, int $pageSize = 50) : IResponse {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->all($pageNumber, $pageSize);
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns all transfers carried out from inception till date.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public static function getTransfersByDates(string $startDate, string $endDate, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->getByDates($startDate, $endDate, $pageNumber, $pageSize);
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Get details of a transfer.
     * 
     * @param string $id The transfer ID or code you want to fetch.
     * 
     * @return IResponse
     */
    public static function getTransfer(string $id) : IResponse {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->get($id);
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Generates a new OTP and sends to customer in the event they are having trouble receiving one.
     * 
     * @param string $transferCode Transaction code for recipient.
     * 
     * @return IResponse
     */
    public static function resendTransferOTP(string $transferCode) : IResponse {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->resendOTP($transferCode);
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * In the event that you want to be able to complete transfers programmatically without use of OTPs, this method helps disable that….with an OTP.
     * Please note that this will send you an OTP and you are to call the `finalizeDisableOTP()` method to conclude operation.
     * 
     * @return IResponse
     */
    public static function disableTransferOTP() : IResponse {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->disableOTP();
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Finalizes the request to disable OTP on your transfers.
     * 
     * @param string $otp OTP sent to business phone to verify disabling OTP requirement.
     * 
     * @return IResponse
     */
    public static function completeDisableTransferOTP(string $otp) : IResponse {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->finalizeDisableOTP($otp);
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * In the event that a customer wants to stop being able to complete transfers programmatically, this endpoint helps turn OTP requirement back on.
     * 
     * @return IResponse
     */
    public static function enableTransferOTP() : IResponse {
        try {
            $transfer = new Transfer();
            $transferResult = $transfer->enableOTP();
            unset($transfer);

            return $transferResult;
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }
}