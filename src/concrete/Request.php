<?php

namespace Aweklin\Paystack\Concrete;

use Aweklin\Paystack\Abstracts\{IRequest, IResponse};
use Aweklin\Paystack\Concrete\Response;
use Aweklin\Paystack\Infrastructures\{Utility, Logger};

/**
 * Abstracts the implementation of network call activities made to the Paystack server.
 * 
 * @author Akeem Aweda | +2347085287169 | akeem@aweklin.com
 * @version 1.0
 */
class Request implements IRequest {

    /**
     * Specifies the secret key to use to make requests to Paystack server.
     */
    private $_apiKey;

    private $_isLoggingEnabled;

    private $_isTelemetryEnabled;
    
    /**
     * Stores the valid request types for making network calls.
     */
    private $_validRequestTypes;

    /**
     * Stores an instance of the Request object.
     */
    private static $_instance;

    /**
     * Creates and returns a new instance of the Request object.
     */
    private function __construct() {
        $this->_validRequestTypes = [IRequest::TYPE_GET, IRequest::TYPE_POST, IRequest::TYPE_PUT, IRequest::TYPE_DELETE];
    }

    /**
     * Creates and returns a new instance of the Request object. Only a single instance of this class exists throughout the application lifecycle.
     * 
     * @return Request
     */
    public static function getInstance() : Request {
        if (!isset(self::$_instance) || (isset(self::$_instance) && !self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Sets the API key to be used for all requests. This method is expected to be called once and inside the `Paystack::initialize($apiKey)` method.
     * 
     * @param string $apiKey A string value, representing the API (secret) key from your Paystack dashboard or settings page.
     * 
     * @return Request
     */
    public function setApiKey(string $apiKey) : Request {
        $this->_apiKey = $apiKey;

        return $this;
    }

    /**
     * Determines whether request logging is turned on or switched off.
     * 
     * @param bool $isEnabled Specifies request logging status.
     * 
     * @return Request
     */
    public function enableLogging(bool $isEnabled) : Request {
        $this->_isLoggingEnabled = $isEnabled;

        return $this;
    }

    /**
     * Determines whether telemetry logging is turned on or switched off.
     * 
     * @param bool $isEnabled Specifies telemetry logging status.
     * 
     * @return Request
     */
    public function enableTelemetry(bool $isEnabled) : Request {
        $this->_isTelemetryEnabled = $isEnabled;

        return $this;
    }

    /**
     * Executes the given request on Paystack server and returns a value, indicating the status, message and data gotten from the request.
     * 
     * @param string $type Indicates if the request is a get or post. Please use the constant value in `IRequest::TYPE_...` 
     * @param string $endpoint The endpoint to initiate on Paystack server. No need to specify the full URL for this parameter.
     * @param array $body Default is empty array and it is required when making a post request. Note that the value must be specified as associative array.
     * 
     * @return IResponse
     */
    public function execute(string $type, string $endpoint, array $body = []) : IResponse {
        $hasError = true;
        $message = '';
        $data = [];

        $url = PAYSTACK_BASE_URL;
        if (!Utility::endsWith(PAYSTACK_BASE_URL, '/'))
            $url .= '/';
        $url .= $endpoint;

        $requestBody = ($body ? \json_encode($body, JSON_PRETTY_PRINT) : '');

        // validations
        $validationResult = $this->_validate($type, $endpoint, $body);
        if ($validationResult->hasError()) {
            $this->_log($type . ' - ' . $url, $requestBody, $validationResult->getMessage());
            return $validationResult;
        }

        if (!Utility::hasInternetAccess()) {
            $message = 'Unable to establish internet connection!';
            $this->_log($type . ' - ' . $url, $requestBody, $message);
            return new Response(true, $message);
        }

        
        try {
            // clean up
            if (Utility::contains($endpoint, PAYSTACK_BASE_URL))
                \str_replace(PAYSTACK_BASE_URL, '', $endpoint);

            $this->_log($type . ' - ' . $url, $requestBody, "Call to {$url}");

            // make network call
			$networkRequest = $this->_makeNetworkRequest($type, $url, $body);

            // prepare the output
			if ($networkRequest) {
				$networkCallResult = \json_decode($networkRequest, true);

				if($networkCallResult) {
                    $message = $networkCallResult[IResponse::MESSAGE];

					if(isset($networkCallResult[IResponse::DATA])) {
						$data = $networkCallResult[IResponse::DATA];

                        // set the appropriate $message and $hasError status based on the status returned in the success field in the data array;
                        $this->_setStatusResult($hasError, $message, (isset($data[IResponse::DATA_STATUS]) ? $data : $networkCallResult));
                        $message = (isset($data[IResponse::DATA_GATEWAY_RESPONSE]) ? $data[IResponse::DATA_GATEWAY_RESPONSE] : (!$message ? "Operation failed." : $message));
                        
                        // if null was previously stored in this variable, change it to empty to empty array to avoid error
                        if (!$data) 
                            $data = [];
                        
                        if ($data && !\is_array($data) && \is_object($data))
                            $data = Utility::convertObjectToArray($data);
					} else {
                        $this->_setStatusResult($hasError, $message, $networkCallResult);
                    }
				} else {
					$message = 'Something went wrong while trying to convert the request output to json.';
				}
			} else {
				$message = 'Something went wrong while executing curl. Kindly check your request information and try again.';
            }

            
            $responseData = (isset($networkCallResult) && $networkCallResult ? \json_encode($networkCallResult, JSON_PRETTY_PRINT) : '');
            $this->_log($type . ' - ' . $url, $requestBody, $responseData);
		} catch (\Exception $e) {
            $message = $e->getMessage();
            
            $this->_log($type . ' - ' . $url, $requestBody, $message);
        }

        return new Response($hasError, $message, $data);
    }

    private function _log(string $url, string $payload, string $message) : void {
        if ($this->_isLoggingEnabled) {
            Logger::logResponse("URL: {$url}" . PHP_EOL . 
                "Payload: {$payload}" . PHP_EOL . 
                "Response: {$message}");
        }
        if ($this->_isTelemetryEnabled) {
            $ipAddress = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : \gethostname());
            if ($ipAddress) {
                try {
                    $hostName = \gethostbyaddr($ipAddress);
                } catch (\Exception $e) {  // ignore error message
                    $hostName = $ipAddress;
                }

                $telemetry = "Source: {$hostName}" . PHP_EOL . 
                    "URL: {$url}";
                //Logger::sendTelemetry($telemetry);
                //TODO:: send telemetry
                /*try {
                    $this->_makeNetworkRequest(IRequest::TYPE_POST, 
                        'https://aweklin.com/api/paystack/logTelemetry', 
                        [
                            'source' => $hostName,
                            'url' => $url
                        ]);
                } catch (\Exception $e) {}  // ignore error message
                */
            }
        }
    }

    private function _setStatusResult(bool &$hasError, string &$message, array $data) {
        if (\is_string($data[IResponse::DATA_STATUS])) {
            switch (\mb_strtolower($data[IResponse::DATA_STATUS])) {
                case IResponse::STATUS_PENDING:
                case IResponse::STATUS_SUCCESS:
                case IResponse::STATUS_PROCESSED:
                case IResponse::STATUS_SEND_BIRTH_DAY:
                case IResponse::STATUS_SEND_OTP:
                case IResponse::STATUS_OTP:
                case IResponse::STATUS_PAY_OFFLINE:
                    $hasError = false;
                    if (isset($data[IResponse::DATA_DISPLAY_TEXT])) {
                        $message = $data[IResponse::DATA_DISPLAY_TEXT] . PHP_EOL . 
                            ' Please take not of this reference number so you can use it later: ' . $data[IResponse::DATA_REFERENCE];
                    }
                break;
                case IResponse::STATUS_TIME_OUT:
                case IResponse::STATUS_FAILED:
                    $hasError = true;
                break;
            }
        } else
            $hasError = !($data[IResponse::DATA_STATUS]);
    }

    /**
     * Validates the request being made and returns the status and error message (if any) of the validation result.
     * 
     * @param string $type Indicates if the request is a get or post. Please use the constant value in IRequest::TYPE_... 
     * @param string $endpoint The endpoint to initiate on Paystack server. No need to specify the full URL for this parameter.
     * @param array $body Default is empty array and it is required when making a post request. Note that the value must be specified as associative array.
     * 
     * @return IResponse
     */
    private function _validate(string $type, string $endpoint, array $body = []) : IResponse {
        if (Utility::isEmpty($this->_apiKey)) 
            return new Response(true, 'API Secrete key is required.');
        if (Utility::isEmpty($type))
            return new Response(true, 'Request method is required.');
        if (!\in_array($type, $this->_validRequestTypes))
            return new Response(true, 'Invalid request type. Must be one of ' . \join(', ', $this->_validRequestTypes));
        if (Utility::isEmpty($endpoint))
            return new Response(true, 'API endpoint is required.');
        if ($type == IRequest::TYPE_POST && !$body)
            return new Response(true, 'Payload/request body is required.');
        if ($type == IRequest::TYPE_POST && !Utility::isAssociative($body))
            return new Response(true, 'Payload/request body must be an associative array');
        if (!\filter_var(PAYSTACK_BASE_URL, \FILTER_VALIDATE_URL))
            return new Response(true, 'The given value: ' . PAYSTACK_BASE_URL . ' is not a valid URL.');

        return new Response(false, '');
    }

    /**
     * Makes a network call to Paystack server.
     * 
     * @param string $type Indicates if the request is a get or post. Please use the constant value in IRequest::TYPE_... 
     * @param string $endpoint The endpoint to initiate on Paystack server. No need to specify the full URL for this parameter.
     * @param array $body Default is empty array and it is required when making a post request. Note that the value must be specified as associative array.
     * 
     * @return mixed
     */
    private function _makeNetworkRequest(string $type, string $url, array $body = []) {
        //echo "Making request to {$url} with type {$type}";
        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_URL, $url);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        if (\mb_strtolower($type) === IRequest::TYPE_POST) {
            curl_setopt($curlHandler, CURLOPT_POST, true);
            $bodyString = http_build_query($body);
            curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $bodyString);
        }
        curl_setopt(
            $curlHandler, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->_apiKey,
                'Cache-Control: no-cache'
            ]
        );
        $request = curl_exec($curlHandler);
        curl_close($curlHandler);

        return $request;
    }

}