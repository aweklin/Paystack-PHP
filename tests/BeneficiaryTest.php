<?php

include_once realpath('.' . DIRECTORY_SEPARATOR . 'autoload.php');
include_once realpath('.' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'config.php');

use Aweklin\Paystack\Paystack;
use Aweklin\Paystack\ConcreteAbstract\SearchParameter;

use PHPUnit\Framework\TestCase;

class BeneficiaryTest extends TestCase {

    function setUp() : void {
        Paystack::initialize(TEST_API_SECRETE_KEY);
    }

    function testBeneficiaryCreationFailForInvalidAccountNumber() {
        $beneficiaryCreationResult = Paystack::createBeneficiary(INVALID_ACCOUNT_NUMBER, VALID_BANK_CODE, VALID_ACCOUNT_NAME);
        $this->assertEquals("Cannot resolve account", $beneficiaryCreationResult->getMessage());
    }

    function testBeneficiaryCreationSuccessful() {
        $beneficiaryCreationResult = Paystack::createBeneficiary(VALID_ACCOUNT_NUMBER, VALID_BANK_CODE_FOR_BVN_VERIFICATION, VALID_ACCOUNT_NAME);
        $this->assertEquals("Transfer recipient created successfully", $beneficiaryCreationResult->getMessage());
    }

    function testBeneficiaryUpdateSuccessful() {
        $beneficiaryCreationResult = Paystack::updateBeneficiary(VALID_BENEFICIARY_ID, VALID_ACCOUNT_NAME, VALID_EMAIL, 'A sample test account');
        $this->assertEquals('Recipient updated', $beneficiaryCreationResult->getMessage());
    }

    function testBeneficiaryListSuccessful() {
        $beneficiaries = Paystack::getBeneficiaries();
        $this->assertFalse($beneficiaries->hasError());
    }

    function testBeneficiaryDetailsSuccessful() {
        $beneficiaries = Paystack::getBeneficiary(VALID_BENEFICIARY_ID);
        $this->assertFalse($beneficiaries->hasError());
    }
}