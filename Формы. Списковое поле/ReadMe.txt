Формы. Списковое поле
CRUD списков и добавление к модели (hasOne, hasMany)
Есть на сайтах: plan2.local

УСТАНОВКА (для связи hasOne):
1) Также как "Формы. Списки пользователей"
    Refactor
        UserList -- ListField
    ListField
        (опционально)function isCustomSort()


УСТАНОВКА (для связи hasMany):
Похоже на "Формы. Списки пользователей", см Contracts::works
1) Refactor (дополнить)
    2contract -- 2equipment
    contract_id -- equipment_id
    planning.contracts -- planning.equipment
    LIST_CONTRACT_WORKS -- 
    works -- products
    Работы -- Продукт
2) (если не создавали для этой модели) Миграция, создание модели
    $this->createTable('{{list_field2contract}}', [
        'id' => 'pk',
        'contract_id' => 'int NOT NULL',
        'list_field_id' => 'int NOT NULL',
        'list_id' => 'int NOT NULL',
    ]);
    $this->createIndex('list_field2contract__list_id', '{{list_field2contract}}', 'list_id');
    //Внешние ключи
    $this->createIndex('list_field2contract__contract_id', '{{list_field2contract}}', 'contract_id');
    $this->addForeignKey('fk_contract_id', '{{list_field2contract}}', 'contract_id', '{{planning.contracts}}', 'id', 'CASCADE', 'CASCADE');
    $this->createIndex('list_field2contract__list_field_id', '{{list_field2contract}}', 'list_field_id');
    $this->addForeignKey('fk_list_field_id', '{{list_field2contract}}', 'list_field_id', '{{list_field}}', 'id', 'CASCADE', 'CASCADE');

    Создание модели для list_field2equipment через gii 
    
3) Модель
    /**
     * @property ListField[] $works
     */
    class Contracts {
        use SaveRelationsTrait;
        public function behaviors()
        {
            return [
                'saveRelations' => [
                    'class' => SaveRelationsBehavior::class,
                    'relationKeyName' => SaveRelationsBehavior::RELATION_KEY_RELATION_NAME,
                    'relations' => [
                        'works' => ['extraColumns' => [
                            'list_id' => ListField::LIST_CONTRACT_WORKS
                        ]],
                    ],
                    'checkRelationsSafe' => true,
                ],
            ];
        }
        public function rules()
        {
            return [
                [['works'], 'safe'],
        }
        public function getWorks2contract()
        {
            return $this->hasMany(ListField2contract::class, ['contract_id' => 'id'])->alias('works2contract')
                ->andWhere(['works2contract.list_id' => ListField::LIST_CONTRACT_WORKS]);
        }
        public function getWorks()
        {
            return $this->hasMany(ListField::class, ['id' => 'list_field_id'])->via('works2contract');
        }
    }
4) ListField
    const LIST_CONTRACT_WORKS = 1;
    function getListNameAll()
        static::LIST_CONTRACT_WORKS=>'Работы',
5) View
    <?= ListField::select2('works', ListField::LIST_CONTRACT_WORKS, $model, true);?>
6) Контроллер
    if($model->load($post = Yii::$app->request->post())) {
        if(!$post['works']) $model->works=null;
        ...
    }

ОПЦИИ
- Сохранение последовательности выбора (добавить в view, relation)

TODO:
- (для связи hasMany) Убрать необходимость создавать таблицу list_field2contract и модель ListField2contract. 
	Вместо них единую list_field2model
		добавить поле "model" куда записывать хэш класса модели как в https://github.com/yii2mod/yii2-comments