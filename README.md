<div align="center" id="top"> 
  <!-- <img src="./.github/app.gif" alt="Paystack-PHP" /> -->

  &#xa0;

  <!-- <a href="https://paystack.netlify.app">Demo</a> -->
</div>

<h1 align="center">Paystack-PHP</h1>

<p align="center">
  <img alt="Github top language" src="https://img.shields.io/github/languages/top/aweklin/paystack-php?color=56BEB8">

  <img alt="Github language count" src="https://img.shields.io/github/languages/count/aweklin/paystack-php?color=56BEB8">

  <img alt="Repository size" src="https://img.shields.io/github/repo-size/aweklin/paystack-php?color=56BEB8">

  <img alt="License" src="https://img.shields.io/github/license/aweklin/paystack-php?color=56BEB8">

  <img alt="Github issues" src="https://img.shields.io/github/issues/aweklin/paystack-php?color=56BEB8" />

  <img alt="Github forks" src="https://img.shields.io/github/forks/aweklin/paystack-php?color=56BEB8" />

  <img alt="Github stars" src="https://img.shields.io/github/stars/aweklin/paystack-php?color=56BEB8" />
</p>

<!-- Status -->

<!-- <h4 align="center"> 
	🚧  Paystack 🚀 Under construction...  🚧
</h4> 

<hr> -->

<p align="center">
  <a href="#about">About</a> &#xa0; | &#xa0; 
  <a href="#requirements">Requirements</a> &#xa0; | &#xa0;
  <a href="#installation">Installation</a> &#xa0; | &#xa0;
  <a href="#usage">Usage</a> &#xa0; | &#xa0;
  <a href="#license">License</a> &#xa0; | &#xa0;
  <a href="https://github.com/aweklin" target="_blank">Author</a>
</p>

<br>

## About ##

A clean, simple, yet, comprehensive Paystack API wrapper for seamlessly managing your online transactions, transfers, and refunds with ease in PHP!

This library adopts best programming practices to provide an easy way to carry out any form of transaction available on the Paystack API.

## Requirements ##

* Minimum of PHP 7.2

## Installation ##

<!-- To install using composer, invoke the command below:
```
composer install aweklin/Paystack-PHP
``` -->

You can also clone this repository using the command below:
```
git clone https://github.com/aweklin/Paystack-PHP
```

## Usage

