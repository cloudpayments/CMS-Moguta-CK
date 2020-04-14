<?php

/*
	Plugin Name: CloudKassir: Сервис онлайн кассы
	Description: Плагин позволяет интегрировать онлайн-кассу CloudKassir в интернет-магазин.
	Author: CloudPayments
	Version: 1.2.0
 */

new cloudkassir;

class cloudkassir
{
  const RECEIPT_TYPE_INCOME = 'Income';
  const RECEIPT_TYPE_INCOME_DELIVERY = 'Income_delivery';
  const RECEIPT_TYPE_INCOME_RETURN = 'IncomeReturn';

  private static $pluginName = ''; // название плагина (соответствует названию папки)
  private static $path = '';//путь к плагину

  private static $curl; //CURL ресурс

  public function __construct()
  {

    mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate')); //Активация плагина
    mgAddAction(__FILE__, array(__CLASS__, 'pageSettingsPlugin')); //Настройки плагина
    mgAddAction('Controllers_Payment_actionWhenPayment', array(__CLASS__, 'hookPayment'), 1);//хук плагина
    mgAddAction('Models_Order_updateOrder', array(__CLASS__, 'hookRefund'), 1);//хук плагина
    
    self::$pluginName = PM::getFolderPlugin(__FILE__);//имя плагина
    self::$path = PLUGIN_DIR . self::$pluginName;//папка плагина
  }
  
