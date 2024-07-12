Grid. Групповое действие
Редактирование нескольких
Есть на сайтах: plan2.local

УСТАНОВКА (1ч):
1) Refactor
	PefElement -- Contracts
2) В модели там где required или enableClientValidation добавить
	, 'except'=>'updateMultiple'

В последней версии упрощённое добавление в index.php
	'class' => \app\components\grid\MultiSelectColumn::class

ОПЦИИ:
Уникальный url. В column:
	'urlEdit' => Url::to(['update-multiple', 'variant_id'=>1]),