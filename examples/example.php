<?php

use Bip70\Client\GuzzleHttpClient;
use Bip70\Protobuf\Proto\PaymentDetails;
use Bip70\X509\RequestValidation;
use Bip70\X509\TrustStoreLoader;
use X509\CertificationPath\PathValidation\PathValidationConfig;

require __DIR__ . "/../vendor/autoload.php";

if ($argc < 2) {
    die("Missing url");
}

$validator = new RequestValidation(PathValidationConfig::defaultConfig(), TrustStoreLoader::fromSystem());
$client = new GuzzleHttpClient();
$request = $client->getRequest($argv[1], $validator);

$details = new PaymentDetails();
$details->parse($request->request()->getSerializedPaymentDetails());

foreach ($details->getOutputsList() as $i => $detail) {
    $script = bin2hex($detail->getScript());
    echo sprintf("Output %d [value: %d script: %s]\n", $i, $detail->getAmount(), $script);
}
