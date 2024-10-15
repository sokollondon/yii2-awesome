<?php

/**
 * PostgreSQL
 * Типы данных https://postgrespro.ru/docs/postgresql/16/datatype
 */
class PostgreSQL extends CDbMigration {

    public function safeUp() {
        //СОЗДАЕМ
        $this->createTable('{{post}}', [
            'id'         => 'pk',
            'name'       => 'string',
            'is_active'  => 'boolean DEFAULT true',
            'user_id'    => 'int',//до +2147483647 также как pk(serial)
            'float8'     => 'float8', //дробные числа. 15 дес. разрядов. Макс 123456789123.45 (= double)(в phpDoc исп. float)
            'type_id'    => 'smallint',// до +32767
            'bigint'     => 'bigint',// до +9223372036854775807
            //'name2'       => "varchar(64)",//NOT NULL || DEFAULT 'val'
            'text'       => 'text',//не ограничена длина
            'date'       => 'date',
            'updated_at' => 'timestamp',
            //AR default
        ]);
            //UNIQUE использовать только в моделе -- rules
            //если есть риск большого кол-ва одновременных запросов на редактирование, то можно сделать и в БД:
            //$this->execute("ALTER TABLE post ADD CONSTRAINT user_and_name_unique UNIQUE (user_id, name);"); //по двум полям
        $this->execute("COMMENT ON COLUMN planning.quiz.is_active IS 'Активен?'");
            //(!) если в названии поле есть заглавные буквы, то его заключать в "", например contractors.quiz."question_Type"

        $this->createIndex('post__user_id', '{{post}}', 'user_id');
        $this->addForeignKey('fk_user_id', '{{post}}', 'user_id', '{{user}}', 'id', 'SET NULL', 'CASCADE'); 
            //, 'CASCADE', 'CASCADE'     (при удалении строки внешнего поля(ref) удалится и текущая строка)
                //, 'SET NULL', 'CASCADE'   (поле user_id д.б. DEFAULT NULL)
            //По-умолчанию, если не указывать: , 'NO ACTION'   (но удалить строку внешнего поля(ref) не даст)

        
        //РЕДАКТИРУЕМ
        $this->addColumn('{{post}}', 'is_active', "boolean DEFAULT true");
        //$this->execute("COMMENT ON COLUMN planning.quiz.is_active IS 'Активен?'");
        
        $this->alterColumn('{{post}}', 'note', 'text');
        //$this->execute("ALTER TABLE post ALTER COLUMN user_id DROP NOT NULL");
        //$this->renameColumn('{{post}}','name_old','name_new');

        $this->execute('CREATE SCHEMA stock;');
        
        
        //НАПОЛНЯЕМ
        $this->insert('{{post}}',['user_id'=>1,'name'=>'От коллег',]);
            $this->execute('ALTER SEQUENCE planning.contract_performer_id_seq RESTART WITH 3;');//Перезапуск последовательности на 3 (может понадобиться, если id задавали вручную)
        //Обновляем поле
        $this->update('{{post}}', ['date'=>'1458460200'], "id=1");

        $this->execute(<<<SQL
INSERT INTO `kredo_new_quiz_answers` (`variant_answers_id`, `text`) VALUES
(1, 'ываы'),
(2, 'fdvgw');
SQL
        );
        $this->delete('{{post}}', "id=1");

        //Yii::$app->cache->flush();
        //app()->cache->flush();
    }

    public function safeDown() {
        //$this->execute("DELETE FROM user WHERE username='admin'"); //Если ниже есть dropTable, то не требуется

        //Не требуется, если ниже есть dropColumn() или dropTable()  {{post}}
//        $this->dropForeignKey('fk_user_id', '{{post}}');
//        $this->execute('DROP INDEX planning.post__user_id;'); 
            //название_схемы.название_индекса (!) Название таблицы не указывается.

        $this->dropColumn('{{client}}', 'user_id');

        $this->dropTable('{{post}}');

        //$this->execute('DROP SCHEMA stock;');
    }
}

