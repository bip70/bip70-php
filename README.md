BIP70 in PHP
=============

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

Much credit is due to the open source that make this package possible. 

 - [sop/x509](https://github.com/sop/x509) for X509, and some crypto utils
 - [rgooding/protobuf-php](https://github.com/rgooding/protobuf-php) for protobuf support
 - [composer/ca-bundle](https://github.com/composer/ca-bundle) for detecting the trusted certificate store, and providing a fallback
