pipeline {
 	agent none
 	stages {
 		stage('setup') {
			agent any
			steps {
				sh 'curl -sS https://getcomposer.org/installer | php'
				sh 'rm -rf ./vendor'
				sh 'rm -f .phpunit.result.cache'
				sh 'rm -f composer.lock'
				sh 'php composer.phar install --ignore-platform-reqs'
			}
 		}
		stage('PHP 8.4') {
			agent {
				docker {
					image 'php:8.4-cli'
				}
			}
			steps {
				sh 'apt-get update \
					&& apt-get install -yy libzip-dev build-essential git \
					&& docker-php-source extract \
					&& docker-php-ext-install zip \
					&& pecl install pcov \
					&& docker-php-ext-enable pcov \
					&& docker-php-source delete'
				sh 'php composer.phar run-script coverage'
			}
		}
		stage('PHP 8.5') {
			agent {
				docker {
					image 'php:8.5-cli'
				}
			}
			steps {
				sh 'apt-get update \
					&& apt-get install -yy libzip-dev build-essential git \
					&& docker-php-source extract \
					&& docker-php-ext-install zip \
					&& pecl install pcov \
					&& docker-php-ext-enable pcov \
					&& docker-php-source delete'
				sh 'php composer.phar run-script coverage'
			}
		}
		stage('Coverage') {
			agent {
				docker {
					image 'php-cli'
				}
			}
			steps {
				sh 'apt-get update \
					&& apt-get install -yy libzip-dev build-essential git \
					&& docker-php-source extract \
					&& docker-php-ext-install zip \
					&& pecl install pcov \
					&& docker-php-ext-enable pcov \
					&& docker-php-source delete'
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