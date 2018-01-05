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

	/** @var array Target directory(ies) */
	private $dir = [];

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

		// Verify and set the target directories
		if(!empty($options['d'])) {

			$dirs = explode(',', $options['d']);

			foreach($dirs as $dir) {

				if(!is_dir($dir)) {
					$this->log('warning', 'Dir ignored because not found: ' . $dir);
				} else {
					$this->dir[] = $dir;
				}

			}

		}
		if(empty($this->dir)) {
			$this->log('error', 'You need to include at least one directory');
		}

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

	$options = getopt('hd:', []);
	$options['run-from'] = 'cli';
	$projectStats = new ProjectStats($options);

} else {

}