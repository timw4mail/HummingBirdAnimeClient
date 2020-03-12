pipeline {
 	agent none
 	stages {
 		stage('setup') {
			agent {
				docker {
					image 'php:alpine'
					args '-u root --privileged'
				}
			}
			steps {
				sh 'apk add --no-cache git'
				sh 'curl -sS https://getcomposer.org/installer | php'
				sh 'rm -f composer.lock'
				sh 'php composer.phar install --ignore-platform-reqs'
			}
 		}
		stage('PHP 7.3') {
			agent {
				docker {
					image 'php:7.3-alpine'
					args '-u root --privileged'
				}
			}
			steps {

				sh 'apk update'
				sh 'apk add --no-cache git php7-phpdbg'
				sh 'phpdbg -dmemory_limit=2g  -qrr -- ./vendor/bin/phpunit --coverage-text --coverage-clover clover.xml --colors=never'
				step([
					$class: 'CloverPublisher',
					cloverReportDir: '',
					cloverReportFileName: 'build/logs/clover.xml',
				])
			}
		}
		stage('PHP 7.4') {
			agent {
				docker {
					image 'php:7.4-alpine'
					args '-u root --privileged'
				}
			}
			steps {
				sh 'php ./vendor/bin/phpunit --colors=never'
			}
		}
 	}
 }