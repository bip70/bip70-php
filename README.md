BIP70 in PHP
=============

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bip70/bip70-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bip70/bip70-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/bip70/bip70-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bip70/bip70-php/?branch=master)

[![Latest Stable Version](https://poser.pugx.org/bip70/bip70/v/stable.png)](https://packagist.org/packages/bip70/bip70)
[![Total Downloads](https://poser.pugx.org/bip70/bip70/downloads.png)](https://packagist.org/packages/bip70/bip70)
[![Latest Unstable Version](https://poser.pugx.org/bip70/bip70/v/unstable.png)](https://packagist.org/packages/bip70/bip70)
[![License](https://poser.pugx.org/bip70/bip70/license.png)](https://packagist.org/packages/bip70/bip70)

This package provides a pure PHP interface to [BIP70](https://github.com/bitcoin/bips/blob/master/bip-0070.mediawiki). 

It exposes classes for:
 * X509 routines
   - loading a trusted certificate store
   - CA chain validation of X509Certificates
   - X509 signature validation of Signature and X509Certificate
 * for extracting, parsing, and manipulating protobufs
 * for downloading and fully validating a payment request over HTTP(s)
 * creating (un)signed payment requests

Note: this library should not be considered stable until the v1.0.0 release. 
Outstanding issues are tracked using the v1.0.0 milestone.

## Contributing

For contributing guidelines, please see [CONTRIBUTING.md](CONTRIBUTING.md)

## Credits

Much credit is due to the open source software that make this package possible. 

 - [sop/x509](https://github.com/sop/x509) for X509, and some crypto utils
 - [rgooding/protobuf-php](https://github.com/rgooding/protobuf-php) for protobuf support
 - [composer/ca-bundle](https://github.com/composer/ca-bundle) for detecting the trusted certificate store, and providing a fallback
