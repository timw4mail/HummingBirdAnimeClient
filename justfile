# Lists the available actions
default:
	@just --list

# Runs rector, showing what changes will be make
rector-dry-run:
	tools/vendor/bin/rector process --config=tools/rector.php --dry-run src

# Runs rector, and updates the files
rector:
	tools/vendor/bin/rector process --config=tools/rector.php src

# Check code formatting
check-fmt:
	tools/vendor/bin/php-cs-fixer fix --dry-run --verbose

# Fix code formatting
fmt:
	tools/vendor/bin/php-cs-fixer fix --verbose

# Run tests
test:
	composer run-script test

# Run tests, update snapshots
test-update:
	composer run-script test-update

# Run unit tests and generate test-coverage report
coverage:
	composer run-script coverage