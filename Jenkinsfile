pipeline {
 	agent none
 	stages {
		stage('PHP 7.3') {
			agent {
				docker {
					image 'php:7.3-alpine'
					args '-u root --privileged'
				}
			}
			steps {
				sh 'apk add --no-cache git php7-phpdbg php7-xsl php7-gd php7-json php7-pdo'
				sh 'curl -sS https://getcomposer.org/installer | php'
				sh 'rm -f composer.lock'
				sh 'php composer.phar install --ignore-platform-reqs'
				sh 'php vendor/bin/robo lint'
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
				sh 'apk add --no-cache git php7-phpdbg php7-xsl php7-gd php7-json php7-pdo'
				sh 'curl -sS https://getcomposer.org/installer | php'
				sh 'rm -f composer.lock'
				sh 'php composer.phar install --ignore-platform-reqs'
				sh 'php vendor/bin/robo lint'
				sh 'phpdbg -qrr -- ./vendor/bin/phpunit --coverage-text --coverage-clover clover.xml --colors=never'
				step([
					$class: 'CloverPublisher',
					cloverReportDir: '',
					cloverReportFileName: 'clover.xml',
				])
			}
		}
 	}
 }