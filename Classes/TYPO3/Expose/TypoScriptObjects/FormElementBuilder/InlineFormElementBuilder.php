<?php
namespace TYPO3\Expose\TypoScriptObjects\FormElementBuilder;

/*                                                                        *
 * This script belongs to the TYPO3.Expose package.              		  *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Render a Form section using the Form framework
 */
class InlineFormElementBuilder extends DefaultFormElementBuilder {
    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * Evaluate the collection nodes
     *
     * @return string
     */
    public function evaluate() {

    	$parentFormElement = $this->tsValue('parentFormElement');
		if (!($parentFormElement instanceof \TYPO3\Form\Core\Model\AbstractSection)) {
			throw new \Exception('TODO: parent form element must be a section-like element');
		}

		$annotations = $this->tsValue("propertyAnnotations");

		if (isset($annotations['TYPO3\Expose\Annotations\Ignore'])){
			return NULL;
		}

		$containerSection = $parentFormElement->createElement("container." . $this->tsValue('identifier'), $this->tsValue('formFieldType'));

		$classAnnotations = $this->reflectionService->getClassAnnotations($this->tsValue("className"));
		$propertyAnnotations = $this->reflectionService->getPropertyTagsValues($this->tsValue("className"), $this->tsValue("propertyName"));
		if (method_exists($containerSection, 'setAnnotations')){
			$containerSection->setAnnotations($propertyAnnotations);
		}

		$varTags = $this->reflectionService->getPropertyTagValues($this->tsValue("className"), $this->tsValue("propertyName"), 'var');
		#$containerSection->setDataType(ltrim(current($varTags), '\\'));

        $containerSection->setLabel($this->tsValue('label'));
        $namespace = $this->tsValue('identifier');
		if (isset($annotations["Doctrine\ORM\Mapping\ManyToMany"]) || isset($annotations["Doctrine\ORM\Mapping\OneToMany"])){
            preg_match("/<(.+)>/", $varTags[0], $matches);
			$className = $matches[1];
        	$objects = $this->tsValue('propertyValue');
            if(is_null($objects) || count($objects) < 1){
                $objects = array(new $className());
            }
            foreach ($objects as $key => $object) {
	            $itemSection = $containerSection->createElement($namespace . '.' . $key, $this->tsValue('formFieldType').'Item');
                $itemSection->setFormBuilder($this->tsValue("formBuilder"));
                $section = $this->tsValue("formBuilder")->createFormForSingleObject($itemSection, $object, $namespace . '.' . $key);
	            $section->setDataType($className);
            }
            return $containerSection;
        } else {
			$className = $this->tsValue("propertyType");
        	$object = $this->tsValue('propertyValue');
            if (is_null($object)) {
				$object = new $className();
            }
            $itemSection = $containerSection->createElement($namespace, $this->tsValue('formFieldType').'Item');
            $itemSection->setFormBuilder($this->tsValue("formBuilder"));
            $section = $this->tsValue("formBuilder")->createFormForSingleObject($itemSection, $object, $namespace);
            $itemSection->setDataType($className);
            return $containerSection;
        }

		return $containerSection;
    }
}
?>