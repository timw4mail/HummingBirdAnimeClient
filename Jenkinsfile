pipeline {
 	agent none
 	stages {
 		stage('setup') {
			agent any
			steps {
				sh 'curl -sS https://getcomposer.org/installer | php'
				sh 'rm -rf ./vendor'
				sh 'rm -f composer.lock'
				sh 'php composer.phar install --ignore-platform-reqs'
			}
 		}
		stage('PHP 8.2') {
			agent {
				docker {
					image 'php:8.2-cli-alpine'
					args '-u root --privileged'
				}
			}
			steps {
				sh 'apk add --no-cache git icu-dev'
				sh 'docker-php-ext-configure intl && docker-php-ext-install intl'
				sh 'php ./vendor/bin/phpunit --colors=never'
			}
		}
		stage('PHP 8.3') {
			agent {
				docker {
					image 'php:8.3-cli-alpine'
					args '-u root --privileged'
				}
			}
			steps {
				sh 'apk add --no-cache git icu-dev'
				sh 'docker-php-ext-configure intl && docker-php-ext-install intl'
				sh 'php ./vendor/bin/phpunit --colors=never'
			}
		}
		stage('Latest PHP') {
			agent {
				docker {
					image 'php:cli-alpine'
					args '-u root --privileged'
				}
			}
			steps {
				sh 'apk add --no-cache git icu-dev'
				sh 'docker-php-ext-configure intl && docker-php-ext-install intl'
				sh 'php ./vendor/bin/phpunit --colors=never'
			}
		}
		stage('Coverage') {
			agent any
			steps {
				sh 'php composer.phar run-script coverage'
				step([
					$class: 'CloverPublisher',
					cloverReportDir: '',
					cloverReportFileName: 'build/logs/clover.xml',
				])
				junit 'build/logs/junit.xml'
			}
		}
 	}
 }