protected function fillName(){
    echo "    >>> fillName: ";
    $ok = 0;
    foreach ($items as $item) {
        if($item->updateAttributes(['name_clear' => '123'])){
            $ok++;
        }
        if($ok%2000 == 0 && $ok) echo ".";
    }
    echo "$ok \n";
}
protected function fillV2(){
    echo "    >>> fillV2: ";
    //Если нужно beforeValidate(), afterSave() и т.д.
    $ok = 0;
    $err = [];
    foreach ($items as $item) {
        if($item->save()){
            $ok++;
        }else{
            $err[] = $item->id;
        }
        //if($ok%500 == 0 && $ok) echo "."; //Для очень долгих операций показывать прогресс ч-з каждые 500
    }
    echo "$ok \n";
    if($err) echo "Fail (".count($err).") for ids: ".implode(', ',$err)." \n";
}


class hasMany__link_many2many extends CDbMigration
{
/*
Когда такая миграция не нужна -- если связь 1 to many
    Если запись таблицы (слева)(many) может быть добавлен только к одной записи таблицы (справа)(1).
        В этом случае достаточно добавить у таблицы (слева)(many) поле таблица_1_id

(!) см "Формы. Добавление нескольких", там вместе с созданием моделей

Заменить
    component2official_note -- название_таблицы
    planning.price_components -- таблица (слева)
    price_components_id -- поле (слева)
    contractors.official_note -- таблица (справа)
    official_note_id -- поле (справа)
    contractors. -- схема
*/
    public function up()
    {
        /**
         * Состав hasMany
         */
        $this->createTable('{{contractors.component2official_note}}', [
            'id' => 'pk',
            'official_note_id' => 'int NOT NULL',
            'price_components_id' => 'int NOT NULL',
        ]);
        //Внешние ключи
        $this->createIndex('component2official_note__official_note_id', '{{contractors.component2official_note}}', 'official_note_id');
        $this->addForeignKey('fk_official_note_id', '{{contractors.component2official_note}}', 'official_note_id', '{{contractors.official_note}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('component2official_note__price_components_id', '{{contractors.component2official_note}}', 'price_components_id');
        $this->addForeignKey('fk_price_components_id', '{{contractors.component2official_note}}', 'price_components_id', '{{planning.price_components}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
    }
}





/**
 * RBAC Yii2
 */
use yii\db\Migration;
class RBAC_Yii2 extends Migration {

    public function safeUp() {
        /**
         * RBAC
         */
        $auth = Yii::$app->authManager;
        $perm = $auth->createPermission('viewTZ_Stock'); $perm->description = 'Склад. ТЗ RO'; $auth->add($perm);
        $perm = $auth->createPermission('updateTZ_Stock'); $perm->description = 'Склад. ТЗ RW'; $auth->add($perm);

        //$authItem = $auth->getRole('Планировщики');
        $authItem = $auth->createRole('ПЛ. Склад. ТЗ RW'); $auth->add($authItem);
        $auth->addChild($authItem, $auth->getPermission('viewTZ_Stock'));
        $auth->addChild($authItem, $auth->getPermission('updateTZ_Stock'));
        $authItem = $auth->createRole('ПЛ. Склад. ТЗ RO'); $auth->add($authItem);
        $auth->addChild($authItem, $auth->getPermission('viewTZ_Stock'));

        //Переименование (+ поиск использования в коде сайта)
        $perm = $auth->getPermission('viewTZ');
        $perm->name = 'viewNEW';
        $auth->update('viewTZ',$perm);
        $this->execute("UPDATE config.menu_item        SET access = REPLACE(access, 'viewTZ', 'viewNEW') WHERE access LIKE '%viewTZ%'");
        $this->execute("UPDATE config.subscribe_target SET access = REPLACE(access, 'viewTZ', 'viewNEW') WHERE access LIKE '%viewTZ%'");
    }

    public function manualAssign()//Вместо этого использовать AD
    {
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole('ПЛ. Командировки. ТЗ RW'),'16');//Таня
//        $auth->revoke($auth->getRole('ПЛ. Командировки. ТЗ RW'),'16');//Таня
    }

    public function safeDown() {
//        /**
//         * RBAC
//         */
//        $auth = Yii::$app->authManager;
//        $auth->remove($auth->getPermission('viewTZ_Stock'));
//        $auth->remove($auth->getPermission('updateTZ_Stock'));
//        $auth->remove($auth->getRole('ПЛ. Склад. ТЗ RW'));
//        $auth->remove($auth->getRole('ПЛ. Склад. ТЗ RO'));
    }

}

/**
 * RBAC Yii1
 */
class RBAC_Yii1 extends CDbMigration {

