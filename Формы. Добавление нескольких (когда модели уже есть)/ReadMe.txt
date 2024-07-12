Добавление нескольких
Есть на сайтах: OLG
(!) Использовать вместо этого, плагин https://github.com/la-haute-societe/yii2-save-relations-behavior (см. plan.local Contracts::linkedEquipments)

ИНСТРУКЦИЯ ПО УСТАНОВКЕ:
1) Скопировать код
2) Поправить 
	foreach($hobbiesAll as $id=>$hobby)...
		На: $hobbiesAll = ArrayHelper::map($hobbiesAll,'id','name');
3) Refactor
	игроку --  (1)
	player_id -- 
	Player (модель)(нету?) -- 
	player (модель для миграции) -- 
	Игрок (для миграции) -- 

	род увлечений (4шт) --  (many)
	категорий (1шт) -- -!!-
	Род увлечений (1шт) -- 
	hobby2player (шт) --  (миграции)
	Hobby2player (4шт) --  (модель)
	Hobby (1шт) --  (модель)
	hobby_id (4шт) -- 
	hobbies (17шт) --  (relation)
	Hobbies (4шт) --  (relation с заглавной)
	hobby (1шт) --  (relation: hobbies.hobby)
4) Миграции
5) Gii сгенерировать модель hobby2player


ОПЦИИ:
Чтобы при валидации поле подсвечивалось (красным если required и не заполнено)
	Модель
		Добавить поле $hobbies_a
		Добавить в attributeLabels()
			//Поля не из БД
            'hobbies_a' => 'Род увлечений',
	View -- _form
		$model->hobbies_a = $hobbies_a;
		echo $form->field($model, 'hobbies_a')->widget(Select2::className(), [
            'data' => $hobbiesAll,
            'options' => ['multiple' => true, 'placeholder' => '...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);




/**************************************
* Использование плагина SaveRelationsBehavior (часть без select2)
*/
ИНСТРУКЦИЯ ПО УСТАНОВКЕ:
1) Refactor
    project_sup2pef_element --  название_таблицы
    dbo.projects --  таблица (слева)
    project_sup_id --  поле (слева)
    ProjectSup --  (model слева)
    planning.pef_element --  таблица (справа)
    pef_element_id --  поле (справа)
    planning. --  схема
    projectSup2pefElement --  [AA](relation, model)
    mainProjectSups --  [AA](relation)
2) Migrate
    $this->createTable('{{planning.project_sup2pef_element}}', [
        'id' => 'pk',
        'pef_element_id' => 'int NOT NULL',
        'project_sup_id' => 'int NOT NULL',
    ]);
    //Внешние ключи
    $this->createIndex('project_sup2pef_element__pef_element_id', '{{planning.project_sup2pef_element}}', 'pef_element_id');
    $this->addForeignKey('fk_pef_element_id', '{{planning.project_sup2pef_element}}', 'pef_element_id', '{{planning.pef_element}}', 'id', 'CASCADE', 'CASCADE');
    $this->createIndex('project_sup2pef_element__project_sup_id', '{{planning.project_sup2pef_element}}', 'project_sup_id');
    $this->addForeignKey('fk_project_sup_id', '{{planning.project_sup2pef_element}}', 'project_sup_id', '{{dbo.projects}}', 'id', 'CASCADE', 'CASCADE');
3) Gii сгенерировать модель project_sup2pef_element
4) Controller
    if($model->load($post = Yii::$app->request->post())) {
        if(!$post['mainProjectSups']) $model->mainProjectSups=null;
    }
5) Model
	 * @property ProjectSup[] $mainProjectSups

    use SaveRelationsTrait;
    public function behaviors()
    {
        return [
            'saveRelations' => [
                'class' => SaveRelationsBehavior::class,
                'relations' => [
                    'mainProjectSups',
                ],
            ],
        ];
    }

    public function getProjectSup2pefElement()
    {
        return $this->hasMany(ProjectSup2pefElement::class, ['pef_element_id' => 'id']);
    }
    public function getMainProjectSups()
    {
        return $this->hasMany(ProjectSup::class, ['id' => 'project_sup_id'])->via('projectSup2pefElement');
    }


ОПЦИИ:
Доп. поле у project_sup2pef_element.
    1) Refactor
        is_main -- (доп. поле)
    2) Model
        'relations' => [
            'mainProjectSups' => ['extraColumns' => [
                'is_main' => UserList::LIST_CONTRACT_EXECUTORS
            ]],
        ],

        public function getProjectSup2pefElement()
        {
            ... ->alias('projectSup2pefElement')
                ->andWhere(['projectSup2pefElement.is_main' => UserList::LIST_CONTRACT_EXECUTORS]);
        }

Сортировка связанных
    Model
        public function getMainProjectSups()
        {
            /** @var yii\db\ActiveQuery $query */
            $query = $this->hasMany(ProjectSup::class, ['id' => 'project_sup_id'])->via('projectSup2pefElement');
            //для сортировки
            if($this->id){
                $query->alias('mainProjectSups')
                    ->innerJoin(ProjectSup2pefElement::tableName(),'project_sup2pef_element.project_sup_id = mainProjectSups.id')
                    ->andWhere(['project_sup2pef_element.is_main' => UserList::LIST_CONTRACT_EXECUTORS])
                    ->andWhere(['project_sup2pef_element.pef_element_id' => $this->id])
                    ->orderBy(['project_sup2pef_element.id'=> SORT_ASC]);
            }
            return $query;
        }