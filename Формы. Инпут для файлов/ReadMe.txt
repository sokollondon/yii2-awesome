Формы. Инпут для файлов.
Есть на сайтах: plan2.local

Примеры:
https://plugins.krajee.com/file-advanced-usage-demo

1) View
    use kartik\file\FileInput;

    $form->field($model, 'file')->widget(FileInput::class, [
        'options' => [
            'multiple' => false,
            'accept' => 'application/pdf, image/*, .dwg, .zip, application/zip',
            'required' => true,
        ],
        'pluginOptions' => [
            'showPreview' => false,
            'showUpload' => false,
            'showRemove' => false,
            'allowedFileExtensions' => ['pdf', 'png', 'tiff', 'jpg', 'dwg', 'zip', 'jpeg'],
            'elErrorContainer' => '#errorBlock',
        ],
    ])


2) Модель:
    public $file,

    public function rules()
    {
        $rules = [
            [['file'], 'file', 'extensions' => 'pdf, png, jpg, tiff, dwg, zip, jpeg',
                'maxSize' => 30*1024*1024, 'tooBig' => 'Максимальный размер 30 Мб', 'checkExtensionByMimeType' => true //Поставить false, если dwg-файлы не будут проходить валидацию
            ],
        ];
        return parent::rules($rules);
    }

    public function getFiles()
    {
        return $this->hasMany(IssueFile::class, ['issue_id' => 'id']);
    }

3) Контроллер:

    public function actionName()
    {
        ...
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            ...
            if ($model->save()) {
                $result = $this->saveFile($model, $model->file);

                if ($result['error']) {
                    Yii::$app->getSession()->addFlash('danger', "Загрузка файла завершена с ошибкой: ".$result['error']);
                }
                if ($result['success']) {
                    ...
                }
            }
        }
    }

    public function saveFile(&$model, $file=null, $type=false, $newDate = false)
    {
        $result = [];
        if ($file && $file->tempName) {
            if ($model->validate(['file']) && StorageHelper::checkConnection()) {
                $s3 = StorageHelper::getInstance();
                if ($s3->doesBucketExist(Yii::$app->params['s3']['defaultBucket'])) {
                    $dir = StorageHelper::getSavePath(IssueFile::UPLOAD_FILES_PATH);
                    $newFile = new IssueFile();
                    $newFile->issue_id = $model->id;
                    $newFile->path = $dir;
                    $newFile->original_name = $model->file->name;
                    $newFile->type_id = $type ?: ApprovalIssue::BASE_TYPE;
                    $newFile->stage_number = $model->current_stage_number;
                    $fileName = $model->id . '_' . $newFile->type_id . '_' . uniqid() . '.' . $model->file->extension;
                    $newFile->name = $fileName;
                    $newFile->add_date = $newDate ?: new yii\db\Expression('NOW()');

                    if ($newFile->type_id == ApprovalIssue::BASE_TYPE && $model->baseFiles && $model->status_id == ApprovalIssue::STATUS_IN_PROCESS) {
                        if ($model->baseFiles[0]->stage_number == $model->current_stage_number) {
                            $model->baseFiles[0]->deleteFile();
                            if ($model->baseFiles[0]->delete()) Yii::$app->getSession()->addFlash('warning', 'Старый файл удален.');
                        }
                    }
                    if ($newFile->type_id == ApprovalIssue::AGREED_TYPE && $model->agreedFiles && $model->status_id == ApprovalIssue::STATUS_CHECKING) {
                        if ($model->agreedFiles[0]->stage_number == $model->current_stage_number) {
                            $model->agreedFiles[0]->deleteFile();
                            if ($model->agreedFiles[0]->delete()) Yii::$app->getSession()->addFlash('warning', 'Старый файл удален.');
                        }
                    }

                    $insert = $s3->putObject(
                        [
                            'Bucket' => Yii::$app->params['s3']['defaultBucket'],
                            'Key' => $dir.$fileName,
                            'SourceFile' => $model->file->tempName,
                        ]
                    );

                    if (!($insert["@metadata"]["statusCode"] == '200' && $newFile->save())) {
                        $result['error']= 'Ошибка в процессе сохранения.';
                    } else {
                        $result['success'] = 'Новый файла загружен.';
                    }
                }
            } else {
                $result['error'] = implode(',', $model->getErrorSummary(true));
                if (!$result['error']) $result['error'] = 'Нет доступа к файловому хранилищу';
            }
        }
        return $result;
    }

