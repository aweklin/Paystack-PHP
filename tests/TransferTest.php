<?php

include_once realpath('.' . DIRECTORY_SEPARATOR . 'autoload.php');
include_once realpath('.' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'config.php');

use Aweklin\Paystack\Paystack;
use Aweklin\Paystack\Models\PaymentTransfer;

use PHPUnit\Framework\TestCase;

class TransferTest extends TestCase {

    function setUp() : void {
        Paystack::initialize(TEST_API_SECRET_KEY);
    }

    function testSingleTransferToBeneficiaryFailForInvalidBeneficiaryId() {
        $transferResult = Paystack::initiateTransferToBeneficiary(INVALID_BENEFICIARY_ID, VALID_AMOUNT);
        $this->assertEquals("Recipient specified is invalid", $transferResult->getMessage());
    }

    function testSingleTransferToBeneficiarySuccessful() {
        $transferResult = Paystack::initiateTransferToBeneficiary(VALID_BENEFICIARY_CODE, VALID_AMOUNT, '', Paystack::CURRENCY_NGN, 'Testing transfer.');
        $this->assertFalse($transferResult->hasError());
    }

    function testFinalizeTransferWithOTPSuccessful() {
        $transferResult = Paystack::completeTransferWithOTP("TRF_j496b0sowjb4oah", '234528');
        $this->assertFalse($transferResult->hasError());
    }

    function testGetTransferDetailSuccessful() {
        $transferResult = Paystack::getTransfer("TRF_j496b0sowjb4oah");
        $this->assertFalse($transferResult->hasError());
    }

}