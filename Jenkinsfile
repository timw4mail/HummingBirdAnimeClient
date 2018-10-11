pipeline {
	agent none
	stages {
		stage('PHP 7.1') {
			agent {
				docker { image 'php:7.1-alpine' }
				label 'php-7.1'
			}
			steps {
				sh 'build/docker_install.sh > /dev/null'
				'apk add --no-cache php7-phpdbg'
				'curl -sS https://getcomposer.org/installer | php'
				'php composer.phar install --ignore-platform-reqs'
				'phpdbg -qrr -- ./vendor/bin/phpunit --coverage-text --colors=never'
			}
		}
		stage('PHP 7.2') {
			agent {
				docker { image 'php:7.2-alpine' }
				label 'php-7.2'
			}
			steps {
				sh 'build/docker_install.sh > /dev/null'
				'apk add --no-cache php7-phpdbg'
				'curl -sS https://getcomposer.org/installer | php'
				'php composer.phar install --ignore-platform-reqs'
				'phpdbg -qrr -- ./vendor/bin/phpunit --coverage-text --colors=never'
			}
		}
	}
}