    public function safeUp() {
        /**
         * RBAC
         */
        $auth=Yii::app()->authManager;
//        $auth->assign('Web-разработчик','63');//Миша С
        $auth->createOperation('viewArrival_Stock',    'Склад. Приход. Просмотр');
        $auth->createOperation('createArrival_Stock',  'Склад. Приход. Создание');
        $auth->createOperation('updateArrival_Stock',  'Склад. Приход. Редактирование');
        $auth->createOperation('deleteArrival_Stock',  'Склад. Приход. Удаление');

//        $authItem = $auth->createRole('ПЛ. Склад RW', '');
        $authItem = $auth->getAuthItem('Планировщики');
        $authItem->addChild('viewArrival_Stock');
        $authItem->addChild('createArrival_Stock');
        $authItem->addChild('updateArrival_Stock');
        $authItem->addChild('deleteArrival_Stock');

          //Переименование (+ поиск использования в коде сайта)
//        $authItem = $auth->getAuthItem('viewStockTotalStock');
//        $authItem->setName('viewStockReports');
//        $authItem->setDescription('Склад. Отчеты');
    }

    public function safeDown() {
        /**
         * RBAC
         */
        $auth=Yii::app()->authManager;
        //$auth->revoke('ПЛ. Склад. ТЗ RW','16');
        $auth->removeAuthItem('viewArrival_Stock');
        $auth->removeAuthItem('createArrival_Stock');
        $auth->removeAuthItem('updateArrival_Stock');
        $auth->removeAuthItem('deleteArrival_Stock');

        //Переименование
//        $authItem = $auth->getAuthItem('viewStockReports');
//        $authItem->setName('viewStockTotalStock');
    }

}





/**
 * Меню Yii2
 */
use app\modules\admin\models\MenuItem;
use app\modules\api\components\ApiPlan1;
class menu_Yii2 extends Migration {

    public function safeUp() {
        /**
         * МЕНЮ
         */
        $root=new MenuItem();
        $root->name='Админка';
        //$root->url='/admin/user'; $root->access = '*'; $root->site_id = MenuItem::PLAN2_SITE_ID;
        $root->makeRoot();

        //$root = MenuItem::findOne(47);
            $item=new MenuItem();
            $item->name='Подраздел1';
            $item->url='/planning/price'; $item->access = 'viewStockElements'; $item->site_id = MenuItem::PLAN2_SITE_ID;
            $item->appendTo($root);

                $item2=new MenuItem();
                $item2->name='Подраздел2';
                $item2->url='/planning/price/equipment'; $item2->access = 'viewStockElements'; $item2->site_id = MenuItem::PLAN2_SITE_ID;
                $item2->appendTo($item);

        //Перемещать также как добавлять
        $item->insertAfter(MenuItem::findOne(1));//или appendTo()


        //$item->save();//изменение
        Yii::$app->cache->flush();
        (new ApiPlan1)->flushCache();
        // Plan1->flushCache работает на beta и прод (не на локалке)

    }

    public function safeDown() {
        /**
         * МЕНЮ
         */
        /** @var MenuItem $item */
        $item = MenuItem::find()->where(['name'=>'Подраздел1'])->orderBy('id DESC')->one();
        $item->deleteWithChildren();

    }
}


/**
 * Меню Yii1
 */
class menu_Yii1 extends CDbMigration {

    public function safeUp() {
        /**
         * МЕНЮ
         */
        $root=new MenuItem;
        $root->name='Админка';
        $root->url='/admin'; $root->access = '*'; $root->site_id = MenuItem::PLAN1_SITE_ID;
        $root->saveNode();

        //$root = MenuItem::model()->findByPk(6);
            $item=new MenuItem();
            $item->name='Подраздел1';
            $item->url='/planning/priceEquipment'; $item->access = 'viewStockElements'; $item->site_id = MenuItem::PLAN1_SITE_ID;
            $item->appendTo($root);

                $item2=new MenuItem();
                $item2->name='Подраздел2';
                $item2->url='/planning/price/equipment'; $item2->access = 'viewStockElements'; $item2->site_id = MenuItem::PLAN1_SITE_ID;
                $item2->appendTo($item);

        //Перемещение
        $item->moveAsLast($root);
        $item2->moveBefore($root);

        app()->cache->flush();
        //todo-s сделать тут очистку cache yii2 по api

    }