- [Initializing the Library](#initializingTheLibrary)
- [Transactions](#transactions)
- [Refunds](#refunds)
- [Transfers](#transfers)
- [Misc](#others)


<div id="initializingTheLibrary">
  <h1>Initializing the Library</h1>
  <p>
    Before you can use this library, you first need to initialize it in your program entry point, perhaps the index.php file. Simply use this line below to initialize the library.
  </p>
  
  <p>
    First, include the autoload file where you want to use it<br>
  </p>
  <code>
      include_once 'path_to_klin_paystack/autoload.php';
  </code>

  <p>
    Then, reference the namespace at the top of the file<br>
    
  </p>
  <code>
    use Aweklin\Paystack\Paystack;
  </code>
    
  <p>
    Next, initialize the API with your secrete key
  </p>
  <code>
    Paystack::initialize('your_api_secrete_key_here');
  </code>

  <h3 style="text-decoration: underline; color: red;"><strong>Important Notes:</strong></h3>
  <p>Note that every requests made returns a uniform response</p>
  <p>You can invoke <code>$result->hasError()</code> to determine if your request returns with an error and you can subsequently call <code>$result->getMessage()</code> for the success or error message. In addition, <code>$result->getData()</code> contains data from Paystack.</p>

  <p>
    When listing records, note that you can specify additional two parameters, which are: <code>$pageNumber</code> and <code>$pageSize</code>. However, this is not applicable to <code>Paystack::getBanks</code> and <code>Paystack::getProviders</code>.
  </p>
  <p>
    Where currency is required part of parameters, you can specify one of the enums: <code>Paystack::CURRENCY_NGN</code>, <code>Paystack::CURRENCY_GHS</code>, and <code>Paystack::CURRENCY_USD</code>. 
  </p>
  <p>
    When supplying date values, please use the yyyy-MM-dd format.
  </p>
</div>

<div id="transactions">
  
  <h1>Transactions</h1>
  
  <h3 style="text-decoration: underline;">Transaction listing</h3>
  
  <p>To list all your transactions, invoke</p>
  
  <code>

    $transactionList = Paystack::getTransactions();

    if ($transactionList->hasError()) {

      echo $transactionList->getMessage();

    } else {

      var_dump ($transactionList->getData());        
    }

  </code>
  
  
  <p>Or, specify the page number and page size</p>

  
  <code>
      
      $transactionList = Paystack::getTransactions($pageNumber, $pageSize);
      if ($transactionList->hasError()) {
        echo $transactionList->getMessage();
      } else {
        var_dump ($transactionList->getData());
      }
  </code>
  
  
  <p>&nbsp;</p>
  
  <p>To list all your transactions by status, invoke</p>
  
  
  <code>

      $transactionList = Paystack::getTransactions($status);      
      if ($transactionList->hasError()) {
        echo $transactionList->getMessage();
      } else {
        var_dump ($transactionList->getData());
      }
  </code>
  

  <p>Or, specify page number and page size</p>
  
  
  <code>

      $transactionList = Paystack::getTransactions($status, $pageNumber, $pageSize);
      <br>
      if ($transactionList->hasError()) {
        echo $transactionList->getMessage();
      } else {
        var_dump ($transactionList->getData());
      }
  </code>
  
 
  <p>$status can be one of <code>Transaction::STATUS_SUCCESS</code>, <code>Transaction::STATUS_FAILED</code>, or <code>Transaction::STATUS_ABANDONED</code></p>

  <p>&nbsp;</p>
  
  <p>To list all your transactions by date range, invoke</p>
  
  
  <code>

      $transactionList = Paystack::getTransactions($startDate, $endDate);
      <br>
      if ($transactionList->hasError()) {
        echo $transactionList->getMessage();
      } else {
        var_dump ($transactionList->getData());
      }
  </code>
  
  
  <p><strong>Date format: yyyy-MM-dd, example: '2020-09-25'</strong></p>
  <p>&nbsp;</p>
  
  <p>To list all your transactions by by other parameter(s), invoke</p>
  
  <code>
      
      $transactionParameter = new TransactionParameter();      
      $transactionParameter<br>
            &nbsp;&nbsp;&nbsp;->setCustomerId(111222333)<br>
            &nbsp;&nbsp;&nbsp;->setStatus(Transaction::STATUS_SUCCESS)<br>
            &nbsp;&nbsp;&nbsp;->setAmount(1000)<br>
            &nbsp;&nbsp;&nbsp;->setCurrency(Paystack::CURRENCY_NGN);<br>
      $transactionList = Paystack::getTransactions($transactionParameter);
      if ($transactionList->hasError()) {
        echo $transactionList->getMessage();
      } else {
        var_dump ($transactionList->getData());
      }
  </code>

  <br>
  
  <p>
    Note that you can set only one or more than one parameter to filter transactions as show above. The TransactionParameter object allows you to chain parameters.
  </p>
  
  <p>
    The <code>setStatus(?)</code> method can take one of the enums: <code>Transaction::STATUS_SUCCESS, Transaction::STATUS_FAILED, Transaction::STATUS_ABANDONED</code>.
  </p>
  
  <p>
    Also, the <code>setCurrency(?)</code> method accepts one of the enums: <code>Paystack::CURRENCY_NGN</code>, <code>Paystack::CURRENCY_GHS</code>, and <code>Paystack::CURRENCY_USD</code>
  </p>

  <h3 style="text-decoration: underline;">Transaction initiation</h3>
  <p>You can initiate a transaction via Bank, Mobile Money, Transfer, QR Code, and USSD.</p>

  <h3>Initiating mobile money payment</h3>
  <p>
    Allows you to carry out a mobile money transaction based on available providers.
  </p>
  <p>Example</p>
  
  <code>
      $mobileMoney = new MobileMoneyPayment('email@domain.com', 5000, 'NGN', '07020000000', 'Airtel');
      $chargeResult = Paystack::initiateTransaction($mobileMoney);
      if ($chargeResult->hasError()) {
        echo $chargeResult->getMessage();
      } else {
        var_dump ($chargeResult->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3>Initiating bank payment</h3>
  <p>
    Allows you to carry out a bank transaction either via USSD or by transfer.
  </p>
  <p>Example</p>
  
  <code>
      $bankPayment = new BankPayment('email@domain.com', 5000, '058', VALID_ACCOUNT_NUMBER);
      $chargeResult = Paystack::initiateTransaction($bankPayment);
      if ($chargeResult->hasError()) {
        echo $chargeResult->getMessage();
      } else {
        var_dump ($chargeResult->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3>Initiating USSD payment</h3>
  
  <p>    
  This Payment method is specifically for Nigerian customers. Nigerian Banks provide USSD services that customers use to perform transactions, and we've integrated with some of them to enable customers complete payments.
    
  The Pay via USSD channel allows your Nigerian customers to pay you by dialling a USSD code on their mobile device. This code is usually in the form of * followed by some code and ending with #. The user is prompted to authenticate the transaction with a PIN and then it is confirmed.
  </p>
  
  <p>Example</p>
  
  
  <code>
      $ussdPayment = new USSDPayment(VALID_EMAIL, VALID_AMOUNT, USSDPayment::BANK_GUARANTEE_TRUST);
      $chargeResult = Paystack::initiateTransaction($ussdPayment);
      if ($chargeResult->hasError()) {
        echo $chargeResult->getMessage();
      } else {
        var_dump ($chargeResult->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  
  <h3>Initiating QR Code payment</h3>
  <p>
    The QR option generates a QR code which allows customers to use their bank's mobile app to complete payments. We currently have only Visa QR option available. We'll have more options later.
 
    When the customer scans the code, they authenticate on their bank app to complete the payment. When the user pays, a response will be sent to your webhook. This means that you need to have webhooks set up on your Paystack Dashboard.
  </p>
  
  <p>Example</p>
  
  
  <code>
      $qrCodePayment = new QRCodePayment(VALID_EMAIL, VALID_AMOUNT, QRCodePayment::PROVIDER_VISA);
      $chargeResult = Paystack::initiateTransaction($qrCodePayment);
      if ($chargeResult->hasError()) {
        echo $chargeResult->getMessage();
      } else {
        var_dump ($chargeResult->getData());
      }
    </code>
  
  
  <p>&nbsp;</p>
  <h3>Initiating a recurring payment</h3>
  <p>
    Allows you to process a recurring payment by charging the authorization code earlier received.
  </p>
  <p>Please note that this requires a valid authorization code.</p>
  
  <p>Example</p>
  
  
  <code>
      $recurringPayment = new RecurringPayment(VALID_EMAIL, VALID_AMOUNT, VALID_AUTHORIZATION_CODE);
      $chargeResult = Paystack::initiateTransaction($recurringPayment);
      if ($chargeResult->hasError()) {
        echo $chargeResult->getMessage();
      } else {
        var_dump ($chargeResult->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Completing transaction with OTP</h3>
  <p>Concludes a payment process initiated via bank payment method.</p>
  <p>Example</p>
  
  <code>
      $chargeResult = Paystack::completeTransactionWithOTP($reference, $otp);
      if ($chargeResult->hasError()) {
        echo $chargeResult->getMessage();
      } else {
        var_dump ($chargeResult->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Transaction verification</h3>
  <p>To verify a transaction, you need to first obtain a transaction reference to be used for the check.</p>
  <p>Example</p>
  
  <code>
      $verificationResult = Paystack::verifyTransaction('abcd');
      if ($verificationResult->hasError()) {
        echo $verificationResult->getMessage();
      } else {
        var_dump ($verificationResult->getData());
      }
    </code>
  
  <p>&nbsp;</p>
</div>

<div id="refunds">
  <h1>Refunds</h1>
  <p>Allows you create and manage transaction refunds.</p>

  <h3 style="text-decoration: underline;">Initiating a refund for a given transaction reference.<h3>
  <p>Example</p>
  
  <code>
      $refundResult = Paystack::initiateRefund($reference);
      if ($refundResult->hasError()) {
        echo $refundResult->getMessage();
      } else {
        var_dump ($refundResult->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Checking refund status</h3>
  <p>It may take up to 24 hours for your refund to be concluded. To check if your refund was successful, invoke</p>
  
  <code>
      $refundCheck = Paystack::getRefund($reference);
      if ($refundCheck->hasError()) {
        echo $refundCheck->getMessage();
      } else {
        var_dump ($refundCheck->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Listing your refunds</h3>
  <p>Return all refunds from inception till date.</p>
  
  <code>
      $refundList = Paystack::getRefunds();
      if ($refundList->hasError()) {
        echo $refundList->getMessage();
      } else {
        var_dump ($refundList->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Listing your refunds by date range</h3>
  <p></p>
  
  <code>
      $refundList = Paystack::getRefundsByDates($startDate, $endDate);
      if ($refundList->hasError()) {
        echo $refundList->getMessage();
      } else {
        var_dump ($refundList->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Listing your refunds by other parameters</h3>
  
  
  <code>
      $transactionParameter = new RefundParameter();
      $transactionParameter->setCurrency(Paystack::CURRENCY_NGN);
      $refundList = Paystack::getRefundsBy($transactionParameter);
      if ($refundList->hasError()) {
        echo $refundList->getMessage();
      } else {
        var_dump ($refundList->getData());
      }
    </code>
  
</div>

<div id="transfers">
  <h1>Transfers</h1>
  <p>Allows you send money.</p>

  <h3 style="text-decoration: underline;">Transfer to beneficiary</h3>
  
  <p>
    Initiates a single transfer request to a beneficiary.

    Status of transfer object returned will be pending if OTP is disabled. In the event that an OTP is required, status will read otp.
  </p>

  <p>Please note that the beneficiary must have been previously added to your beneficiary list, please refer to Misc section.</p>

  <p>Example</p>
  
  <code>
      $transferResult = Paystack::initiateTransferToBeneficiary(VALID_BENEFICIARY_CODE, VALID_AMOUNT, 'reference', Paystack::CURRENCY_NGN, 'Testing transfer.');
      if ($transferResult->hasError()) {
        echo $transferResult->getMessage();
      } else {
        var_dump ($transferResult->getData());
      }
    </code>
  
  
  <br>

  <p>
    Note that the reference is optional, so it can be empty (to allow Paystack generate a transaction reference for you, and it's part of the <code>$transferResult->getData()</code> data).
  </p>

  <p>&nbsp;</p>
  
  <h3 style="text-decoration: underline;">Transfer to bank account</h3>
  
  <p>
    Initiates a single transfer request to an account number.

    Note that this method will have to validate the account number, create the beneficiary, then transfer to the beneficiary.

    Status of transfer object returned will be pending if OTP is disabled. In the event that an OTP is required, status will read otp.
  </p>

  <p>Example</p>
  
  <code>
      $transferResult = Paystack::initiateTransferToAccount($accountNumber, $accountName, $bankCode, $amount, $reference, $currency, $remark);
      if ($transferResult->hasError()) {
        echo $transferResult->getMessage();
      } else {
        var_dump ($transferResult->getData());
      }
    </code>
  

  <p>
    * @param string $accountNumber Receiver's account number.
    <br>* @param string $accountName Receiver's account name.
    <br>* @param string $bankCode Receiver's bank code. You can get the list of Bank Codes by calling the <code>`Aweklin\Paystack\Core\DataProvider::getBanks()`</code> method.
    <br>* @param float $amount Amount to be transferred.
    <br>* @param string $reference If specified, the field should be a unique identifier (in lowercase) for the object. Only -,_ and alphanumeric characters allowed.
    <br>* @param string $currency Three-letter ISO currency.
    <br>* @param string $remark The reason for the transfer.
  </p>

  <p>&nbsp;</p>
  
  <h3 style="text-decoration: underline;">Bulk transfer</h3>
  
  <p>
    Initiates a bulk transfer request.

    You need to disable the Transfers OTP requirement to use this endpoint.
  </p>

  <p>Example</p>
  
  <code>
      $transferResult = Paystack::initiateBulkTransfers($transfers, $currency);
      if ($transferResult->hasError()) {
        echo $transferResult->getMessage();
      } else {
        var_dump ($transferResult->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <p>
    <strong>$transfers is an array of <code>\Aweklin\Paystack\Models\PaymentTransfer`</code>, each containing: amount, recipient, and reference.</strong>
  </p>

  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Conclude transfer with OTP</h3>
  <p>
    Finalizes an initiated transfer with OTP.
  </p>

  <p>Example</p>
  
  <code>
      $transferResult = Paystack::completeTransferWithOTP($transferCode, $otp);
      if ($transferResult->hasError()) {
        echo $transferResult->getMessage();
      } else {
        var_dump ($transferResult->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Resend transfer OTP</h3>
  <p>
    Generates a new OTP and sends to customer in the event they are having trouble receiving one.
  </p>

  <p>Example</p>
  
  <code>
      $transferResult = Paystack::resendTransferOTP($transferCode);
      if ($transferResult->hasError()) {
        echo $transferResult->getMessage();
      } else {
        var_dump ($transferResult->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Disable transfer OTP</h3>
  <p>
    In the event that you want to be able to complete transfers programmatically without use of OTPs, this method helps disable that….with an OTP.

    Please note that this will send you an OTP and you are to call the `finalizeDisableOTP()` method to conclude operation.
  </p>

  <p>Example</p>
  
  <code>
      $transferResult = Paystack::disableOTP();
      if ($transferResult->hasError()) {
        echo $transferResult->getMessage();
      } else {
        var_dump ($transferResult->getData());
      }
    </code>
  
  
  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Complete disable transfer OTP</h3>
  <p>
    Finalizes the request to disable OTP on your transfers.
  </p>

  <p>Example</p>
  
  <code>
      $transferResult = Paystack::finalizeDisableOTP($otp);
      if ($transferResult->hasError()) {
        echo $transferResult->getMessage();
      } else {
        var_dump ($transferResult->getData());
      }
    </code>
  
  
  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Enable transfer OTP</h3>
  <p>
    In the event that a customer wants to stop being able to complete transfers programmatically, this endpoint helps turn OTP requirement back on.
  </p>

  <p>Example</p>
  
  <code>
      $transferResult = Paystack::enableOTP();
      if ($transferResult->hasError()) {
        echo $transferResult->getMessage();
      } else {
        var_dump ($transferResult->getData());
      }
    </code>
  
</div>
<div id="others">

  <p>&nbsp;</p>

  <p>&nbsp;</p>
  
  <h3 style="text-decoration: underline;">Listing your transfers</h3>
  <p>Return all transfers from inception till date.</p>
  
  
  <code>
      $transferList = Paystack::getTransfers();
      if ($transferList->hasError()) {
        echo $transferList->getMessage();
      } else {
        var_dump ($transferList->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  
  <h3 style="text-decoration: underline;">Listing your transfers by date range</h3>
  
  
  <code>
      $transferList = Paystack::getTransfersByDates($startDate, $endDate);
      if ($transferList->hasError()) {
        echo $transferList->getMessage();
      } else {
        var_dump ($transferList->getData());
      }
    </code>
  

  <p>&nbsp;</p>
  <h3 style="text-decoration: underline;">Get details of a transfer</h3>
  
  
  <code>
      $transferDetails = Paystack::getTransfer($id);
      if ($transferDetails->hasError()) {
        echo $transferDetails->getMessage();
      } else {
        var_dump ($transferDetails->getData());
      }
    </code>
  
  <p>&nbsp;</p>
</div>

<div id="others">
  <h1>Misc</h1>
  
  <h3 style="text-decoration: underline;">Get account balance</h3>
  
  
  <code>
      $result = Paystack::getBalance();
      if ($result->hasError()) {
        echo $result->getMessage();
      } else {
        var_dump ($result->getData());
      }
    </code>
  
  <p>&nbsp;</p>
  
  <h3 style="text-decoration: underline;">Get list of banks</h3>
  
  <p>Get a list of all Nigerian banks and their properties</p>

  
  <code>
      $result = Paystack::getBanks();
      if ($result->hasError()) {
        echo $result->getMessage();
      } else {
        var_dump ($result->getData());
      }
    </code>
  
  <p>&nbsp;</p>
  
  <h3 style="text-decoration: underline;">Get providers</h3>

  <p>Get a list of all providers for Dedicated NUBAN</p>

  
  <code>
      $result = Paystack::getProviders();
      if ($result->hasError()) {
        echo $result->getMessage();
      } else {
        var_dump ($result->getData());
      }
    </code>
  
  <p>&nbsp;</p>
  
  <h3 style="text-decoration: underline;">Validate BVN</h3>
  
  <p>Validates the given Bank Verification Number, BVN against the account number and bank code provided.</p>

  
  <code>
      $result = Paystack::verifyBVN(VALID_BVN, VALID_BANK_CODE_FOR_BVN_VERIFICATION, VALID_ACCOUNT_NUMBER);
      if ($result->hasError()) {
        echo $result->getMessage();
      } else {
        var_dump ($result->getData());
      }
    </code>
  
  <p>&nbsp;</p>
  
  <h3 style="text-decoration: underline;">Get account balance.</h3>
  
  <p>Get a customer's information by using the Bank Verification Number.</p>

  
  <code>
      $result = Paystack::getBVNDetails($bvn);
      if ($result->hasError()) {
        echo $result->getMessage();
      } else {
        var_dump ($result->getData());
      }
    </code>
  
  <p>&nbsp;</p>
  
  <h3 style="text-decoration: underline;">Get account balance.</h3>
  
  <p>Confirm an account belongs to the right customer.</p>

  
  <code>
      $result = Paystack::getAccountDetails(string $accountNumber, string $bankCode);
      if ($result->hasError()) {
        echo $result->getMessage();
      } else {
        var_dump ($result->getData());
      }
    </code>
  
  <p>&nbsp;</p>

  <h3 style="text-decoration: underline;">Adding transfer beneficiary</h3>
  
  <p>
    Creates a new recipient. A duplicate account number will lead to the retrieval of the existing record.
  </p>

  
  <code>
      $beneficiaryCreationResult = Paystack::createBeneficiary(VALID_ACCOUNT_NUMBER, VALID_BANK_CODE_FOR_BVN_VERIFICATION, VALID_ACCOUNT_NAME);
      if ($beneficiaryCreationResult->hasError()) {
        echo $beneficiaryCreationResult->getMessage();
      } else {
        var_dump ($beneficiaryCreationResult->getData());
      }
    </code>
  
  <p>&nbsp;</p>

  <h3 style="text-decoration: underline;">Updating transfer beneficiary</h3>
  
  <p>
    Updates recipient. A duplicate account number will lead to the retrieval of the existing record.
  </p>

  
  <code>
      $beneficiaryUpdateResult = Paystack::updateBeneficiary(VALID_BENEFICIARY_ID, VALID_ACCOUNT_NAME, VALID_EMAIL, 'A sample test account');
      if ($beneficiaryUpdateResult->hasError()) {
        echo $beneficiaryUpdateResult->getMessage();
      } else {
        var_dump ($beneficiaryUpdateResult->getData());
      }
    </code>
  
  <p>&nbsp;</p>

  <h3 style="text-decoration: underline;">Listing transfer beneficiaries</h3>
  
  <p>
    Returns all beneficiaries created from inception till date.
  </p>

  
  <code>
      $beneficiaries = Paystack::getBeneficiaries();
      if ($beneficiaries->hasError()) {
        echo $beneficiaries->getMessage();
      } else {
        var_dump ($beneficiaries->getData());
      }
    </code>
  
  <p>&nbsp;</p>

  <h3 style="text-decoration: underline;">Listing transfer beneficiaries created between two dates</h3>
  
  <p>
    Returns all beneficiaries created between two dates.
  </p>

  
  <code>
      $beneficiaries = Paystack::getBeneficiariesByDates($startDate, $endDate);
      if ($beneficiaries->hasError()) {
        echo $beneficiaries->getMessage();
      } else {
        var_dump ($beneficiaries->getData());
      }
    </code>
  
  <p>&nbsp;</p>
  
  <h3 style="text-decoration: underline;">Get details of a beneficiary.</h3>
  
  
  <code>
      $beneficiaryDetails = Paystack::getBeneficiary(VALID_BENEFICIARY_ID);
      if ($beneficiaryDetails->hasError()) {
        echo $beneficiaryDetails->getMessage();
      } else {
        var_dump ($beneficiaryDetails->getData());
      }
    </code>
  
  <p>&nbsp;</p>

</div>

## Testing ##

This project has been developed with TDD. Over 95% of the code has been unit tested and code base improved upon.
With the unit testing in place, developers of this API can equally learn how to use it simply by reading through the tests written.

To run the unit tests in this project, kindly open the config file inside the tests folder and set values for all the constants before running your tests. If you need some help on this, kindly contact <a href="mailto:support@aweklin.com" title="Send email to">support@aweklin.com</a>.

## Contributions ##

This project is open to professionals to contribute & report issues for us to make it better together.
Security issues should be reported privately, via email, to <a href="mailto:support@aweklin.com" title="Send email to">support@aweklin.com</a>.

## License ##

This project is under license from MIT. For more details, see the [LICENSE](LICENSE.md) file.


Made with :heart: by <a href="https://github.com/aweklin" target="_blank">Akeem Aweda</a>

&#xa0;

<a href="#top">Back to top</a>
