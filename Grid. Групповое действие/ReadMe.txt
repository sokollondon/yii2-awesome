Grid. Групповое действие
Редактирование нескольких
Есть на сайтах: plan2.local

УСТАНОВКА (1ч):
1) Refactor
	Contracts -- 
2) В модели там где required или enableClientValidation добавить
	, 'except'=>'updateMultiple'

Для bool поля
	rules() 'safe'
	view: <?=$form->field($model, 'is_active')->dropDownList([null => '', 'yes' => 'Да', 'no' => 'Нет']);?>
	controller: if ($model->is_active) $item->is_active = getBool($model->is_active);
Чтобы обрабатывались ошибки валидации
	см. ElementController::actionUpdateMultiple()
	Html::submitButton('Сохранить', ['class' => '... sendAjax'])
	renderPartial -- renderAjax

ОПЦИИ:
Уникальный url. В column:
	'urlEdit' => Url::to(['update-multiple', 'variant_id'=>1]),