    public function safeDown() {
        /**
         * МЕНЮ
         */
        $item = MenuItem::model()->find(['condition' => "name='Подраздел1'", 'order'=>'id DESC',]);
        $item->deleteNode();
        app()->cache->flush();
    }
}





/**
 * MySQL
 */
class MySQL extends CDbMigration {

    public function safeUp() {
        //СОЗДАЕМ
        $this->createTable('{{post}}', [
            'id'                => 'pk',
            'user_id'       => 'int COMMENT "ID вопроса"',
            'name'              => 'string COMMENT "Название"',
            'text'              => 'text COMMENT "Описание"',
            'date'              => 'date COMMENT "Описание"',
            'datetime'              => 'datetime COMMENT "Описание"',
            'is_text_write'     => 'tinyint(1) DEFAULT "0" COMMENT "Вводят в отдельное тектовое поле?"',
        ], 'DEFAULT CHARSET=utf8 ENGINE=InnoDB');
        $this->createIndex('user_id', '{{post}}', 'user_id');
        $this->addForeignKey('fk_post_user_id', '{{post}}', 'user_id', '{{user}}', 'id', 'SET NULL', 'CASCADE');

        $this->addColumn('{{client}}', 'is_quiz_answered', 'tinyint(1) DEFAULT "0" COMMENT "Ответил на опрос?"  AFTER `is_shown`');

        $this->alterColumn('{{client}}', 'answer', 'tinyint(4) DEFAULT NULL COMMENT "Ответил на опрос?"');


        
        $this->createTable('{{post2category}}', [
            'id'                => 'pk',
            'post_id'           => 'integer NOT NULL COMMENT "ID поста"',
            'category_id'       => 'integer NOT NULL COMMENT "ID категории"',
        ], 'DEFAULT CHARSET=utf8 ENGINE=InnoDB');
        $this->createIndex('post_id', '{{post2category}}', 'post_id');
        $this->createIndex('category_id', '{{post2category}}', 'category_id');
        $this->addForeignKey('fk_post_id', '{{post2category}}', 'post_id', '{{post}}', 'id', "CASCADE", "CASCADE");
        $this->addForeignKey('fk_category_id', '{{post2category}}', 'category_id', '{{category}}', 'id', "CASCADE", "CASCADE");

        
        //НАПОЛНЯЕМ
        //Добавляем варианты ответа
        $this->insert('{{post}}',['user_id'=>1,'name'=>'От коллег',]);
        //Обновляем поле
        $this->update('{{post}}', ['date'=>'1458460200'], "id=1");

        $this->execute(<<<SQL
INSERT INTO `kredo_new_quiz_answers` (`variant_answers_id`, `text`) VALUES
(1, 'ываы'),
(2, 'fdvgw');
SQL
        );
    }

    public function safeDown() {
        //$this->delete('{{user}}',['username'=>'admin']); //Если ниже есть dropTable, то delete не требуется

        $this->dropForeignKey('fk_user_id', '{{post}}');
            //$this->dropIndex('user_id', '{{post}}'); //Не нужно, если будет удаляться таблица post или поле user_id.
        $this->dropColumn('{{client}}', 'is_quiz_answered');

        $this->dropTable('{{post}}');
    }
}
class MySQL_Yii2 extends yii\db\Migration
{
    public function safeUp()
    {
        $this->addColumn('{{post}}', 'create_by', $this->integer());
        $this->addColumn('{{post}}', 'create_date', $this->timestamp()->defaultValue(null));

        $this->createIndex('file_create_by', '{{post}}', 'create_by');
    }

    public function safeDown()
    {
        $this->dropColumn('{{post}}', 'create_by');
        $this->dropColumn('{{post}}', 'create_date');
    }
}
