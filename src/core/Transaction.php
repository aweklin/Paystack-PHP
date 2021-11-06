<?php

namespace Aweklin\Paystack\Core;

use Aweklin\Paystack\Abstracts\{IRequest, IResponse};
use Aweklin\Paystack\ConcreteAbstract\{PaymentMethod, Filterable};
use Aweklin\Paystack\Concrete\{Request, Response};
use Aweklin\Paystack\Models\{MobileMoneyPayment, BankPayment, DefaultPayment, TransactionParameter, USSDPayment, QRCodePayment, RecurringPayment};
use Aweklin\Paystack\Exceptions\{EmptyParameterException, EmptyValueException, ParameterExistsException};
use Aweklin\Paystack\Infrastructures\Utility;
use Aweklin\Paystack\Paystack;
use InvalidArgumentException;

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

    private function _processMultipleArgumentsForPayment($args) : IResponse {
        if (!is_string($args[0]))
            throw new InvalidArgumentException("Email is expected as the first argument.");
        if (!\filter_var($args[0], \FILTER_VALIDATE_EMAIL))
            throw new InvalidArgumentException("Invalid email address.");
        if (!is_numeric($args[1]))
            throw new InvalidArgumentException("Amount is expected as the second argument.");
        
        $defaultPayment = new DefaultPayment($args[0], floatval($args[1]));
        
        array_shift($args); // remove email
        array_shift($args); // remove amount

        if ($args) {
            switch(count($args)) {
                case 1:
                    $value = $args[0];
                    if (is_string($value)) {
                        if (in_array($value, [Paystack::CURRENCY_GHS, Paystack::CURRENCY_NGN, Paystack::CURRENCY_USD])) {
                            $defaultPayment->setCurrency($value);
                        } else {
                            $defaultPayment->setReference($value);
                        }
                    }
                    break;

                case 2:
                    $arg1 = $args[0];
                    $arg2 = $args[1];
                    if (is_string($arg1) && is_array($arg2)) {
                        if (in_array($arg1, [Paystack::CURRENCY_GHS, Paystack::CURRENCY_NGN, Paystack::CURRENCY_USD])) {
                            $defaultPayment->setCurrency($arg1);
                        } else {
                            $defaultPayment->setReference($arg1);
                        }
                        $this->_setCustomFields($defaultPayment, $arg2);

                    } elseif (is_string($arg2) && is_array($arg1)) {
                        if (in_array($arg2, [Paystack::CURRENCY_GHS, Paystack::CURRENCY_NGN, Paystack::CURRENCY_USD])) {
                            $defaultPayment->setCurrency($arg2);
                        } else {
                            $defaultPayment->setReference($arg2);
                        }
                        $this->_setCustomFields($defaultPayment, $arg1);
                    }
                    break;

                case 3: // must follow the order reference: string, currency: string, custom_fields: array
                    $defaultPayment->setReference($args[0]);
                    $defaultPayment->setCurrency($args[1]);
                    if (!is_array($args[2]))
                        throw new InvalidArgumentException("The 3rd argument must be an array");

                    $this->_setCustomFields($defaultPayment, $args[2]);
                    break;
                    
            }
        }

        /*if (isset($args[2]) && is_array($args[2])) {// custom fields
            foreach($args[2] as $field => $value) {
                if (!is_object($value) && !is_array($value)) {
                    $defaultPayment->addCustomField($field, $value);
                }
            }
        } else {
            if (isset($args[2]) && is_string($args[2])) { // reference
                $defaultPayment->setReference($args[2]);
            }
        }*/
        
        return $this->_initialize($defaultPayment);
    }

    private function _setCustomFields(PaymentMethod &$paymentMethod, array $data) {
        foreach($data as $field => $value) {
            if (!is_object($value) && !is_array($value)) {
                $paymentMethod->addCustomField($field, $value);
            }
        }
    }

    public function __call($name, $arguments) {
        if ($name == 'initiate') {
            $argumentDescription = "A single argument passed must be of type PaymentMethod, otherwise, pass 2 arguments with string and float, where the first argument represents the email and the second argument represents the amount to pay.";
            
            switch(count($arguments)) {
                case 1:
                    try {
                        $arguments = $arguments[0];
                        if (isset($arguments[0]) && !isset($arguments[1])) {    // PaymentMethod instance passed                            
                            return $this->_initialize($arguments[0]);
                        }
                        
                        // email and amount passed [& possibly, 3rd param as array of custom fields]
                        return $this->_processMultipleArgumentsForPayment($arguments);
                    } catch (\Throwable $th) {
                        //echo PHP_EOL . "Error: " . $th->getMessage() . " on line " . $th->getLine();
                        throw new InvalidArgumentException($argumentDescription);
                    }

                case 2:
                    return $this->_processMultipleArgumentsForPayment($arguments); 

                default:
                    throw new InvalidArgumentException("Unsupported number of arguments. Please pass only one or two arguments. {$argumentDescription}");
            }
        }
    }

    /**
     * Initiates a transaction based on the payment method model specified.
     * 
     * @param PaymentMethod $paymentMethod A model class that inherits from the PaymentMethod base class.
     * 
     * @return IResponse
     */
    private function _initialize(PaymentMethod $paymentMethod) : IResponse {
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
            if (!Utility::isEmpty($paymentMethod->getReference())) {
                $body['ref'] = $paymentMethod->getReference();
            }
            if (!Utility::isEmpty($paymentMethod->getCurrency())) {
                $body['currency'] = $paymentMethod->getCurrency();
            }
            
            if ($paymentMethod instanceof MobileMoneyPayment) {
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
            
            $endpoint = ($paymentMethod instanceof DefaultPayment ? 'transaction/initialize' : 'charge');
            return Request::getInstance()->execute(IRequest::TYPE_POST, $endpoint, $body);

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