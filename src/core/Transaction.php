<?php

namespace Aweklin\Paystack\Core;

use Aweklin\Paystack\Abstracts\{IRequest, IResponse};
use Aweklin\Paystack\ConcreteAbstract\{PaymentMethod, Filterable};
use Aweklin\Paystack\Concrete\{Request, Response};
use Aweklin\Paystack\Models\{MobileMoneyPayment, BankPayment, TransactionParameter, USSDPayment, QRCodePayment, RecurringPayment};
use Aweklin\Paystack\Exceptions\{EmptyParameterException, EmptyValueException, ParameterExistsException};
use Aweklin\Paystack\Infrastructures\Utility;

/**
 * Contains methods to start a payment transaction from start to finish.
 */
class Transaction extends Filterable {

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_ABANDONED = 'abandoned';

    const FILTER_PARAM_STATUS = 'status';
    const FILTER_PARAM_CUSTOMER_ID = 'customer';
    const FILTER_PARAM_START_DATE = 'from';
    const FILTER_PARAM_END_DATE = 'to';
    const FILTER_PARAM_AMOUNT = 'amount';
    const FILTER_PARAM_CURRENCY = 'currency';

    /**
     * Initiates a transaction based on the payment method model specified.
     * 
     * @param PaymentMethod $paymentMethod A model class that inherits from the PaymentMethod base class.
     * 
     * @return IResponse
     */
    public function initiate(PaymentMethod $paymentMethod) : IResponse {
        try {
            if (!$paymentMethod)
                return new Response(true, 'Payment method information is required.');
            
            $validationResult = $paymentMethod->validate();
            if ($validationResult->hasError())
                return $validationResult;

            $body = [
                'amount' => $paymentMethod->getAmount(),
                'email' => $paymentMethod->getEmail()
            ];

            if ($paymentMethod instanceof MobileMoneyPayment) {
                $body['currency'] = $paymentMethod->getCurrency();
                $body['mobile_money'] = [
                    'phone' => $paymentMethod->getPhone(),
                    'provider' => $paymentMethod->getProvider()
                ];
            } elseif ($paymentMethod instanceof BankPayment) {
                $body['bank'] = [
                    'code' => $paymentMethod->getCode(),
                    'account_number' => $paymentMethod->getAccountNumber()
                ];
                if ($paymentMethod->getBirthDate()) {
                    $body['birthday'] = $paymentMethod->getBirthDate();
                }
            } elseif ($paymentMethod instanceof USSDPayment) {
                $body['ussd'] = [
                    'type' => $paymentMethod->getType()
                ];
            } elseif ($paymentMethod instanceof QRCodePayment) {
                $body['qr'] = [
                    'provider' => $paymentMethod->getProvider()
                ];
            }

            if ($paymentMethod instanceof RecurringPayment) {
                $body['authorization_code'] = $paymentMethod->getAuthorizationCode();

                return Request::getInstance()->execute(IRequest::TYPE_POST, 'transaction/charge_authorization', $body);
            }
            if (!Utility::isEmpty($paymentMethod->getCustomFields())) {
                $customFields = [];
                foreach($paymentMethod->getCustomFields() as $key => $value) {
                    $customFields[$key] = $value;
                }
                $body['metadata'] = [
                    'custom_fields' => [$customFields]
                ];
            }

            return Request::getInstance()->execute(IRequest::TYPE_POST, 'charge', $body);

        } catch (EmptyParameterException $e) {
            return new Response(true, $e->getMessage());
        }  catch (EmptyValueException $e) {
            return new Response(true, $e->getMessage());
        }  catch (ParameterExistsException $e) {
            return new Response(true, $e->getMessage());
        }  catch (\InvalidArgumentException $e) {
            return new Response(true, $e->getMessage());
        }  catch (\OutOfRangeException $e) {
            return new Response(true, $e->getMessage());
        }  catch (\LogicException $e) {
            return new Response(true, $e->getMessage());
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Concludes a payment process initiated via bank payment method.
     * 
     * @param string $reference Transaction reference number.
     * @param string $otp One-Time-Password (OTP).
     * 
     * @return IResponse
     */
    public function sendOTP(string $reference, string $otp) : IResponse {
        try {
            if (Utility::isEmpty($reference))
                return new Response(true, 'Transaction reference number is required.');
            if (Utility::isEmpty($otp))
                return new Response(true, 'One-Time-Password (OTP) is required.');
            
            $body = [
                'otp' => $otp,
                'reference' => $reference
            ];
            return Request::getInstance()->execute(IRequest::TYPE_POST, 'charge/submit_otp', $body);

        } catch (EmptyParameterException $e) {
            return new Response(true, $e->getMessage());
        }  catch (EmptyValueException $e) {
            return new Response(true, $e->getMessage());
        }  catch (ParameterExistsException $e) {
            return new Response(true, $e->getMessage());
        }  catch (\InvalidArgumentException $e) {
            return new Response(true, $e->getMessage());
        }  catch (\OutOfRangeException $e) {
            return new Response(true, $e->getMessage());
        }  catch (\LogicException $e) {
            return new Response(true, $e->getMessage());
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Verifies transaction after payment has been made using the transaction reference number.
     * 
     * @param string $reference The transaction reference number to verify.
     * 
     * @return IResponse
     */
    public function verify(string $reference) : IResponse {
        try {
            return Request::getInstance()->execute(IRequest::TYPE_GET, "transaction/verify/{$reference}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

    /**
     * Returns details of a transaction.
     * 
     * @param int $id An ID for the transaction to fetch
     */
    public function get(int $id) : IResponse {
        if ($id <= 0)
            return new Response(true, 'Transaction reference is required.');
        
        return Request::getInstance()->execute(IRequest::TYPE_GET, "transaction/{$id}");
    }

    /**
     * All mastercard and visa authorizations can be checked with this method to know if they have funds for the payment you seek.
     *
     * This method should be used when you do not know the exact amount to charge a card when rendering a service. 
     * It should be used to check if a card has enough funds based on a maximum range value.
     */
    public function checkAuthorization(RecurringPayment $recurringPayment) : IResponse {
        try {
            if (Utility::isEmpty($recurringPayment))
                return new Response(true, 'Authorization detail is required.');
            
            $validationResult = $recurringPayment->validate();
            if ($validationResult->hasError())
                return $validationResult;

            $body = [
                'email' => $recurringPayment->getEmail(),
                'amount' => $recurringPayment->getAmount(),
                'authorization_code' => $recurringPayment->getAuthorizationCode()
            ];

            return Request::getInstance()->execute(IRequest::TYPE_POST, 'transaction/check_authorization', $body);
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
    public function getByStatus(string $status, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        $transactionParameter = new TransactionParameter();
        $transactionParameter->setStatus($status);
        
        return $this->filter($transactionParameter, $pageNumber, $pageSize);
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
    public function getByCustomerId(int $customerId, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        $transactionParameter = new TransactionParameter();
        $transactionParameter->setCustomerId($customerId);
        
        return $this->filter($transactionParameter, $pageNumber, $pageSize);
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
    public function getByAmount(float $amount, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        $transactionParameter = new TransactionParameter();
        $transactionParameter->setAmount($amount);
        
        return $this->filter($transactionParameter, $pageNumber, $pageSize);
    }

    /**
     * Filters and returns all transactions carried out by status between two dates.
     * Simply pass in one of the class constants Transaction::STATUS_... to get the right transaction status to use for the filter.
     * 
     * @param string $status Filter transactions by status ('failed', 'success', 'abandoned'). Please invoke one of the status constants in Transaction::STATUS_...
     * @param string $startDate A timestamp from which to start listing transaction e.g. 2016-09-24T00:00:05.000Z, 2016-09-21.
     * @param string $endDate A timestamp at which to stop listing transaction e.g. 2016-09-24T00:00:05.000Z, 2016-09-21.
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public function getByStatusAndDates(string $status, string $startDate, string $endDate, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        $transactionParameter = new TransactionParameter();
        $transactionParameter
            ->setStatus($status)
            ->setStartDate($startDate)
            ->setEndDate($endDate);
        
        return $this->filter($transactionParameter, $pageNumber, $pageSize);
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
            return Request::getInstance()->execute(IRequest::TYPE_GET, "transaction/?perPage={$pageSize}&page={$pageNumber}{$customParams}");
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

}