  static function activate()
  {

    if (!MG::getSetting('cloudkassirOption')) {
      $arr = array(
        'taxation_system' => '0',
        'vat' => 'vat_none',
        'vat_delivery' => 'vat_none',
        'status_refund' => array(4)
      );
      MG::setOption(array('option' => 'cloudkassirOption', 'value' => addslashes(serialize($arr))));
    }

    DB::query("
		 CREATE TABLE IF NOT EXISTS `" . PREFIX . self::$pluginName . "` (
			`id` int(11) NOT NULL COMMENT 'ID заказа',
			`number` varchar(32) NOT NULL COMMENT 'Номер заказа',
			`uuid` varchar(100) NOT NULL DEFAULT '' COMMENT 'Номер чека на cloudkassir',
			`status` varchar(25) NOT NULL DEFAULT '' COMMENT 'Статус',      
			`fn_number` varchar(255) DEFAULT '' COMMENT 'Фискальный номер документа на cloudkassir',
			`last_action` varchar(25) NOT NULL DEFAULT '' COMMENT 'Последняя операция',      
			`error` varchar(255) NOT NULL DEFAULT '' COMMENT 'Текст ошибки',      
			`time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата обработки',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

  }

  static function preparePageSettings()
  {
    //перед генерацией страницы настроек плагина
    echo '   
			<link rel="stylesheet" href="' . SITE . '/' . self::$path . '/css/style.css" type="text/css" />
		 
			<script type="text/javascript">
				includeJS("' . SITE . '/' . self::$path . '/js/script.js");  
			</script> 
		';
  }

  static function pageSettingsPlugin()
  {
    $pluginName = self::$pluginName;
    //Вывод страницы плагина в админке
    self::preparePageSettings();

    $options = unserialize(stripslashes(MG::getSetting('cloudkassirOption')));

    $paymentVariants = array();
    $rows = DB::query("SELECT `id`, `name` FROM `" . PREFIX . "payment` ORDER BY `sort` asc");
    while ($row = DB::fetchAssoc($rows)) {
      $paymentVariants[$row['id']] = $row['name'];
    }

    $statusVariants = array();
    $lang = MG::get('lang');
    if (class_exists('statusOrder')) {
      $dbQuery = DB::query('SELECT `id_status`, `status` FROM `' . PREFIX . 'mg-status-order`');
      while ($dbRes = DB::fetchArray($dbQuery)) {
        $statusVariants[$dbRes['id_status']] = $dbRes['status'];
      }
    } else {
      $ls = Models_Order::$status;
      foreach ($ls as $key => $value) {
        $statusVariants[$key] = $lang[$value];
      }
    }

    include 'pageplugin.php';
  }

  /**
   * @param string $location
   * @param array $request
   * @return bool|array
   */
  private static function makeRequest($location, $request = array(), $auth = array())
  {
    if (!self::$curl) {
      $auth = $auth['public_id'] . ':' . $auth['secret_key'];
      self::$curl = curl_init();
      curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt(self::$curl, CURLOPT_CONNECTTIMEOUT, 30);
      curl_setopt(self::$curl, CURLOPT_TIMEOUT, 30);
      curl_setopt(self::$curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
      curl_setopt(self::$curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt(self::$curl, CURLOPT_USERPWD, $auth);
    }

    curl_setopt(self::$curl, CURLOPT_URL, 'https://api.cloudpayments.ru/' . $location);
    curl_setopt(self::$curl, CURLOPT_HTTPHEADER, array(
      "content-type: application/json"
    ));
    curl_setopt(self::$curl, CURLOPT_POST, true);
    curl_setopt(self::$curl, CURLOPT_POSTFIELDS, json_encode($request));

    $response = curl_exec(self::$curl);
    if ($response === false || curl_getinfo(self::$curl, CURLINFO_HTTP_CODE) != 200) {
      MG::loger('CloudKassir Failed API request' .
        ' Location: ' . $location .
        ' Request: ' . print_r($request, true) .
        ' HTTP Code: ' . curl_getinfo(self::$curl, CURLINFO_HTTP_CODE) .
        ' Error: ' . curl_error(self::$curl)
      );

      return false;
    }
    $response = json_decode($response, true);
    if (!isset($response['Success']) || !$response['Success']) {
      MG::loger('CloudKassir Failed API request' .
        ' Location: ' . $location .
        ' Request: ' . print_r($request, true) .
        ' HTTP Code: ' . curl_getinfo(self::$curl, CURLINFO_HTTP_CODE) .
        ' Error: ' . isset($response['Message']) ? $response['Message'] : ''
      );

      return false;
    }

    return $response;
  }

  /**
   * @param $orderId
   * @param $options
   * @param string $type
   * @return array|bool
   */
  private static function requestOrderReceipt($orderId, $options, $type = self::RECEIPT_TYPE_INCOME)
  {
    $order = DB::query("SELECT `id`, `number`, `user_email`, `phone`, `order_content`, `number`, `summ`, `delivery_cost` 
					FROM `" . PREFIX . "order` WHERE `id` = " . $orderId);

    $order = DB::fetchAssoc($order);
    $order['order_content'] = unserialize(stripslashes($order['order_content']));

    $order['phone'] = substr(preg_replace('~\D~', '', $order['phone']), 1);
    
    $receipt = array(
      'Items' => array(),
      'taxationSystem' => $options['taxation_system'],
      'calculationPlace'=>'www.'.$_SERVER['SERVER_NAME'],
      'email' => $order['user_email'],
      'phone' => $order['phone']
    );
    if (floatval($order['delivery_cost']) > 0) {
        $amount = floatval($order['delivery_cost']) + floatval($order['summ']);
    }
    else $amount = floatval($order['summ']);
    if ($type == 'Income_delivery') {
        $kassa_method = 4;
        $Payment_sign = 'Income';
        $receipt['amounts']['advancePayment'] = $amount;
    }
    else {
        $kassa_method = (float)$options['method'];
        $Payment_sign = $type;
        $receipt['amounts']['electronic'] = $amount;
    }
    
    $vat = substr($options['vat'], 4); //Удаляем vat_
    if ($vat == 'none') {
      $vat = '';
    }
    $vat_delivery = substr($options['vat_delivery'], 4); //Удаляем vat_
    if ($vat_delivery == 'none') {
      $vat_delivery = '';
    }
    
    foreach ($order['order_content'] as $item) {

      $tmp = explode(PHP_EOL, $item['name']);

      $item = array(
        'label' => MG::textMore($tmp[0], 125),
        'price' => floatval($item['price']),
        'quantity' => floatval($item['count']),
        'amount' => floatval($item['price']) * floatval($item['count']),
        'vat' => $vat,
        'method' => $kassa_method,
        'object' => (float)$options['object'],
      );

      $receipt['Items'][] = $item;
    }

    if (floatval($order['delivery_cost']) > 0) {
      $item = array(
        'label' => 'Доставка',
        'price' => floatval($order['delivery_cost']),
        'quantity' => 1,
        'amount' => floatval($order['delivery_cost']),
        'vat' => $vat_delivery,
        'method' => $kassa_method,
        'object' => 4,
      );
      $receipt['Items'][] = $item;
    }

    $response = self::makeRequest('kkt/receipt', array(
      'Inn' => $options['inn'],
      'Type' => $Payment_sign,
      'CustomerReceipt' => $receipt,
      'InvoiceId' => $order['number'],
      'AccountId' => $order['user_email'],
    ), array(
      'public_id' => $options['public_id'],
      'secret_key' => $options['secret_key']
    ));

    if (isset($response['Model']['Id'])) {
      self::updateReceipt(array(
        'order_id' => $order['id'],
        'order_number' => $order['number'],
        'uuid' => $response['Model']['Id'],
        'type' => $type,
        'error' => ''
      ));
    } else {
      self::updateReceipt(array(
        'order_id' => $order['id'],
        'order_number' => $order['number'],
        'uuid' => '',
        'type' => $type,
        'error' => $response['Message']
      ));
    }

    return $response;
  }

  private static function updateReceipt($data)
  {
    $res = DB::query("SELECT id FROM " . PREFIX . self::$pluginName . " WHERE id = " . intval($data['order_id']));
    if ($row = DB::fetchAssoc($res)) {
      DB::query("UPDATE `" . PREFIX . self::$pluginName . "` SET  
						`last_action` = " . DB::quote($data['type']) . ", `error` = " . DB::quote($data['error']) . "
          WHERE `id` = " . intval($data['order_id'])
      );
    } else {
      DB::query("INSERT INTO `" . PREFIX . self::$pluginName . "` 
						(`id`, `number`, `uuid`, `last_action`, `error`) VALUES
						(" . DB::quote($data['order_id']) . ", " . DB::quote($data['order_number']) . ", " .
        DB::quote($data['uuid']) . ", " . DB::quote($data['type']) . ", " . DB::quote($data['error']) . ")"
      );
    }
  }

  private static function checkAllowAction($orderId, $type)
  {
    $res = DB::query("SELECT `last_action` FROM " . PREFIX . self::$pluginName . " WHERE id = " . intval($orderId));
    $lastAction = '';
    if ($row = DB::fetchAssoc($res)) {
      $lastAction = $row['last_action'];
    }

    switch ($type) {
      case self::RECEIPT_TYPE_INCOME:
        $allow = empty($lastAction);
        break;
      case self::RECEIPT_TYPE_INCOME_DELIVERY:
        $allow = $lastAction === self::RECEIPT_TYPE_INCOME;
        break;
      case self::RECEIPT_TYPE_INCOME_RETURN:
        $allow = $lastAction === self::RECEIPT_TYPE_INCOME ||
        $allow = $lastAction === self::RECEIPT_TYPE_INCOME_DELIVERY;
        break;    
      default:
        $allow = false;
    }
    return $allow;
  }

  static function hookPayment($args)
  {
    //функция хука
    $ok = isset($args['args']) ? true : false;

    if ($ok) {
      $ok = self::checkAllowAction($args['args']['paymentOrderId'], self::RECEIPT_TYPE_INCOME);
    }

    if ($ok) {
      $options = unserialize(stripslashes(MG::getSetting('cloudkassirOption')));
      $ok = in_array($args['args']['paymentID'], $options['payment_enable']);
    }

    if ($ok) {
      self::requestOrderReceipt($args['args']['paymentOrderId'], $options, self::RECEIPT_TYPE_INCOME);
    }
    return $args;
  }

  static function hookRefund($args)
  {
    //функция хука
    $ok = isset($args['args']) ? true : false;
    
    if ($ok) {
      $options = unserialize(stripslashes(MG::getSetting('cloudkassirOption')));
      
      if ($args['args'][0]['status_id'] == $options['status_delivered'][0] && ($options['method'] ==1 || $options['method'] ==2 || $options['method'] ==3)) {
        $ok = self::checkAllowAction($args['args'][0]['id'], self::RECEIPT_TYPE_INCOME_DELIVERY);
      }
      else $ok = self::checkAllowAction($args['args'][0]['id'], self::RECEIPT_TYPE_INCOME_RETURN);
    }

    if ($ok) {
        
        $order = DB::query("SELECT `payment_id` FROM `" . PREFIX . "order` WHERE `id` = " . $args['args'][0]['id']);
        $order = DB::fetchAssoc($order);
        
      $ok = (in_array($args['args'][0]['status_id'], $options['status_refund']) && in_array($order['payment_id'], $options['payment_enable']))
      || (in_array($args['args'][0]['status_id'], $options['status_delivered']) && in_array($order['payment_id'], $options['payment_enable']));
    }
    if ($ok) {
        if ($args['args'][0]['status_id'] == $options['status_delivered'][0]) {
            self::requestOrderReceipt($args['args'][0]['id'], $options, self::RECEIPT_TYPE_INCOME_DELIVERY);
        }
        else self::requestOrderReceipt($args['args'][0]['id'], $options, self::RECEIPT_TYPE_INCOME_RETURN);
    }
    return $args;
  }
}

?>