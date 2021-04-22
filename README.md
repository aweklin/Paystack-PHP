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
- [Verifications](#verifications)
- [Misc](#others)


<div id="initializingTheLibrary">
  <h1>Initializing the Library</h1>
  <p>
    Before you can use this library, you first need to initialize it in your program entry point, perhaps the index.php file. Simply use this line below to initialize the library.
  </p>
  
  <p>
    First reference the namespace at the top of your index file<br>
    <blockquote>
      <code>
        use Aweklin\Paystack\Paystack;
      </code>
    </blockquote>
  </p>
  <p>
    Then, initialize the API with your secrete key
    <blockquote>
      <code>
        Paystack::initialize('your_api_secrete_key_here');
      </code>
    </blockquote>
  </p>
  <p>
    <br><strong>NB:</strong>
    For now, other parts of the documentation is under development. However, you can refer to the tests written for this API to learn how to use it or read through the documentations provided for the API.
    <br>
    You can send an email to <a href="mailto:support@aweklin.com" title="Send email to">support@aweklin.com</a> for quick support before the rest of the documentation is released.
  </p>
</div>
<div id="transactions">

</div>
<div id="refunds">

</div>
<div id="transfers">

</div>
<div id="verifications">

</div>
<div id="others">
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
