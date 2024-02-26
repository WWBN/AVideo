OpenPGP.php Examples
====================

The scripts in this folder show how to use this library to perform various tasks
such as [generating a new key](keygen.php), [signing a message](sign.php), and
[verifying a message](verify.php) that has been signed.

To use these examples, make sure [`phpseclib`](http://phpseclib.sourceforge.net/) is available. You can install it
using [Composer](https://getcomposer.org/):

```sh
git clone https://github.com/singpolyma/openpgp-php.git # Clone the repository.
cd openpgp-php
composer install # Use Composer to install the requirements.
```

Once Composer has installed the requirements, run the examples using PHP:

```sh
# Generate a new OpenPGP key; see the `keygen.php` file for parameters.
php ./examples/keygen.php > mykey.gpg
```
