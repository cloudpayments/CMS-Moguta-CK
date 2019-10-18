<?php

class Pactioner extends Actioner {

  private $pluginName = 'cloudkassir';
  
  /**
  * Сохранение опций
  */
  public function saveBaseOption() {
    $this->messageSucces = 'Сохранено';
    $this->messageError = 'Ошибка сохранения';
    if (!empty($_POST['data'])) {
      MG::setOption(array('option' => 'cloudkassirOption', 'value' => addslashes(serialize($_POST['data']))));
    }
    return true;
  }
}