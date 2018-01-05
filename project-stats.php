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

	/** @var array Excluded files by extension */
	private $excludedExt = [];

	/** @var array Excluded files by name */
	private $excludedFileNames = ['.DS_Store'];

	/** @var array Excluded files by path */
	private $excludedFilePaths = [];

	/** @var array Excluded directories by name */
	private $excludedDirNames = ['.git', 'node_modules', '.idea', '.nbproject'];

	/** @var array Excluded directories by path */
	private $excludedDirPaths = [];

	/** @var bool Flag used to detect multi-line comments*/
	private $commentStarted = false;

	/** @var array Project stats */
	private $stats = [
		'included-dirs' => 0,
		'excluded-dirs' => 0, // Excluded by `--exclude-dirs`
		'included-files' => 0,
		'excluded-files' => 0, // Excluded by `--exclude-files-ext` and `--exclude-files`
		'code-lines' => 0,
		'empty-lines' => 0,
		'comment-lines' => 0,
		'chars' => 0,
		'size' => 0,
	];

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

		// Set the excluded extensions
		if(!empty($options['exclude-files-ext'])) {
			$this->excludedExt = explode(',', $options['exclude-files-ext']);
		}

		// Set the excluded file names
		if(!empty($options['exclude-files'])) {

			$files = explode(',', $options['exclude-files']);

			foreach($files as $excludedFile) {

				if(strpos($excludedFile, '**/') === 0) {
					$this->excludedFileNames[] = substr($excludedFile, 3);
				} else {
					$this->excludedFilePaths[] = $excludedFile;
				}

			}

		}

		// Set the excluded directories
		if(!empty($options['exclude-dirs'])) {

			$dirs = explode(',', $options['exclude-dirs']);

			foreach($dirs as $excludedDir) {

				if(strpos($excludedDir, '**/') === 0) {
					$this->excludedDirNames[] = substr($excludedDir, 3);
				} else {
					$this->excludedDirPaths[] = $excludedDir;
				}

			}

		}

	}

	/**
	 * Generate project stats
	 */
	public function generateStats() {

		$this->log('info', 'Generating project stats...');

		foreach($this->dir as $dir) {
			$this->processDir($dir);
		}

		$this->log('info', 'Generation completed');

	}

	/**
	 * Process a directory
	 *
	 * @param string $dir Directory path
	 */
	private function processDir($dir) {

		// Clean dir name
		$dir = str_replace('//', '/', $dir);

		// Check if the name is excluded
		$dirName = basename($dir);
		if(in_array($dirName, $this->excludedDirNames)) {

			$this->log('history', 'Skipping dir: ' . $dir);
			$this->stats['excluded-dirs']++;
			return;

		}

		// Check if the path is excluded
		foreach($this->excludedDirPaths as $excludedDirPath) {

			if(preg_match('/' . preg_quote($excludedDirPath, '/') . '\/?$/', $dir)) {

				$this->log('history', 'Skipping dir: ' . $dir);
				$this->stats['excluded-dirs']++;
				return;

			}

		}

		// Else process the directory
		if ($handle = opendir($dir)) {

			$this->log('history', 'Processing dir: ' . $dir);
			$this->stats['included-dirs']++;

			while (($entry = readdir($handle)) !== false) {

				// Skip current and parent folder
				if ($entry == '.' || $entry == '..') {
					continue;
				}

				// Process every folders and files in the current directory
				if(is_dir($dir . '/' . $entry)) {
					$this->processDir($dir . '/' . $entry);
				} else {
					$this->processFile($dir . '/' . $entry);
				}

			}

			closedir($handle);

		} else {
			$this->log('warning', 'Unable to open dir: ' . $dir);
		}

	}

	/**
	 * Process a file
	 *
	 * @param string $file File path
	 */
	private function processFile($file) {

		// Check if the extension is excluded
		$ext = strrchr($file, '.');
		if(!empty($ext) && in_array(substr($ext, 1), $this->excludedExt)) {

			$this->log('history', 'Skipping file by extension: ' . $file);
			$this->stats['excluded-files']++;
			return;

		}

		// Check if the name is excluded
		$fileName = basename($file);
		if(in_array($fileName, $this->excludedFileNames)) {

			$this->log('history', 'Skipping file by name: ' . $file);
			$this->stats['excluded-files']++;
			return;

		}

		// Check if the path is excluded
		foreach($this->excludedFilePaths as $excludedFilePath) {

			if(preg_match('/' . preg_quote($excludedFilePath, '/') . '$/', $file)) {

				$this->log('history', 'Skipping file: ' . $file);
				$this->stats['excluded-files']++;
				return;

			}

		}

		// Open the file in read mode
		if ($handle = fopen($file, 'r')) {

			$this->log('history', 'Processing file: ' . $file);
			$this->stats['included-files']++;
			$this->stats['size'] += filesize($file);

			// Process every lines of the file
			while (($line = fgets($handle)) !== false) {
				$this->processLine($line);
			}

			fclose($handle);

		} else {
			$this->log('warning', 'Unable to open file: ' . $file);
		}

	}

	/**
	 * Process a line
	 *
	 * @param string $line Line content
	 */
	private function processLine($line) {

		// Increment the character count
		$this->stats['chars'] += strlen($line);

		// Get a version of the line without spaces, line breaks and tabs
		$cleanLine = trim($line);

		if(empty($cleanLine)) {

			// Increment the empty line count when the line is empty
			$this->stats['empty-lines']++;

		} else {

			// Check if the line is a comment and increment the comment line count if it's and inline comment
			if(
				substr($line, 0, 1) == '#'
				|| substr($line, 0, 2) == '//'
			) {
				$this->stats['comment-lines']++;
				return;
			}

			// Check if the line is a block comment start and increment the comment line count if it's the case
			if(substr($line, 0, 2) == '/*') {

				$this->stats['comment-lines']++;
				$this->commentStarted = true;
				return;

			}

			// Count multi-line comment lines
			if($this->commentStarted) {

				$this->stats['comment-lines']++;

				// Check if the line is a comment end
				if(strpos($line, '*/')) {
					$this->commentStarted = false;
				}

				return;

			}

			// Increment the code line count
			$this->stats['code-lines']++;

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

	$options = getopt('hd:', [
		'exclude-files::',
		'exclude-files-ext::',
		'exclude-dirs::',
	]);
	$options['run-from'] = 'cli';
	$projectStats = new ProjectStats($options);
	$projectStats->generateStats();

} else {

}