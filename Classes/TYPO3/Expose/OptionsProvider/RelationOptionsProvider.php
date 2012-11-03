<?php
namespace TYPO3\Expose\OptionsProvider;

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
 * OptionsProvider for related Beings
 *
 */
class RelationOptionsProvider extends \TYPO3\Expose\Core\OptionsProvider\AbstractOptionsProvider {

	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	* TODO: Document this Method! ( getOptions )
	*/
	public function getOptions() {
		return $this->persistenceManager->createQueryForType($this->getRelationClass())->execute();
	}

	public function getRelationClass() {
		if ($this->propertySchema['elementType'] !== NULL) {
			return $this->propertySchema['elementType'];
		}
		return $this->propertySchema['type'];
	}
}

?>