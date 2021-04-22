<?php

namespace Aweklin\Paystack\Abstracts;

/**
 * Encapsulates the methods used to obtain result/response from Paystack request.
 */
interface IResponse {

    /**
     * Transaction is successful. Give value after checking to see that all is in order.
     */
    const STATUS_SUCCESS = 'success';

    /**
     * Transaction refund is successful.
     */
    const STATUS_PROCESSED = 'processed';

    /**
     * Transaction failed. No remedy for this, start a new charge after showing data.message to user
     */
    const STATUS_FAILED = 'failed';

    /**
     * Transaction is being processed. Call Check pending charge at least 10 seconds after getting this status to check status.
     */
    const STATUS_PENDING = 'pending';

    /**
     * Paystack needs OTP from customer to complete the transaction. 
     * Show data.display_text to user with an input that accepts OTP and submit the OTP to the Submit OTP with reference and otp
     */
    const STATUS_SEND_OTP = 'send_otp';

    /**
     * Transaction is awaiting OTP to complete.
     */
    const STATUS_OTP = 'otp';

    /**
     * Customer's birthday is needed to complete the transaction. 
     * Show data.display_text to user with an input that accepts the birthdate and submit to the Submit Birthday endpoint with reference and birthday.
     */
    const STATUS_SEND_BIRTH_DAY = 'send_birthday';

    /**
     * Used for the USSD or mobile money payment option. Show data.display_text or data.ussd_code to user with instruction on how to complete the transaction by dialing USSD code.
     * When the user completes payment, a response is sent to the merchant’s webhook. Hence, for this to work properly as expected, webhooks must be set up for the merchant.
     */
    const STATUS_PAY_OFFLINE = 'pay_offline';

    /**
     * Transaction has failed. You may start a new charge after showing data.message to user
     */
    const STATUS_TIME_OUT = 'timeout';

    /**
     * Stores the response message.
     */
    const MESSAGE = 'message';

    /**
     * Stores the response data.
     */
    const DATA = 'data';

    /**
     * Stores the transaction reference number.
     */
    const DATA_REFERENCE = 'reference';

    /**
     * Stores the response status (bool|string).
     */
    const DATA_STATUS = 'status';

    /**
     * Stores the text that should be displayed to the user when the status returns one of [STATUS_SEND_OTP, STATUS_SEND_BIRTH_DAY].
     */
    const DATA_DISPLAY_TEXT = 'display_text';

    /**
     * Stores the gateway response.
     */
    const DATA_GATEWAY_RESPONSE = 'gateway_response';

    /**
     * Returns true if the request made contains or results in an error.
     * 
     * @return bool
     */
    function hasError(): bool;

    /**
     * Returns the response message obtained from the request.
     * 
     * @return string
     */
    function getMessage(): string;

    /**
     * Returns the response data obtained from the request.
     * 
     * @return array
     */
    function getData(): array;

}