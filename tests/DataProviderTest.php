<?php

include_once realpath('.' . DIRECTORY_SEPARATOR . 'autoload.php');
include_once realpath('.' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'config.php');

use Aweklin\Paystack\Paystack;

use PHPUnit\Framework\TestCase;

class DataProviderTest extends TestCase {

    public function setUp() : void {
        Paystack::initialize(TEST_API_SECRETE_KEY);
    }

    public function testBalanceSuccessful() {
        $banks = Paystack::getBalance();
        $this->assertNotEmpty($banks->getData());
    }

    public function testBankListSuccessful() {
        $banks = Paystack::getBanks();
        $this->assertNotEmpty($banks->getData());
    }

    public function testProviderListSuccessful() {
        $providers = Paystack::getProviders();
        $this->assertNotEmpty($providers->getData());
    }

    public function testBVNVerificationSuccessful() {
        $bvnVerificationResult = Paystack::verifyBVN(INVALID_BVN, INVALID_BANK_CODE_FOR_BVN_VERIFICATION, INVALID_ACCOUNT_NUMBER);
        $this->assertEquals('BVN resolved', $bvnVerificationResult->getMessage());
    }

    public function testBVNDetailFailWithUnableToResolveBVN() {
        $bvnVerificationResult = Paystack::getBVNDetails(INVALID_BVN);
        $this->assertEquals('Unable to resolve BVN', $bvnVerificationResult->getMessage());
    }

    public function testAccountDetailFailWithUnknownBankCode087() {
        $bvnVerificationResult = Paystack::getAccountDetails(INVALID_ACCOUNT_NUMBER, INVALID_BANK_CODE_FOR_BVN_VERIFICATION);
        $this->assertEquals('Unknown bank code: 087', $bvnVerificationResult->getMessage());
    }

    public function testAccountDetailSuccessful() {
        $bvnVerificationResult = Paystack::getAccountDetails(VALID_ACCOUNT_NUMBER, VALID_BANK_CODE_FOR_BVN_VERIFICATION);
        $this->assertFalse($bvnVerificationResult->hasError());
    }

}