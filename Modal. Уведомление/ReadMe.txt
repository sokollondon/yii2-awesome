Modal. Уведомление
Есть на сайтах: plan2.local

УСТАНОВКА:
1) 
    public function actionAjaxJourneyCanSetStatusExecuted()
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        return $this->renderPartial('//common/_modal',[
            'title'=>'Вы уверены?', 'bg'=>'warning',
            'body'=>'- 111<br>- 222',
            'footer'=>'<span class="btn btn-warning" data-dismiss="modal">Ok</span>',
        ]);
    }
