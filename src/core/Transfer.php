<?php

namespace Aweklin\Paystack\Core;

use Aweklin\Paystack\Abstracts\{IRequest, IResponse};
use Aweklin\Paystack\Concrete\{Request, Response};
use Aweklin\Paystack\ConcreteAbstract\Filterable;
use Aweklin\Paystack\Core\{DataProvider, Beneficiary};
use Aweklin\Paystack\Exceptions\{EmptyValueException};
use Aweklin\Paystack\Infrastructures\Utility;
use Aweklin\Paystack\Models\{PaymentTransfer, TransferParameter};

/**
 * Allows you send money.
 */
class Transfer extends Filterable {

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
    public function initiateToAccount(string $accountNumber, string $accountName, string $bankCode, float $amount, string $reference = '', string $currency = 'NGN', string $remark = '') : IResponse {
        try {
            // validations
            if (Utility::isEmpty($accountNumber))
                return new Response(true, 'Account number is required.');
            if (Utility::isEmpty($bankCode))
                return new Response(true, 'Bank code is required.');
            if ($amount <= 0)
                return new Response(true, 'Amount must be grater than zero.');
            if (!Utility::isEmpty($currency) && \mb_strlen($currency) != 3)
                return new Response(true, 'Invalid currency.');
            
            $dataProvider = new DataProvider();
            $accountNumberVerificationResult = $dataProvider->getAccountDetails($accountNumber, $bankCode);
            unset($dataProvider);
            if ($accountNumberVerificationResult->hasError())
                return $accountNumberVerificationResult;
                
            $beneficiary = new Beneficiary();
            $beneficiaryCreationResult = $beneficiary->create($accountNumber, $bankCode, $accountName, $accountName, $currency);
            unset($beneficiary);
            if ($beneficiaryCreationResult->hasError())
                return $beneficiaryCreationResult;
            
            $recipient = $beneficiaryCreationResult->getData()['id'];

            return $this->initiateToRecipient($recipient, $amount, $reference, $currency, $remark);
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Initiates a single transfer request to a beneficiary.
     * 
     * Status of transfer object returned will be pending if OTP is disabled. In the event that an OTP is required, status will read otp.
     * 
     * @param string $recipient Transaction code for recipient.
     * @param float $amount Amount to be transferred.
     * @param string $reference If specified, the field should be a unique identifier (in lowercase) for the object. Only -,_ and alphanumeric characters allowed.
     * @param string $currency Three-letter ISO currency.
     * @param string $remark The reason for the transfer.
     * 
     * @return IResponse
     */
    public function initiateToRecipient(string $recipient, float $amount, string $reference = '', string $currency = 'NGN', string $remark = '') : IResponse {
        try {
            if (Utility::isEmpty($recipient))
                return new Response(true, 'Recipient code is required.');
            if ($amount <= 0)
                return new Response(true, 'Amount must be grater than zero.');
            if (!Utility::isEmpty($currency) && \mb_strlen($currency) != 3)
                return new Response(true, 'Invalid currency.');
            
            $body = [
                'source' => 'balance',
                'recipient' => Utility::parseString($recipient),
                'amount' => $amount * 100
            ];
            if (!Utility::isEmpty($currency)) {
                $body['currency'] = Utility::parseString($currency);
            }
            if (!Utility::isEmpty($reference)) {
                $body['reference'] = Utility::parseString($reference);
            }
            if (!Utility::isEmpty($remark)) {
                $body['reason'] = Utility::parseString($remark);
            }

            return Request::getInstance()->execute(IRequest::TYPE_POST, 'transfer', $body);
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
     * @param float $amount Amount to be transferred.
     * @param string $currency Three-letter ISO currency.
     * 
     * @return IResponse
     */
    public function initiateBulk(array $transfers, string $currency = 'NGN') : IResponse {
        try {
            if (Utility::isEmpty($transfers))
                return new Response(true, 'Transfers parameter is required and must be an array of \Aweklin\Paystack\Models\PaymentTransfer.');
            if (!Utility::isEmpty($currency) && \mb_strlen($currency) != 3)
                return new Response(true, 'Invalid currency.');
            
            $body = ['source' => 'balance'];
            if (!Utility::isEmpty($currency)) {
                $body['currency'] = Utility::parseString($currency);
            }

            $i = 1;
            $body['transfers'] = [];
            foreach($transfers as $transfer) {
                if ($transfer instanceof PaymentTransfer) {
                    \array_push($body['transfers'], [
                        'amount' => $transfer->getAmount(),
                        'recipient' => $transfer->getRecipient(),
                        'reference' => $transfer->getReference()
                    ]);
                } else
                    throw new \InvalidArgumentException("The object on line {$i} must be an instance of \Aweklin\Paystack\Models\PaymentTransfer.");

                $i++;
            }

            return Request::getInstance()->execute(IRequest::TYPE_POST, 'transfer/finalize_transfer', $body);
        } catch (EmptyValueException $e) {
            return new Response(true, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return new Response(true, $e->getMessage());
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
    public function finalizeTransferWithOTP(string $transferCode, string $otp) : IResponse {
        try {
            if (Utility::isEmpty($transferCode))
                return new Response(true, 'Transaction code for recipient is required.');
            if (Utility::isEmpty($otp))
                return new Response(true, 'OTP is required.');
            
            $body = [
                'transfer_code' => Utility::parseString($transferCode),
                'otp' => Utility::parseString($otp)
            ];

            return Request::getInstance()->execute(IRequest::TYPE_POST, 'transfer/finalize_transfer', $body);
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
    public function resendOTP(string $transferCode) : IResponse {
        try {
            if (Utility::isEmpty($transferCode))
                return new Response(true, 'Transaction code for recipient is required.');
            
            $body = [
                'transfer_code' => Utility::parseString($transferCode),
                'reason' => 'resend_otp'
            ];

            return Request::getInstance()->execute(IRequest::TYPE_POST, 'transfer/resend_otp', $body);
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
    public function disableOTP() : IResponse {
        try {
            return Request::getInstance()->execute(IRequest::TYPE_POST, 'transfer/disable_otp');
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * In the event that a customer wants to stop being able to complete transfers programmatically, this endpoint helps turn OTP requirement back on.
     * 
     * @return IResponse
     */
    public function enableOTP() : IResponse {
        try {
            return Request::getInstance()->execute(IRequest::TYPE_POST, 'transfer/enable_otp');
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
    public function finalizeDisableOTP(string $otp) : IResponse {
        try {
            if (Utility::isEmpty($otp))
                return new Response(true, 'OTP is required.');
            
            $body = ['otp' => Utility::parseString($otp)];

            return Request::getInstance()->execute(IRequest::TYPE_POST, 'transfer/disable_otp_finalize', $body);
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Performs a transfer search based on the parameters specified.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * @param string $customParams Custom parameters to filter transfers with.
     * 
     * @return IResponse
     */
    protected function search(int $pageNumber = 1, int $pageSize = 50, string $customParams = '') : IResponse {
        try {
            $this->normalizePagingParams($pageNumber, $pageSize);
            return Request::getInstance()->execute(IRequest::TYPE_GET, "transfer/?perPage={$pageSize}&page={$pageNumber}{$customParams}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Performs a transfer search based with the specified customer id.
     * 
     * @param int $customerId Specify an ID for the customer whose transfers you want to retrieve.
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public function getByCustomerId(string $customerId, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        try {
            if ($customerId <= 0)
                return new Response(true, 'Customer id is required.');

            $searchParameter = new TransferParameter();
            $searchParameter->setCustomerId($customerId);
           
            return $this->filter($searchParameter, $pageNumber, $pageSize);
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
    public function get(string $id) : IResponse {
        try {
            if (Utility::isEmpty($id))
                return new Response(true, 'Transfer ID or code is required.');

            return Request::getInstance()->execute(IRequest::TYPE_GET, "transfer/{$id}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

}