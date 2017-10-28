checkdeps:
		if [ ! -d vendor ] || [ ! -f composer.lock ]; then composer install; else echo "Already have dependencies"; fi

pretest: checkdeps

phpcs: pretest
		vendor/bin/phpcs -n --standard=PSR1,PSR2 src test/unit

phpcbf: pretest
		vendor/bin/phpcbf -n --standard=PSR1,PSR2 src test/unit

phpunit-ci: pretest
		vendor/bin/phpunit --coverage-text --coverage-text --coverage-clover=build/coverage.clover

ocular:
                wget https://scrutinizer-ci.com/ocular.phar

ifdef OCULAR_TOKEN
scrutinizer: ocular
                @php ocular.phar code-coverage:upload --format=php-clover build/coverage.clover --access-token=$(OCULAR_TOKEN);
else
scrutinizer: ocular
                php ocular.phar code-coverage:upload --format=php-clover build/coverage.clover;
endif

