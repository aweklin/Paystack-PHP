<?php

include_once realpath('.' . DIRECTORY_SEPARATOR . 'autoload.php');
include_once realpath('.' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'config.php');

use Aweklin\Paystack\Paystack;
use Aweklin\Paystack\Core\Transaction;
use Aweklin\Paystack\Models\{MobileMoneyPayment, BankPayment, USSDPayment, QRCodePayment, RecurringPayment, TransactionParameter, DefaultPayment};

use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase {

    public function setUp() : void {
        Paystack::initialize(TEST_API_SECRET_KEY);
    }

    function testFailForEmptyApiKey() {
        Paystack::initialize('');
        $verificationResult = Paystack::verifyTransaction('abcd');
        $this->assertEquals('API Secrete key is required.', $verificationResult->getMessage());
        $this->setUp();
    }

    function testTransactionListSuccessful() {
        $transactionList = Paystack::getTransactions();
        $this->assertFalse($transactionList->hasError());
    }

    function testTransactionListByDateRangeFailForEmptyDates() {
        $transactionList = Paystack::getTransactionsByDates('', '');
        $this->assertTrue($transactionList->hasError());
    }

    function testTransactionListByDateRangeSuccessful() {
        $transactionList = Paystack::getTransactionsByDates('2020-09-25', '2020-09-30');
        $this->assertFalse($transactionList->hasError());
    }

    function testTransactionListByFailForEmptyParameter() {
        $transactionList = Paystack::getTransactionsBy(new TransactionParameter());
        $this->assertEquals('One or more parameters is required.', $transactionList->getMessage());
    }

    function testTransactionListBySuccessful() {
        $transactionParameter = new TransactionParameter();
        $transactionParameter
            ->setCustomerId(1664722)
            ->setStatus(Transaction::STATUS_SUCCESS);
        $transactionList = Paystack::getTransactionsBy($transactionParameter);
        $this->assertFalse($transactionList->hasError());
    }

    
    function testFailForInvalidTransactionReference() {
        $verificationResult = Paystack::verifyTransaction('abcd');
        $this->assertTrue($verificationResult->hasError());
    }

    function testSuccessfulForValidPaymentReference() {
        $verificationResult = Paystack::verifyTransaction(VALID_PAYMENT_REFERENCE);
        $this->assertFalse($verificationResult->hasError());
    }

    function testMobileMoneyPaymentFailForUnsupportedCurrency() {
        $mobileMoney = new MobileMoneyPayment(VALID_EMAIL, VALID_AMOUNT, 'NGN', '07085287169', 'Airtel');
        $chargeResult = Paystack::initiateTransaction($mobileMoney);
        $this->assertTrue($chargeResult->hasError());
    }

    function testBankPaymentFailForBankCodeNotSupported() {
        $bankPayment = new BankPayment(VALID_EMAIL, VALID_AMOUNT, '058', VALID_ACCOUNT_NUMBER);
        $chargeResult = Paystack::initiateTransaction($bankPayment);
        $this->assertTrue($chargeResult->hasError());
    }

    function testBankPaymentFailForEnterBirthDateMessageInTheResponseObject() {
        $bankPayment = new BankPayment(VALID_EMAIL, VALID_AMOUNT, VALID_BANK_CODE, VALID_ACCOUNT_NUMBER);
        $chargeResult = Paystack::initiateTransaction($bankPayment);
        $this->assertTrue($chargeResult->hasError());
    }

    function testBankPaymentFailWithoutOTP() {
        $bankPayment = new BankPayment(VALID_EMAIL, VALID_AMOUNT, VALID_BANK_CODE, VALID_ACCOUNT_NUMBER, '1995-12-23');
        $chargeResult = Paystack::initiateTransaction($bankPayment);
        $this->assertTrue($chargeResult->hasError());

        return $chargeResult->getData()['reference'];
    }

    /**
     * @depends testBankPaymentFailWithoutOTP
     */
    function testBankPaymentSuccessfulWithOTP(string $reference) {
        $chargeResult = Paystack::completeTransactionWithOTP($reference, '123456');
        $this->assertFalse($chargeResult->hasError());
    }

    function testMobileMoneyPaymentSuccessful() {
        $mobileMoney = new MobileMoneyPayment(VALID_EMAIL, VALID_AMOUNT, 'GHS', VALID_PHONE, 'MTN');
        $chargeResult = Paystack::initiateTransaction($mobileMoney);
        $this->assertFalse($chargeResult->hasError());
    }

    function testUSSDPayment() {
        $ussdPayment = new USSDPayment(VALID_EMAIL, VALID_AMOUNT, USSDPayment::BANK_GUARANTEE_TRUST);
        $chargeResult = Paystack::initiateTransaction($ussdPayment);
        $this->assertTrue($chargeResult->hasError());
    }

    function testQRCodePayment() {
        $qrCodePayment = new QRCodePayment(VALID_EMAIL, VALID_AMOUNT, QRCodePayment::PROVIDER_VISA);
        $chargeResult = Paystack::initiateTransaction($qrCodePayment);
        $this->assertTrue($chargeResult->hasError());
    }

    function testCheckAuthorizationFailForInvalidAuthorizationCode() {
        $recurringPayment = new RecurringPayment(VALID_EMAIL, VALID_AMOUNT, INVALID_AUTHORIZATION_CODE);
        $chargeResult = Paystack::checkAuthorization($recurringPayment);
        $this->assertTrue($chargeResult->hasError());

        return $chargeResult->hasError();
    }

    function testCheckAuthorizationSuccessful() {
        $recurringPayment = new RecurringPayment(VALID_EMAIL, VALID_AMOUNT, VALID_AUTHORIZATION_CODE);
        $chargeResult = Paystack::checkAuthorization($recurringPayment);
        $this->assertFalse($chargeResult->hasError());

        return $chargeResult->hasError();
    }

    /**
     * @depends testCheckAuthorizationSuccessful 
     */  
    function testRecurringPaymentSuccessful(bool $hasErrorCheckingAuthorization) {
        if (!$hasErrorCheckingAuthorization) {
            $recurringPayment = new RecurringPayment(VALID_EMAIL, VALID_AMOUNT, VALID_AUTHORIZATION_CODE);
            $chargeResult = Paystack::initiateTransaction($recurringPayment);
            $this->assertFalse($chargeResult->hasError());
        } else {
            $this->assertFalse(false);
        }
    }  

    public function testDefaultPaymentWithSingleArgumentSuccessful() {
        $payment = new DefaultPayment(VALID_EMAIL, VALID_AMOUNT);
        $result = Paystack::initiateTransaction($payment);
        $this->assertEquals('Authorization URL created', $result->getMessage());
    }

    public function testDefaultPaymentWithDoubleArgumentsSuccessful() {
        $result = Paystack::initiateTransaction(VALID_EMAIL, VALID_AMOUNT);
        $this->assertEquals('Authorization URL created', $result->getMessage());
    }

    public function testDefaultPaymentWith3ArgumentsSuccessful() {
        $result = Paystack::initiateTransaction(VALID_EMAIL, VALID_AMOUNT, ['name' => 'Akeem Aweda', 'profession' => 'Software Engineer']);
        $this->assertEquals('Authorization URL created', $result->getMessage());
    }

    public function testDefaultPaymentWith4ArgumentsSuccessful() {
        $result = Paystack::initiateTransaction(VALID_EMAIL, VALID_AMOUNT, 'abcd1234', ['name' => 'Akeem Aweda', 'profession' => 'Software Engineer']);
        $this->assertEquals('Authorization URL created', $result->getMessage());
    }

    public function testDefaultPaymentWith5ArgumentsSuccessful() {
        $result = Paystack::initiateTransaction(VALID_EMAIL, VALID_AMOUNT, 'abcd1234', Paystack::CURRENCY_NGN, ['name' => 'Akeem Aweda', 'profession' => 'Software Engineer']);
        $this->assertEquals('Authorization URL created', $result->getMessage());
    }
}