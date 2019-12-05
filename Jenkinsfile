pipeline {
 	agent none
 	stages {
 		stage('PHP 7.1') {
 			agent {
 				docker {
 					image 'php:7.1-alpine'
 					args '-u root --privileged'
 				}
 			}
 			steps {
 				sh 'chmod +x ./build/docker_install.sh'
 				sh 'sh build/docker_install.sh'
 				sh 'apk add --no-cache php7-phpdbg'
 				sh 'curl -sS https://getcomposer.org/installer | php'
 				sh 'php composer.phar install --ignore-platform-reqs'
 				sh 'phpdbg -qrr -- ./vendor/bin/phpunit --coverage-text --colors=never'
 			}
 		}
 		stage('PHP 7.2') {
 			agent {
 				docker {
 					image 'php:7.2-alpine'
 					args '-u root --privileged'
 				}
 			}
 			steps {
 				sh 'chmod +x ./build/docker_install.sh'
 				sh 'sh build/docker_install.sh'
 				sh 'apk add --no-cache php7-phpdbg'
 				sh 'curl -sS https://getcomposer.org/installer | php'
 				sh 'php composer.phar install --ignore-platform-reqs'
 				sh 'phpdbg -qrr -- ./vendor/bin/phpunit --coverage-text --colors=never'
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
				sh 'chmod +x ./build/docker_install.sh'
				sh 'sh build/docker_install.sh'
				sh 'apk add --no-cache php7-phpdbg'
				sh 'curl -sS https://getcomposer.org/installer | php'
				sh 'php composer.phar install --ignore-platform-reqs'
				sh 'phpdbg -qrr -- ./vendor/bin/phpunit --coverage-text --colors=never'
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
				sh 'chmod +x ./build/docker_install.sh'
				sh 'sh build/docker_install.sh'
				sh 'apk add --no-cache php7-phpdbg'
				sh 'curl -sS https://getcomposer.org/installer | php'
				sh 'php composer.phar install --ignore-platform-reqs'
				sh 'phpdbg -qrr -- ./vendor/bin/phpunit --coverage-text --colors=never'
			}
		}
 	}
 }