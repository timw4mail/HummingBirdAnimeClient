<?php declare(strict_types=1);

use Robo\Tasks;

if ( ! function_exists('glob_recursive'))
{
	// Does not support flag GLOB_BRACE
	function glob_recursive($pattern, $flags = 0)
	{
		$files = glob($pattern, $flags);

		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
		{
			$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
		}

		return $files;
	}
}

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends Tasks {

	/**
	 * Directories used by analysis tools
	 *
	 * @var array
	 */
	protected $taskDirs = [
		'build/logs',
		'build/pdepend',
		'build/phpdox',
	];

	/**
	 * Directories to remove with the clean task
	 *
	 * @var array
	 */
	protected $cleanDirs = [
		'coverage',
		'docs',
		'phpdoc',
		'build/logs',
		'build/phpdox',
		'build/pdepend'
	];


	/**
	 * Do static analysis tasks
	 */
	public function analyze(): void
	{
		$this->prepare();
		$this->lint();
		$this->phploc(TRUE);
		$this->phpcs(TRUE);
		$this->dependencyReport();
		$this->phpcpdReport();
	}

	/**
	 * Run all tests, generate coverage, generate docs, generate code statistics
	 */
	public function build(): void
	{
		$this->analyze();
		$this->coverage();
		$this->docs();
	}

	/**
	 * Cleanup temporary files
	 */
	public function clean(): void
	{
		$cleanFiles = [
			'build/humbug.json',
			'build/humbug-log.txt',
		];
		array_map(static function ($file) {
			@unlink($file);
		}, $cleanFiles);

		// So the task doesn't complain,
		// make any 'missing' dirs to cleanup
		array_map(static function ($dir) {
			if ( ! is_dir($dir))
			{
				`mkdir -p {$dir}`;
			}
		}, $this->cleanDirs);

		$this->_cleanDir($this->cleanDirs);
		$this->_deleteDir($this->cleanDirs);
	}

	/**
	 * Run unit tests and generate coverage reports
	 */
	public function coverage(): void
	{
		$this->_run(['phpdbg -qrr -- vendor/bin/phpunit -c build']);
	}

	/**
	 * Generate documentation with phpdox
	 */
	public function docs(): void
	{
		$cmd_parts = [
			'vendor/bin/phpdox',
		];
		$this->_run($cmd_parts, ' && ');
	}

	/**
	 * Verify that source files are valid
	 */
	public function lint(): void
	{
		$files = $this->getAllSourceFiles();

		$chunks = array_chunk($files, 12);

		foreach($chunks as $chunk)
		{
			$this->parallelLint($chunk);
		}
	}

	/**
	 * Run the phpcs tool
	 *
	 * @param bool $report - if true, generates reports instead of direct output
	 */
	public function phpcs($report = FALSE): void
	{
		$report_cmd_parts = [
			'vendor/bin/phpcs',
			'--standard=./build/phpcs.xml',
			'--report-checkstyle=./build/logs/phpcs.xml',
		];

		$normal_cmd_parts = [
			'vendor/bin/phpcs',
			'--standard=./build/phpcs.xml',
		];

		$cmd_parts = ($report) ? $report_cmd_parts : $normal_cmd_parts;

		$this->_run($cmd_parts);
	}

	/**
	 * Run the phploc tool
	 *
	 * @param bool $report - if true, generates reports instead of direct output
	 */
	public function phploc($report = FALSE): void
	{
		// Command for generating reports
		$report_cmd_parts = [
			'vendor/bin/phploc',
			'--count-tests',
			'--log-csv=build/logs/phploc.csv',
			'--log-xml=build/logs/phploc.xml',
			'src',
			'tests'
		];

		// Command for generating direct output
		$normal_cmd_parts = [
			'vendor/bin/phploc',
			'--count-tests',
			'src',
			'tests'
		];

		$cmd_parts = ($report) ? $report_cmd_parts : $normal_cmd_parts;

		$this->_run($cmd_parts);
	}

	/**
	 * Create temporary directories
	 */
	public function prepare(): void
	{
		array_map([$this, '_mkdir'], $this->taskDirs);
	}

	/**
	 * Lint php files and run unit tests
	 */
	public function test(): void
	{
		$this->lint();

		$this->_run(['phpunit']);
	}

	/**
	 * Create pdepend reports
	 */
	protected function dependencyReport(): void
	{
		$cmd_parts = [
			'vendor/bin/pdepend',
			'--jdepend-xml=build/logs/jdepend.xml',
			'--jdepend-chart=build/pdepend/dependencies.svg',
			'--overview-pyramid=build/pdepend/overview-pyramid.svg',
			'src'
		];
		$this->_run($cmd_parts);
	}

	/**
	 * Get the total list of source files, including tests
	 *
	 * @return array
	 */
	protected function getAllSourceFiles(): array
	{
		$files = array_merge(
			glob_recursive('build/*.php'),
			glob_recursive('src/*.php'),
			glob_recursive('src/**/*.php'),
			glob_recursive('tests/*.php'),
			glob_recursive('tests/**/*.php'),
			glob('*.php')
		);

		sort($files);

		return $files;
	}

	/**
	 * Run php's linter in one parallel task for the passed chunk
	 *
	 * @param array $chunk
	 */
	protected function parallelLint(array $chunk): void
	{
		$task = $this->taskParallelExec()
			->timeout(5)
			->printed(FALSE);

		foreach($chunk as $file)
		{
			$task = $task->process("php -l {$file}");
		}

		$task->run();
	}

	/**
	 * Generate copy paste detector report
	 */
	protected function phpcpdReport(): void
	{
		$cmd_parts = [
			'vendor/bin/phpcpd',
			'--log-pmd build/logs/pmd-cpd.xml',
			'src'
		];
		$this->_run($cmd_parts);
	}

	/**
	 * Shortcut for joining an array of command arguments
	 * and then running it
	 *
	 * @param array $cmd_parts - command arguments
	 * @param string $join_on - what to join the command arguments with
	 */
	protected function _run(array $cmd_parts, $join_on = ' '): void
	{
		$this->taskExec(implode($join_on, $cmd_parts))->run();
	}
}