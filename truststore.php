<?php

use ASN1\Type\Primitive\Integer;
use Bip70\X509\TrustStoreLoader;

require "vendor/autoload.php";

$store = TrustStoreLoader::fromSystem();
