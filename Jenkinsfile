pipeline {
	agent none
	stages {
		stage('PHP 7.1') {
			agent {
				label 'php-7.1'
			}
			docker { image 'php:7.1-alpine' }
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
				label 'php-7.2'
			}
			docker { image 'php:7.2-alpine' }
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