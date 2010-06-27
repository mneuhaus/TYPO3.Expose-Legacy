<?php

namespace F3\Admin\Actions;

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
 * @scope prototype
 */
class CreateAction extends AbstractAction {
    /**
     * Function to Check if this Requested Action is supported
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function canHandle($being, $action = null, $id = false){
        return !$id;
    }

    /**
     * The Name of this Action
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function getClass(){
        return "ui-icon ui-button-w32-round_plus";
    }
    
    /**
     * The Name of this Action
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function __toString(){
        return "Create";
    }

    /**
     * Create objects
     *
     * @param string $being
     * @param array $ids
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function execute($being, $ids = null){
        $object = $this->adapter->getBeing($being);

		if($this->request->hasArgument("create")){
			$result = $this->adapter->createObject($being,$this->request->getArgument("item"));
            $errors = $result["errors"];
			if(empty($errors)){
				$arguments = array(
                    "being"=>$being,
                    "adapter" => get_class($this->adapter)
                );
				$this->controller->redirect('list',NULL,NULL,$arguments);
                #return array('list',NULL,NULL,$arguments);
			}else{
#				foreach ($attributeSets as $set => $attributes) {
#					foreach ($attributes as $key => $attribute) {
#						if(array_key_exists($attribute["name"],$errors)){
#							$attributeSets[$set][$key]["error"] = $errors[$attribute["name"]];
#						}
#					}
#				}
			}
		}

		$this->view->assign("being",$object);
    }
}

?>