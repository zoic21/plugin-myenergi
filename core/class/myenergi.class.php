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

  public static function cron5() {
    self::sync();
  }

  public static function request($_path) {
    $url = 'https://s' . config::byKey('myenergi::server', 'myenergi') . '.myenergi.net/' . trim($_path, '/');
    $request_http = new com_http($url, config::byKey('myenergi::serial', 'myenergi'), config::byKey('myenergi::apikey', 'myenergi'));
    $request_http->setCURLOPT_HTTPAUTH(CURLAUTH_DIGEST);
    $request_http->setHeader(array(
      'Content-Type: application/json'
    ));
    log::add('myenergi', 'debug', 'Call url ' . $url);
    $return = json_decode(trim($request_http->exec(20)), true);
    log::add('myenergi', 'debug', 'Results ' . json_encode($return));
    return $return;
  }

  public static function sync() {
    $datas  = self::request('cgi-jstatus-*');
    foreach ($datas as $data) {
      if (isset($data['eddi'])) {
        foreach ($data['eddi'] as $eddi) {
          $eqLogic = self::byLogicalId($eddi['sno'], 'myenergi');
          if (!is_object($eqLogic)) {
            $eqLogic = new self();
            $eqLogic->setLogicalId($eddi['sno']);
            $eqLogic->setName('Eddi - ' . $eddi['sno']);
            $eqLogic->setEqType_name('myenergi');
            $eqLogic->setIsVisible(1);
            $eqLogic->setIsEnable(1);
            $eqLogic->setConfiguration('device', 'eddi');
          }
          $eqLogic->setConfiguration('firmware', $eddi['fwv']);
          $eqLogic->save();

          foreach ($eddi as $key => $value) {
            $eqLogic->checkAndUpdateCmd($key, $value);
          }
        }
      }
      if (isset($data['zappi'])) {
        foreach ($data['zappi'] as $zappi) {
          $eqLogic = self::byLogicalId($zappi['sno'], 'myenergi');
          if (!is_object($eqLogic)) {
            $eqLogic = new self();
            $eqLogic->setLogicalId($zappi['sno']);
            $eqLogic->setName('Zappi - ' . $zappi['sno']);
            $eqLogic->setEqType_name('myenergi');
            $eqLogic->setIsVisible(1);
            $eqLogic->setIsEnable(1);
            $eqLogic->setConfiguration('device', 'zappi');
          }
          $eqLogic->setConfiguration('firmware', $zappi['fwv']);
          $eqLogic->save();

          if (isset($zappi['che'])) {
            $previousChe = $eqLogic->getCmd('info', 'che')->execCmd();
            $consumption = $eqLogic->getCmd('info', 'consumption');
            $prevConsumption = $consumption->execCmd();
            if (date('Y-m-d', strtotime($consumption->getValueDate())) != date('Y-m-d')) {
              $prevConsumption = 0;
            }
            if ($previousChe != $zappi['che'] && $zappi['che'] > 0) {
              $prevConsumption += ($previousChe < $zappi['che']) ? ($zappi['che'] - $previousChe) : $zappi['che'];
            }
            $eqLogic->checkAndUpdateCmd('consumption', $prevConsumption);
          }
          foreach ($zappi as $key => $value) {
            $eqLogic->checkAndUpdateCmd($key, $value);
          }
        }
      }
    }
  }


  public static function devicesParameters($_device = '') {
    $return = array();
    foreach (ls(dirname(__FILE__) . '/../config/devices/', '*.json') as $file) {
      try {
        $content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $file);
        $return[str_replace('.json', '', $file)] = is_json($content, array());
      } catch (Exception $e) {
      }
    }
    if (isset($_device) && $_device != '') {
      if (isset($return[$_device])) {
        return $return[$_device];
      }
      return array();
    }
    return $return;
  }

  /*     * *********************Méthodes d'instance************************* */

  public function postSave() {
    if ($this->getConfiguration('applyDevice') != $this->getConfiguration('device')) {
      $this->applyModuleConfiguration();
    }
    $cmd = $this->getCmd('action', 'refresh');
    if (!is_object($cmd)) {
      $cmd = new philipsHueCmd();
      $cmd->setName(__('Rafraichir', __FILE__));
      $cmd->setEqLogic_id($this->getId());
      $cmd->setIsVisible(1);
      $cmd->setLogicalId('refresh');
    }
    $cmd->setType('action');
    $cmd->setSubtype('other');
    $cmd->save();
  }

  public function applyModuleConfiguration() {
    $this->setConfiguration('applyDevice', $this->getConfiguration('device'));
    $this->save(true);
    if ($this->getConfiguration('device') == '') {
      return true;
    }
    $device = self::devicesParameters($this->getConfiguration('device'));
    if (!is_array($device)) {
      return true;
    }
    $this->import($device);
  }

  public function getImgFilePath() {
    if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $this->getConfiguration('device') . '.png')) {
      return $this->getConfiguration('device') . '.png';
    }
    return false;
  }

  public function getImage() {
    $imgpath = $this->getImgFilePath();
    if ($imgpath === false) {
      return 'plugins/myenergi/plugin_info/myenergi_icon.png';
    }
    return 'plugins/myenergi/core/config/devices/' . $imgpath;
  }



  /*     * **********************Getteur Setteur*************************** */
}

class myenergiCmd extends cmd {
  /*     * *************************Attributs****************************** */



  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  public function execute($_options = array()) {
    if ($this->getLogicalId() == 'refresh') {
      myenergi::sync();
      return;
    }
    $eqLogic = $this->getEqLogic();
    $replace = array(
      '#serial#' => $eqLogic->getLogicalId()
    );
    switch ($this->getSubType()) {
      case 'slider':
        $replace['#slider#'] = round(floatval($_options['slider']), 2);
        break;
      case 'select':
        $replace['#select#'] = $_options['select'];
        break;
      case 'message':
        $replace['#title#'] = $_options['title'];
        $replace['#message#'] = $_options['message'];
        if ($_options['message'] == '' && $_options['title'] == '') {
          throw new Exception(__('Le message et le sujet ne peuvent pas être vide', __FILE__));
        }
        break;
    }
    myenergi::request(str_replace(array_keys($replace), $replace, $this->getLogicalId()));
    myenergi::sync();
  }

  /*     * **********************Getteur Setteur*************************** */
}
