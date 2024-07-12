Формы. Списки пользователей
Похоже на "Формы. Списковое поле"
Есть на сайтах: plan2.local

УСТАНОВКА:
1) (если впервые исп. в проекте) см. plan2.local
2) Добавить в меню
	Как тут function getMenuJourneySettings()


УСТАНОВКА (для связи hasOne):
1) Refactor (проверить)
	sender_id -- 
	LIST_CONTRACT_SENDER -- 
	Sender -- (AA)(relation)
	Задание выдал -- 
2) Миграция
	$this->addColumn('{{planning.contracts}}', 'sender_id', 'int');
3) Модель
	/**
	 * @property int $sender_id
	 * @property UserList $sender
	 */
	class Contracts {
	    public function rules()
	    {
	        return [
	            [['sender_id'], 'integer'],
	        ];
	    }
	    public function attributeLabels()
	    {
	        return [
	            'sender_id' => 'Задание выдал',
	        ];
	    }

	    public function getSender()
	    {
	        return $this->hasOne(UserList::class, ['id' => 'sender_id']);
	    }
	}
4) UserList
	const LIST_CONTRACT_SENDER = 2; //todo-s номер следующий
	function getListNameAll()
        static::LIST_CONTRACT_SENDER=>'Задание выдал',
    function canUpdate()
5) View
	<?= UserList::select2('sender_id',UserList::LIST_CONTRACT_SENDER, $model);?>
		Если нужно setDefault, добавить ,false,true,$model->isNewRecord



УСТАНОВКА (для связи hasMany):
1) Refactor (дополнить)
	executors -- (relation. 3 варианта региста, заменять с Preserve case)
	Исполнитель -- 

2) (если не создавали для этой модели) Миграция
    $this->createTable('{{sp.user_list2contract}}', [
        'id' => 'pk',
        'contract_id' => 'int NOT NULL',
        'user_id' => 'int NOT NULL',
        'list_id' => 'int NOT NULL',
    ]);
    $this->createIndex('user_list2contract__list_id', '{{sp.user_list2contract}}', 'list_id');
    //Внешние ключи
    $this->createIndex('user_list2contract__contract_id', '{{sp.user_list2contract}}', 'contract_id');
    $this->addForeignKey('fk_contract_id', '{{sp.user_list2contract}}', 'contract_id', '{{planning.contracts}}', 'id', 'CASCADE', 'CASCADE');
    $this->createIndex('user_list2contract__user_id', '{{sp.user_list2contract}}', 'user_id');
    $this->addForeignKey('fk_user_id', '{{sp.user_list2contract}}', 'user_id', '{{rbac.ad_user_access}}', 'id', 'CASCADE', 'CASCADE');
3) Модель
	/**
	 * @property User[] $executors
	 */
	class Contracts {
	    public function behaviors()
	    {
	        return [
	            'saveRelations' => [
	                'class' => SaveRelationsBehavior::class,
	                'relationKeyName' => SaveRelationsBehavior::RELATION_KEY_RELATION_NAME,
	                'relations' => [
	                    'executors'=>['extraColumns' => [
	                        'list_id' => UserList::LIST_CONTRACT_EXECUTORS
	                    ]],
	                ],
	            ],
	        ];
	    }
	    public function getExecutors2contract()
	    {
	        return $this->hasMany(UserList2contract::class, ['contract_id' => 'id'])->alias('executors2contract')
	            ->andWhere(['executors2contract.list_id' => UserList::LIST_CONTRACT_EXECUTORS]);
	    }
	    public function getExecutors()
	    {
	        return $this->hasMany(User::class, ['id' => 'user_id'])->via('executors2contract');
	    }
	}
4) UserList
	const LIST_CONTRACT_EXECUTORS = 1;
	function getListNameById()
        static::LIST_CONTRACT_EXECUTORS=>'Исполнитель',
5) View
	<?= UserList::select2('executors', UserList::LIST_CONTRACT_EXECUTORS, $model, true);?>
6) Контроллер
    if($model->load($post = Yii::$app->request->post())) {
        if(!$post['executors']) $model->executors=null;
        ...
    }
