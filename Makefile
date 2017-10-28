
phpcs:
		vendor/bin/phpcs -n --standard=PSR1,PSR2 src test/unit

phpcbf:
		vendor/bin/phpcbf -n --standard=PSR1,PSR2 src test/unit
