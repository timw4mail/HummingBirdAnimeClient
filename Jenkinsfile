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
		stage('PHP 8') {
			agent {
				docker {
					image 'php:8-cli-alpine'
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
					image 'php:cli-alpine'
					args '-u root --privileged'
				}
			}
			steps {
				sh 'apk add --no-cache git'
				sh 'php ./vendor/bin/phpunit --colors=never'
			}
		}
		stage('Code Cleanliness') {
			agent {
				docker {
					image 'php:8-cli-alpine'
					args '-u root --privileged'
				}
			}
			steps {
				sh "php ./vendor/bin/phpstan analyse src/ -c phpstan.neon -n --no-ansi --no-progress --error-format=checkstyle > build/logs/checkstyle.xml"
				recordIssues(tools: [checkstyle(reportEncoding: 'UTF-8')])
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