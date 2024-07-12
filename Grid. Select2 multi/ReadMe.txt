Grid. Select2 multi
Есть на сайтах: plan2.local

УСТАНОВКА:
1) Refactor
2) Модель. Search() как обычно:
	$query->andFilterWhere(['t.status_id' => $this->status_id]);
3) View
	Grid columns
        [
            'attribute'=>'status_id',
            //...
            'filter'=> $model->getStatusList(),
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'options' => ['multiple' => true],
            ],
        ],
    <style>
	    #contracts-status_id+.select2 .select2-selection{max-width:60px;}
	</style>


	Доп. Если нужена кнопка с готовым фильтром вверху
		$status_id = [$model::STATUS_NEED_CALC, $model::STATUS_WORK];
		$btn_left.= Html::a('Не завершённые', ['', 'Contracts'=>['status_id'=>$status_id]], ['title'=>$title,
		    'class'=>($model->status_id == $status_id ? 'label label-primary' : '')
		]);