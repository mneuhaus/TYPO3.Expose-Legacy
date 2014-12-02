<?php
namespace TYPO3\Expose\Core\OptionsProvider;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Expose".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 */
abstract class AbstractOptionsProvider implements OptionsProviderInterface {

	/**
	 * @var array
	 */
	protected $propertySchema;

	/**
	 * @param array $annotations
	 */
	public function __construct($propertySchema = array()) {
		$this->propertySchema = $propertySchema;
	}

}

?>