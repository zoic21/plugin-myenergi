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

$("body").delegate(".eqLogicAction[data-action=sync]", 'click', function (){
  $.ajax({// fonction permettant de faire de l'ajax
    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/myenergi/core/ajax/myenergi.ajax.php", // url du fichier php
    data: {
        action: "sync",
    },
    dataType: 'json',
    error: function(request, status, error) {
        handleAjaxError(request, status, error);
    },
    success: function(data) { // si l'appel a bien fonctionné
        if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
        }
        $('#div_alert').showAlert({message: '{{Synchronisation OK}}', level: 'success'});
        window.location.reload();
    }
});
});

/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
})


$('.eqLogicAttr[data-l1key=configuration][data-l2key=device]').on('change', function () {
    if($('.eqLogicAttr[data-l1key=configuration][data-l2key=device]').value() == ''){
      $('#img_device').attr("src",'plugins/myenergi/plugin_info/myenergi_icon.png');
      return;
    }
    $.ajax({
      type: "POST",
      url: "plugins/myenergi/core/ajax/myenergi.ajax.php",
      data: {
        action: "getImageModel",
        model: $('.eqLogicAttr[data-l1key=configuration][data-l2key=device]').value(),
      },
      dataType: 'json',
      global: false,
      error: function (request, status, error) {
        handleAjaxError(request, status, error);
      },
      success: function (data) {
        if (data.state != 'ok') {
          $('#div_alert').showAlert({message: data.result, level: 'danger'});
          return;
        }
        if(data.result != false){
          $('#img_device').attr("src",'plugins/myenergi/core/config/devices/'+data.result);
        }else{
          $('#img_device').attr("src",'plugins/myenergi/plugin_info/myenergi_icon.png');
        }
      }
    });
  });