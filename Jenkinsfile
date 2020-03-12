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
				sh 'php ./vendor/bin/phpunit --colors=never'
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
				sh 'phpdbg -qrr -- ./vendor/bin/phpunit --coverage-clover build/logs/clover.xml --colors=never'
				step([
					$class: 'CloverPublisher',
					cloverReportDir: '',
					cloverReportFileName: 'build/logs/clover.xml',
				])
			}
		}
 	}
 }