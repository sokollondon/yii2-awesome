<?php

namespace app\modules\constructors\models;

use app\components\ActiveRecordDefault;
use app\modules\admin\models\User;
use yii;

/**
 * table {@link tableName}
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $reason_id
 * @property float $price
 * @property bool $is_active
 *
 * @property User $user
 * 
 * @mixin SaveRelationsBehavior
 */
class PostMy extends ActiveRecordDefault
{
    public static function tableName()
    {
        return 'constructors.on_executor';
    }

    /**
     * Док:
     * http://www.webapplex.ru/pravila-validaczii-form-v-yii-2.x
     * https://www.yiiframework.com/doc/guide/2.0/ru/input-validation
     */
    public function rules()
    {
        //NOTE: you should only define rules for those attributes that will receive user inputs.
        return [
            [['user_id'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['text'], 'string'],
            ['reason_hand', 'required', 'when' => function(self $model) {return $model->reason_id == 9;}
            , 'whenClient' => "function (attribute, value) {
                return $('select[name=\"PostMy[reason_id]\"]').val() == 9;
            }"],//,'enableClientValidation' => false
            [['user_id'], 'integer'],
            [['price'], 'number'],//float  //'min'=>0.01
            [['price'], 'number','enableClientValidation' => false],//убирает валидацию на стороне клиента
            [['is_active'], 'boolean'],
            [['date'], 'isValidDate'],
            ['user_id', 'unique', 'message' => 'Этот пользователь уже добавлен'],
            ['user_id', 'unique', 'targetAttribute' => ['user_id', 'list_id']],//уникальность по двум полям
            [['file'], 'file', 'extensions' => 'doc, docx', 'maxSize' => 3*1024*1024, 'tooBig' => 'Максимальный размер 3 Мб', 'checkExtensionByMimeType'=> false],
            ['email', 'email'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            //Ограничение по сценарию. Ещё вариант scenarios() ниже
            [['status_id'], 'required', 'except'=>'updateMultiple'],
            [['status_id'], 'required', 'on'=>'default'],//сценарий по-умолчанию

            //Доп.
            [['name'], 'trim'],
            [['name'], 'trim2'],//убирает двойной пробел
            [['esd_id', 'equipment_id'], 'required', 'when' => function(self $model) {//одно из двух полей
                return (!$model->esd_id && !$model->equipment_id);
            },'whenClient' => 'function(){return false;}'],
            ['qty', function ($attribute){
                if($this->$attribute != 3){
                    $this->addError($attribute, 'Свой валидатор');
                }
            }],//'skipOnEmpty' => false
            ['email', function ($attribute) {//Несколько email через запятую
                $email = explode(',', $this->$attribute);
                foreach ($email as $item) {
                    $item = trim($item);
                    if(!(new yii\validators\EmailValidator())->validate($item, $error)){
                        $this->addError($attribute, $error);
                    }
                }
            }],
        ];
    }
    //Ограничение доступа к полям. По умолчанию доступны все которые есть в rules()
    //(!)Не сделать, если есть DynamicForm
    // только для app\components\ActiveForm
    public function scenarios()
    {
        $s = parent::scenarios();
        if(Yii::$app->id =='console') return $s;
        $sD = $s[self::SCENARIO_DEFAULT];
        $s[self::SCENARIO_SEARCH] = $sD;//в search() добавить $this->setScenario(self::SCENARIO_SEARCH);

        //ВАРИАНТ №1. Можно редактировать только заданные поля
        if(self::canUpdateTZ()){
            $sD=[];
            $sD = array_merge($sD, ['price','name']);
        }

        //ВАРИАНТ №2. Можно редактировать ВСЕ КРОМЕ заданных полей
        if(!self::canUpdateTZ2()){
            unset($sD[array_search('price',$sD)]);
        }

        $s[self::SCENARIO_DEFAULT] = $sD;
        return $s;
    }
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
        ];
    }

    public static function getList($orIds=[])
    {//todo-s $addEmpty=true надо?
        $items = static::find()
            ->where([
                'is_active' => true,
            ])->orFilterWhere(['IN', 'id', $orIds])->asArray()->all();
        $list = ArrayHelper::map($items, 'id', 'name');
        return [null => ''] + $list;
    }

    public function beforeValidate()
    {
        $this->price = changeCommaToDot($this->price);
        return parent::beforeValidate();
    }
    public function beforeSave($insert) {
//        if($this->time) $this->time = date("Y-m-d H:i:s", strtotime($this->time));//для MYSQL
        return parent::beforeSave($insert);
    }
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);
        //your_code
        //if($this->isAttributeChangedAfterSave('qty', $changedAttributes))//изменились
    }

    public function isAttributeChangedAfterSave($name, $changedAttributes)
    {
        return array_key_exists($name, $changedAttributes) && $this->$name != $changedAttributes[$name];
    }


//    public function isValidDate($attribute)//Валидация даты
//    {
//        if($this->$attribute=="") $this->$attribute=null;
//        if(!is_null($this->$attribute) && !strtotime($this->$attribute))
//        {
//            $this->addError($attribute, 'Неверно указана дата');
//        }
//    }
//    public function trim2($attr)
//    {
//        $this->$attr = trim($this->$attr);
//        $this->$attr = preg_replace('/[\s]{2,}/', ' ', $this->$attr);//убирает двойной пробел
//    }


    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
    public function getUsers2()
    {
        return $this->hasMany(User::class, ['post_id' => 'id'])->alias('users2')
            ->orderBy(['users2.id' => SORT_ASC])->andWhere(['users2.is_admin'=>true]);
    }
    public function getUsers3()
    {
        $query = $this->hasMany(User::class, ['post_id' => 'id']);
        if($this->id){//для сортировки, чтобы не было ошибки при фильтрации
            $query->orderBy(['users3.id' => SORT_ASC]);
        }
        return $query;
    }
    public function getAdmins()//фильтр по связанным
    {
        return $this->hasMany(User::class, ['post_id' => 'id'])->joinWith(['roles roles'])
            ->andWhere(['roles.name'=>'admin']);
    }
    public function getTag2post()
    {
        return $this->hasMany(Tag2post::class, ['post_id' => 'id']);
    }
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->via('tag2post');
    }
    public function getTags3()//Сортировка hasMany по таблице many2many.id (в порядке в котором прикрепляли)
    {   //(!) Не будет работать ->with(['tags3']) => т.е. relation нужно получать для каждой записи отдельно
        //ПР: Contracts::getExecutors()
        /** @var yii\db\ActiveQuery $query */
        $query = $this->hasMany(Tag::class, ['id' => 'tag_id'])->via('tag2post');
        //для сортировки
        if($this->id){
            $query->alias('tags3')
                ->innerJoin('{{tag2post}}','tag2post.tag_id = tags3.id')
                ->andWhere(['tag2post.list_id' => 1])
                ->andWhere(['tag2post.post_id' => $this->id])
                ->orderBy(['tag2post.id'=> SORT_ASC]);
        }
        return $query;
    }
}
