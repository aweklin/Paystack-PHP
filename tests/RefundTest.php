<?php

include_once realpath('.' . DIRECTORY_SEPARATOR . 'autoload.php');
include_once realpath('.' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'config.php');

use Aweklin\Paystack\Paystack;
use Aweklin\Paystack\Models\RefundParameter;

use PHPUnit\Framework\TestCase;

class RefundTest extends TestCase {

    function setUp() : void {
        Paystack::initialize(TEST_API_SECRET_KEY);
    }

    function testInitiateRefundFailForInvalidRefundID() {
        $refundResult = Paystack::initiateRefund(INVALID_TRANSACTION_ID);
        $this->assertTrue($refundResult->hasError());
    }
    
    function testInitiateRefundSuccessful() {
        $refundResult = Paystack::initiateRefund(825243238);
        $this->assertFalse($refundResult->hasError());
    }

    function testRefundCheckFailForInvalidRefundID() {
        $refundCheck = Paystack::getRefund(INVALID_TRANSACTION_ID);
        $this->assertTrue($refundCheck->hasError());
    }

    function testRefundCheckSuccessful() {
        $refundCheck = Paystack::getRefund(971134);
        $this->assertFalse($refundCheck->hasError());
    }

    function testRefundListSuccessful() {
        $refundList = Paystack::getRefunds();
        $this->assertFalse($refundList->hasError());
    }

    function testRefundListByDateRangeFailForEmptyDates() {
        $refundList = Paystack::getRefundsByDates('', '');
        $this->assertTrue($refundList->hasError());
    }

    function testRefundListByDateRangeSuccessful() {
        $refundList = Paystack::getRefundsByDates('2020-09-25', '2020-09-30');
        $this->assertFalse($refundList->hasError());
    }

    function testRefundListByFailForEmptyParameter() {
        $refundList = Paystack::getRefundsBy(new RefundParameter());
        $this->assertEquals('One or more parameters is required.', $refundList->getMessage());
    }

    function testRefundListBySuccessful() {
        $transactionParameter = new RefundParameter();
        $transactionParameter->setCurrency(Paystack::CURRENCY_NGN);
        $refundList = Paystack::getRefundsBy($transactionParameter);
        $this->assertFalse($refundList->hasError());
    }

}