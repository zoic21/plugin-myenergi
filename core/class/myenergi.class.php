<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class myenergi extends eqLogic {
  /*     * *************************Attributs****************************** */


  public static $_encryptConfigKey = array('myenergi::serial', 'myenergi::apikey');


  /*     * ***********************Methode static*************************** */



  /*     * *********************Méthodes d'instance************************* */



  /*     * **********************Getteur Setteur*************************** */
}

class myenergiCmd extends cmd {
  /*     * *************************Attributs****************************** */



  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  public function execute($_options = array()) {
  }

  /*     * **********************Getteur Setteur*************************** */
}
