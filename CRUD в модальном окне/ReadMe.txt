CRUD в модальном окне
Есть на сайтах: plan2.local

УСТАНОВКА:
1) Сгенерировать gii шаблон crudInModal.

(old)УСТАНОВКА (1,5ч, если одно поле):
1) Создать модель
2) Файлы (10.04.2021)
	ContractPaymentController.php
	/contract-payment/_form.php
	Grid из /_journey/_payment.php
	ContractPayment::search()
3) Refactor
	ContractPayment --  (модель)
	contract-payment -- 
	contract_id -- 
	Условия оплаты -- 
	'data-updateOk' => '.payment_wrap',  --  
	$model::canUpdateJourneyCalc() -- 

