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
		stage('PHP 7.3') {
			agent {
				docker {
					image 'php:7.3-alpine'
					args '-u root --privileged'
				}
			}
			steps {
				sh 'apk add --no-cache git'
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
				sh 'apk add --no-cache git'
				sh 'php ./vendor/bin/phpunit --colors=never'
			}
		}
		stage('Latest PHP') {
			agent {
				docker {
					image 'php:alpine'
					args '-u root --privileged'
				}
			}
			steps {
				sh 'apk add --no-cache git'
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
			}
		}
 	}
 }