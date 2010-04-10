<?php

namespace F3\Admin\Adapters;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Abstract validator
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @prototype
 */
abstract class AbstractAdapter implements AdapterInterface {
	/**
	 * @var \F3\Admin\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $helper;
	
	/**
	 * @var \F3\FLOW3\Package\PackageManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $packageManager;
	
	/**
	 * @var \F3\FLOW3\Reflection\ReflectionService
	 * @author Marc Neuhaus
	 * @inject
	 */
	protected $reflection;
	
	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $objectManager;
	
	public function transformToObject($being,$data,$target=null,$propertyMapper = null){
#		$item = $this->convertArray($this->request->getArgument("item"),$being);
		$data = $this->cleanUpItem($data);

		$arg = $this->objectManager->get("F3\Admin\Argument","item",$being);
		if($propertyMapper !== null)
			$arg->replacePropertyMapper($propertyMapper);
		if($target !== null)
			$arg->setTarget($target);
		$validator = $this->helper->getModelValidator($being);
		if(is_object($validator))
			$arg->setValidator($validator);
		$arg->setValue($data);

		$targetObject = $arg->getValue();

		$validationErrors = $arg->getValidator()->getErrors();

		$errors = array();
		if(count($validationErrors)>0){
			foreach ($validationErrors as $propertyError) {
				$errors[$propertyError->getPropertyName()] = array();
				foreach ($propertyError->getErrors() as $error) {
					$errors[$propertyError->getPropertyName()][] = $error->getMessage();
				}
			}
		}
		
		return array(
			"errors" => $errors,
			"object" => $targetObject
		);
	}
	
	public function cleanUpItem($item){
		foreach ($item as $key => $value) {
			if(is_array($value)){
				$item[$key] = $this->cleanUpItem($value);
			}
			if(empty($item[$key]) && $item[$key] !== false){
				unset($item[$key]);
			}
		}
		return $item;
	}
	
	public function getName($being){
		$parts = explode("\\",$being);
		return str_replace("_AOPProxy_Development","",end($parts));
	}

	public function getSetting($raw,$default = null,$path = "Widgets.Mapping"){
		$mappings = \F3\FLOW3\Reflection\ObjectAccess::getPropertyPath($this->settings,$path);

		if(isset($mappings[$raw])){
			return $mappings[$raw];
		}

		foreach ($mappings as $pattern => $widget) {
			if(preg_match("/".$pattern."/",$raw) > 0){
				return $widget;
			}
		}

		if($default !== null)
			return $default;

		return $raw;
    }

	public function convertValues($values,$properties){
		foreach ($values as $property => $value) {
			$values[$property] = $this->convertValue($value,$properties[$property]["var"][0],"storage");
		}
		return $values;
	}

	public function convertValue($value,$type,$target="presentation"){
		$widgetType = $this->getSetting($type,"TextField");
		
		if($target == "presentation"){
			switch ($type) {
				case 'string':
					return strval($value);
				case 'integer':
					return intval($value);
				case 'float':
					return floatval($value);
				case 'boolean':
					return $value ? "true" : "false";

				default:
					$callback = $this->getCallback($this->getSetting($type,null,"Conversions.Presentation"));
					if(!empty($callback))
						return call_user_func($callback,$value);
#					echo "Type:".$type."<br />";
					return $value;
					break;
			}
		}else{
			switch ($type) {
				case 'string':
					return strval($value);
				case 'integer':
					return intval($value);
				case 'float':
					return floatval($value);
				case 'boolean':
					return $value == "true" ? true : false;

				default:
					$callback = $this->getCallback($this->getSetting($type,null,"Conversions.Storage"));
					if(!empty($callback))
						return call_user_func($callback,$value);
#					echo "Type:".$type."<br />";
					return $value;
					break;
			}
		}
	}

	public function getCallback($raw){
		$callback = null;

		if(function_exists($raw)){
			$callback = $raw;
		}elseif(stristr($raw,"::")){
			$callback = $raw;
		}elseif(stristr($raw,"->")){
			$parts = explode("->",$raw);

			if($parts[0] == __CLASS__ || $parts[0] == "self" || $parts[0] == "this"){
				$callback = array(
					$this,
					$parts[1]
				);
			}elseif(class_exists($parts[0])){
				$callback = array(
					$this->objectManager->getObject($parts[0]),
					$parts[1]
				);
			}
		}

		return $callback;
	}
	
	
	## Conversion Functions
	public function dateTimeToString($datetime){		
		if(is_object($datetime)){
			$string = date("l, F d, Y h:m:s A",$datetime->getTimestamp());
			return $string;
		}
	}
	
	public function stringToDateTime($string){
		$datetime = new \DateTime($string);
		return $datetime;
	}
	
	public function identifierToModel($identifier){
		if(!empty($identifier)){
			return $this->persistenceManager->getObjectByIdentifier($identifier);
		}
	}
	
	public function modelToIdentifier($model){
		if(is_object($model)){
			return array(array(
				"id" => $this->persistenceManager->getIdentifierByObject($model),
				"name" => $model->__toString()
			));
		}
	}
	
	public function identifiersToSplObjectStorage($identifiers){
		$spl = new \SplObjectStorage();
		foreach ($identifiers as $identifier) {
			$spl->attach($this->identifierToModel($identifier));
		}
		return $spl;
	}
	
	public function splObjectStorageToIdentifiers($spl){
		$identifiers = array();
		if(count($spl)>0){
			foreach ($spl as $model) {
				$identifiers[] = current($this->modelToIdentifier($model));
			}
		}
		return $identifiers;
	}
}

?>