# Lists the available actions
default:
	@just --list

# -------------------------------------------------------------------
# Front-end stuff
# -------------------------------------------------------------------

# Builds/optimizes JS and CSS
build:
	cd frontEndSrc && npm run build && cd ..

# Builds/optimizes CSS
css:
	composer run-script build:css

# Builds/optimizes JS
js:
	composer run-script build:js

# -------------------------------------------------------------------
# Code Quality and Formatting
# -------------------------------------------------------------------

# Check code formatting
check-fmt:
	tools/vendor/bin/php-cs-fixer fix --dry-run --verbose

# Fix code formatting
fmt:
	tools/vendor/bin/php-cs-fixer fix --verbose

# Runs phpstan code check
phpstan:
	composer run-script phpstan

# Runs rector, showing what changes will be make
rector-dry-run:
	tools/vendor/bin/rector process --config=tools/rector.php --dry-run src tests

# Runs rector, and updates the source files
rector:
	tools/vendor/bin/rector process --config=tools/rector.php src tests

# -------------------------------------------------------------------
# Testing
# -------------------------------------------------------------------

# Run tests
test:
	composer run-script test

# Run tests, update snapshots
test-update:
	composer run-script test-update

# Run unit tests and generate test-coverage report
coverage:
	composer run-script coverage

# -------------------------------------------------------------------
# Misc
# -------------------------------------------------------------------

# Update the per-file header comments
update-headers:
	php tools/update_header_comments.php