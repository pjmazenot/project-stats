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

	/** @var array Options */
	private $options;

	/**
	 * ProjectStats constructor.
	 *
	 * @param array $options
	 */
	public function __construct($options) {

		// Set options
		$this->options = $options;

	}

}