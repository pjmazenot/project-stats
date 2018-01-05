<?php
/**
 * Generate code statistics for one or more projects
 *
 * @author Pierre-Julien Mazenot <pj.mazenot@gmail.com>
 * @link   https://github.com/pjmazenot/project-stats
 */

/**
 * Class ProjectStats
 */
final class ProjectStats {

	/** @var string Source of the script call (cli) */
	private $runFrom;

	/** @var array Options */
	private $options;

	/**
	 * ProjectStats constructor.
	 *
	 * @param array $options
	 */
	public function __construct($options) {

		// Verify and set the script caller
		if(!empty($options['run-from']) && in_array($options['run-from'], ['cli'])) {
			$this->runFrom = $options['run-from'];
		} else {
			$this->log('error', 'Caller not supported');
		}

		// Set options
		$this->options = $options;

	}

	/**
	 * Log an message
	 *
	 * With the CLI if a message has the $level == "error" the process is stopped
	 *
	 * @param string $level (error,warning,info)
	 * @param string $message
	 */
	private function log($level, $message) {

		if($this->runFrom == 'cli') {

			if(isset($this->options['h'])) {
				echo '[' . strtoupper($level) . '] ' . $message . PHP_EOL;
			}

			if($level == 'error') {
				die;
			}

		}

	}

}

// CLI
if(php_sapi_name() == 'cli') {

	$options = [];
	$options['run-from'] = 'cli';
	$projectStats = new ProjectStats($options);

} else {

}