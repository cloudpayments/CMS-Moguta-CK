<div class="section-<?php echo $pluginName?>">
	<div class="widget-table-action base-settings">
		<h3>Настройки плагина CloudKassir</h3>
		<div class="large-6 small-12 columns">
			<div class="row">
				<div class="large-6 columns">
					<span>Public ID:</span>
				</div>
				<div class="large-6 columns">
					<input type="text" name="public_id" value="<?php echo $options['public_id']; ?>">
				</div>
			</div>
			<div class="row">
				<div class="large-6 columns">
					<span>Секретный ключ:</span>
				</div>
				<div class="large-6 columns">
					<input type="text" name="secret_key" value="<?php echo $options['secret_key']; ?>">
				</div>
			</div>
			<div class="row">
				<div class="large-6 columns">
					<span>ИНН организации:</span>
				</div>
				<div class="large-6 columns">
					<input type="text" name="inn" value="<?php echo $options['inn']; ?>">
				</div>
			</div>
			<div class="row">
				<div class="large-6 columns">
					<span>Система налогообложения:</span>
				</div>
				<div class="large-6 columns">
					<select name="taxation_system">
							<option value="0" <?php echo ($options['taxation_system'] == '0' ? 'selected' : ''); ?>>Общая система налогообложения</option>
							<option value="1" <?php echo ($options['taxation_system'] == '1' ? 'selected' : ''); ?>>Упрощенная система налогообложения (Доход)</option>
							<option value="2" <?php echo ($options['taxation_system'] == '2' ? 'selected' : ''); ?>>Упрощенная система налогообложения (Доход минус Расход)</option>
							<option value="3" <?php echo ($options['taxation_system'] == '3' ? 'selected' : ''); ?>>Единый налог на вмененный доход</option>
							<option value="4" <?php echo ($options['taxation_system'] == '4' ? 'selected' : ''); ?>>Единый сельскохозяйственный налог</option>
							<option value="5" <?php echo ($options['taxation_system'] == '5' ? 'selected' : ''); ?>>Патентная система налогообложения</option>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="large-6 columns">
					<span>Ставка НДС:</span>
				</div>
				<div class="large-6 columns">
					<select name="vat">
							<option value="vat_none" <?php echo ($options['vat'] == 'vat_none' ? 'selected' : ''); ?>>НДС не облагается</option>
							<option value="vat_0" <?php echo ($options['vat'] == 'vat_0' ? 'selected' : ''); ?>>НДС 0%</option>
							<option value="vat_10" <?php echo ($options['vat'] == 'vat_10' ? 'selected' : ''); ?>>НДС 10%</option>
							<option value="vat_20" <?php echo ($options['vat'] == 'vat_20' ? 'selected' : ''); ?>>НДС 20%</option>
                            <option value="vat_110" <?php echo ($options['vat'] == 'vat_110' ? 'selected' : ''); ?>>Расчетный НДС 10/110</option>
                            <option value="vat_120" <?php echo ($options['vat'] == 'vat_120' ? 'selected' : ''); ?>>Расчетный НДС 20/120</option>
					</select>
				</div>
			</div>
            <div class="row">
                <div class="large-6 columns">
                    <span>Ставка НДС для доставки:</span>
                </div>
                <div class="large-6 columns">
                    <select name="vat_delivery">
                        <option value="vat_none" <?php echo ($options['vat_delivery'] == 'vat_none' ? 'selected' : ''); ?>>НДС не облагается</option>
                        <option value="vat_0" <?php echo ($options['vat_delivery'] == 'vat_0' ? 'selected' : ''); ?>>НДС 0%</option>
                        <option value="vat_10" <?php echo ($options['vat_delivery'] == 'vat_10' ? 'selected' : ''); ?>>НДС 10%</option>
                        <option value="vat_20" <?php echo ($options['vat_delivery'] == 'vat_20' ? 'selected' : ''); ?>>НДС 20%</option>
                        <option value="vat_110" <?php echo ($options['vat_delivery'] == 'vat_110' ? 'selected' : ''); ?>>Расчетный НДС 10/110</option>
                        <option value="vat_120" <?php echo ($options['vat_delivery'] == 'vat_120' ? 'selected' : ''); ?>>Расчетный НДС 20/120</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="large-6 columns">
                    <span>Способ расчета:</span>
                </div>
                <div class="large-6 columns">
                    <select name="method">
                        <option value="0" <?php echo ($options['method'] == '0' ? 'selected' : ''); ?>>Неизвестный способ расчета</option>
                        <option value="1" <?php echo ($options['method'] == '1' ? 'selected' : ''); ?>>Предоплата 100%</option>
                        <option value="2" <?php echo ($options['method'] == '2' ? 'selected' : ''); ?>>Предоплата</option>
                        <option value="3" <?php echo ($options['method'] == '3' ? 'selected' : ''); ?>>Аванс</option>
                        <option value="4" <?php echo ($options['method'] == '4' ? 'selected' : ''); ?>>Полный расчёт</option>
                        <option value="5" <?php echo ($options['method'] == '5' ? 'selected' : ''); ?>>Частичный расчёт и кредит</option>
                        <option value="6" <?php echo ($options['method'] == '6' ? 'selected' : ''); ?>>Передача в кредит</option>
                        <option value="7" <?php echo ($options['method'] == '7' ? 'selected' : ''); ?>>Оплата кредита</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="large-6 columns">
                    <span>Предмет расчета:</span>
                </div>
                <div class="large-6 columns">
                    <select name="object">
                        <option value="0" <?php echo ($options['object'] == '0' ? 'selected' : ''); ?>>Неизвестный предмет оплаты</option>
                        <option value="1" <?php echo ($options['object'] == '1' ? 'selected' : ''); ?>>Товар</option>
                        <option value="2" <?php echo ($options['object'] == '2' ? 'selected' : ''); ?>>Подакцизный товар</option>
                        <option value="3" <?php echo ($options['object'] == '3' ? 'selected' : ''); ?>>Работа</option>
                        <option value="4" <?php echo ($options['object'] == '4' ? 'selected' : ''); ?>>Услуга</option>
                        <option value="5" <?php echo ($options['object'] == '5' ? 'selected' : ''); ?>>Ставка азартной игры</option>
                        <option value="6" <?php echo ($options['object'] == '6' ? 'selected' : ''); ?>>Выигрыш азартной игры</option>
                        <option value="7" <?php echo ($options['object'] == '7' ? 'selected' : ''); ?>>Лотерейный билет</option>
                        <option value="8" <?php echo ($options['object'] == '8' ? 'selected' : ''); ?>>Выигрыш лотереи</option>
                        <option value="9" <?php echo ($options['object'] == '9' ? 'selected' : ''); ?>>Предоставление РИД</option>
                        <option value="10" <?php echo ($options['object'] == '10' ? 'selected' : ''); ?>>Платеж</option>
                        <option value="11" <?php echo ($options['object'] == '11' ? 'selected' : ''); ?>>Агентское вознаграждение</option>
                        <option value="12" <?php echo ($options['object'] == '12' ? 'selected' : ''); ?>>Составной предмет расчета</option>
                        <option value="13" <?php echo ($options['object'] == '13' ? 'selected' : ''); ?>>Иной предмет расчета</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="large-6 columns">
                    <span>Способы оплаты:</span><br><a id="clearPayment">Очистить</a>
                </div>
                <div class="large-6 columns">
                    <select name="payment_enable" multiple size="10">
                      <?php foreach($paymentVariants as $key => $value): ?>
                          <option value="<?php echo $key; ?>"<?php if(in_array($key, $options['payment_enable'])) echo " selected" ?>><?php echo $value; ?></option>
                      <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="large-6 columns">
                    <span>Статусы заказа для печати второго чека:</span><br><a id="clearRefundStatus">Очистить</a>
                </div>
                <div class="large-6 columns">
                    <select name="status_delivered" multiple size="10">
                      <?php foreach($statusVariants as $key => $value): ?>
                          <option value="<?php echo $key; ?>"<?php if(in_array($key, $options['status_delivered'])) echo " selected" ?>><?php echo $value; ?></option>
                      <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="large-6 columns">
                    <span>Статусы заказа для возврата (возврат прихода):</span><br><a id="clearRefundStatus">Очистить</a>
                </div>
                <div class="large-6 columns">
                    <select name="status_refund" multiple size="10">
                      <?php foreach($statusVariants as $key => $value): ?>
                          <option value="<?php echo $key; ?>"<?php if(in_array($key, $options['status_refund'])) echo " selected" ?>><?php echo $value; ?></option>
                      <?php endforeach; ?>
                    </select>
                </div>
            </div>
			<div class="row">
				<div class="large-6 columns">
				</div>
				<div class="large-6 columns">
					<button class="base-setting-save button success"><span><i class="fa fa-floppy-o"></i> Сохранить</span></button>
				</div>
			</div>
		</div>		
		<div class="clear"></div>
	</div>
</div>