4) Миграция для модели файлов (пример)

    $this->createTable('{{sp.issue_file}}', [
        'id' => 'pk',
        'issue_id' => $this->integer()->notNull(),
        'path' => 'string',
        'name' => 'varchar(255) DEFAULT NULL',
        'original_name' => 'varchar(255) DEFAULT NULL',
        'type_id' => 'int DEFAULT 1',
        'add_date' => 'date',

        //Стандартные поля
        'create_by' => 'int DEFAULT NULL',
        'create_date' => 'timestamp',
        'update_by' => 'int DEFAULT NULL',
        'update_date' => 'timestamp',
    ]);

    $this->execute("comment on column sp.issue_file.issue_id is 'ID предмета Согласования'");
    $this->execute("comment on column sp.issue_file.path is 'Путь'");
    $this->execute("comment on column sp.issue_file.name is 'Имя файла'");
    $this->execute("comment on column sp.issue_file.type_id is 'Тип файла'");
    $this->execute("comment on column sp.issue_file.add_date is 'Дата загрузки'");

    //Внешние ключи
    $this->createIndex('issue_file__issue_id', '{{sp.issue_file}}', 'issue_id');
    $this->addForeignKey('fk_issue_id', '{{sp.issue_file}}', 'issue_id', '{{sp.approval_issue}}', 'id', 'SET NULL', 'CASCADE');

    //Стандартные поля
    $this->createIndex('issue_file__create_by', '{{sp.issue_file}}', 'create_by');
    $this->createIndex('issue_file__update_by', '{{sp.issue_file}}', 'update_by');


5) Модель для файлов

    namespace app\modules\sp\models;

    use StorageHelper;
    use yii;
    use yii\data\ActiveDataProvider;

    /**
     * @property integer $id
     * @property integer $issue_id
     * @property string $path
     * @property string $name
     * @property string $original_name
     * @property integer $type_id
     * @property integer $stage_number
     * @property string $add_date
     *
     * @property ApprovalIssue $issue
     */
    class IssueFile extends \app\components\ActiveRecordDefault
    {
        const UPLOAD_FILES_PATH = 'files/approval/';

        public static function tableName()
        {
            return 'sp.issue_file';
        }
        public function rules()
        {
            $rules = [
                [['issue_id'], 'required'],
                [['issue_id', 'type_id'], 'default', 'value' => null],
                [['issue_id', 'type_id', 'stage_number'], 'integer'],
                [['add_date'], 'safe'],
                [['path', 'name', 'original_name'], 'string', 'max' => 255],
                [['issue_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApprovalIssue::class, 'targetAttribute' => ['issue_id' => 'id']],
            ];
            return parent::rules($rules);
        }
        public function attributeLabels()
        {
            $labels = [
                'issue_id' => 'ID предмета Согласования',
                'path' => 'Путь',
                'name' => 'Имя файла',
                'original_name' => 'Original Name',
                'type_id' => 'Тип файла',
                'add_date' => 'Дата загрузки',
            ];
            return parent::attributeLabels($labels);
        }

        public function search()
        {
            $this->load(Yii::$app->request->queryParams);
            $query = self::find()->with([]);

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'id' => SORT_DESC,
                    ],
                ],
            ]);

            $query->andFilterWhere([
                'id' => $this->id,
                'issue_id' => $this->issue_id,
                'type_id' => $this->type_id,
                'add_date' => $this->add_date,
            ]);
            $query->andFilterWhere(['ILIKE', 'path', $this->path]);
            $query->andFilterWhere(['ILIKE', 'name', $this->name]);
            $query->andFilterWhere(['ILIKE', 'original_name', $this->original_name]);

            return $dataProvider;
        }

        public function getFileLink()
        {
            $s3 = StorageHelper::getInstance();
            $bucket = Yii::$app->params['s3']['defaultBucket'];

            $key = $this->path . $this->name;
            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $key,
                'ResponseContentDisposition' => 'inline; filename="'.$this->original_name.'"',
            ]);
            $request = $s3->createPresignedRequest($cmd, '+12 hours');

            return (string) $request->getUri();
        }

        public function getFileIcon()
        {
            $icon = "far fa-file";
            if ($this->original_name) {
                $ext = mb_strtolower(mb_substr(mb_strrchr($this->original_name, '.'), 1));
                $extensions = ['pdf' => '-pdf', 'png' => '-image', 'tiff' => '-image', 'jpg' => '-image', 'jpeg' => '-image', 'zip' => '-archive'];
                $icon .= $extensions[$ext] ?? '';
            }
            return $icon;
        }

        public function deleteFile()
        {
            $s3 = StorageHelper::getInstance();
            $bucket = Yii::$app->params['s3']['defaultBucket'];
            $key = $this->path . $this->name;

            if ($s3->doesObjectExist($bucket, $key)) {
                $s3->deleteObject([
                    'Bucket' => $bucket,
                    'Key' => $key,
                ]);
            }
        }

        public function getIssue()
        {
            return $this->hasOne(ApprovalIssue::class, ['id' => 'issue_id']);
        }
    }


TODO: 
    Создать родительский класс для модели файлов


