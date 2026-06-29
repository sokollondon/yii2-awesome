Формы. Инпут для файлов.
Есть на сайтах: plan2.local

Примеры:
https://plugins.krajee.com/file-advanced-usage-demo

1) Refactor 
    FileIssue -- 
    sp.file_issue -- 
    file_issue -- 
    ApprovalIssue --  [AA](модель)
    issue_id -- 
    baseFile --  [AA](relation)
    Файл --

2) Миграция для модели файлов

    $this->createTable('{{sp.file_issue}}', [
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
    $this->execute("comment on column sp.file_issue.path is 'Путь'");
    $this->execute("comment on column sp.file_issue.name is 'Имя файла'");
    $this->execute("comment on column sp.file_issue.add_date is 'Дата загрузки'");

    $this->createIndex('file_issue__issue_id', '{{sp.file_issue}}', 'issue_id');
    $this->createIndex('file_issue__create_by', '{{sp.file_issue}}', 'create_by');
    $this->createIndex('file_issue__update_by', '{{sp.file_issue}}', 'update_by');

3) Модель для файлов

    namespace app\modules\sp\models;

    use StorageHelper;
    use yii;

    /**
     * @property integer $id
     * @property integer $issue_id
     * @property string $path
     * @property string $name
     * @property string $original_name
     * @property integer $type_id
     * @property string $add_date
     */
    class FileIssue extends \app\components\ActiveRecordDefault
    {
        const UPLOAD_FILES_PATH = 'files/approvalIssue/';

        public static function tableName()
        {
            return 'sp.file_issue';
        }
        public function rules()
        {
            $rules = [
                [['issue_id'], 'required'],
                [['issue_id', 'type_id'], 'default', 'value' => null],
                [['issue_id', 'type_id'], 'integer'],
                [['add_date'], 'safe'],
                [['path', 'name', 'original_name'], 'string', 'max' => 255],
                [['issue_id'], 'exist', 'skipOnError' => true, 'targetClass' => ApprovalIssue::class, 'targetAttribute' => ['issue_id' => 'id']],
            ];
            return parent::rules($rules);
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
    }

4) Поправить какие форматы файла нужны

5) View form
    use kartik\file\FileInput;

    <?
    $form = ActiveForm::begin([
        'id' => 'formApprovalIssue',
    ]);
    ?>

    <?
    $label = 'Файл ';
    if ($file = $model->specFile) {
        $label .= Html::a('<i class="'.$file->getFileIcon().'"></i>',
            $file->getFileLink(), ['target'=>'_blank', 'title' => $file->original_name]);
    }
    echo $form->field($model, 'f_baseFile')->widget(FileInput::class, [
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
            'elErrorContainer' => '.field-'.Html::getInputId($model, 'f_baseFile').' .help-block',
        ],
    ])->label($label);?>

    //Нужно если отправляется по ajax
    <?=Html::submitButton('Сохранить', [
        'class' => 'btn btn-primary right sendAjax',
        'href' => \app\components\Url::current(),
        'data-get_data_func' => 'getFormData',
    ])?>
    <script>
        function getFormData() {
            $.ajaxSettings.contentType = false;
            $.ajaxSettings.processData = false;
            return new FormData(<?=$form->id?>);
        }
    </script>

6) View index $columns
    [
        'attribute'=>'f_baseFile',
        'label' => 'Файл',
        'hAlign' => 'center',
        'width' => '100px',
        'contentOptions' => ['class' => 'showOnHover relative f_baseFile'],
        'content'=>function(ApprovalIssue $model){
            $res = '';
            $file = $model->baseFile;
            if ($file) {
                $res = Html::a('<span style="font-size:20px;"><i class="'.$file->getFileIcon().'"></i></span>',
                    $file->getFileLink(), ['target'=>'_blank', 'title' => $file->original_name, 'class' => 'link-inherit opacity']);
                if (ApprovalIssue::canUpdate()) {
                    $res .= Html::a('<i class="fas fa-times"></i>', ['delete-file', 'id'=>$file->id],
                        ['class' => 'sendAjax hide absolute', 'title' => 'Удалить файл', 'style' => 'font-size:10px;top:2px;margin-left:5px;',
                            'data-confirm' => 1, 'data-updateOk' => "[data-key=$model->id] .f_baseFile",
                        ]);
                }
            }
            return $res;
        },
        'filter' => false,
    ],

7) Модель
    /**
     * @property FileIssue $baseFile
     */
    class ApprovalIssue
    {
        public $f_baseFile;

        public function rules()
        {
            $rules = [
                [['f_baseFile'], 'file', 'extensions' => ['pdf', 'png', 'tiff', 'jpg', 'dwg', 'zip', 'jpeg'],
                    'maxSize' => 30*1024*1024, 'tooBig' => 'Максимальный размер 30 Мб', 'checkExtensionByMimeType' => true //Поставить false, если dwg-файлы не будут проходить валидацию
                ],
            ];
            return parent::rules($rules);
        }

        public function getBaseFile()
        {
            return $this->hasOne(FileIssue::class, ['issue_id' => 'id'])->andWhere(['type_id'=> ApprovalIssue::FILE_BASE_TYPE]);
        }
    }

8) Контроллер:

    public function actionName()
    {
        ...
        if ($model->load(Yii::$app->request->post())) {
            $model->f_baseFile = UploadedFile::getInstance($model, 'f_baseFile');
            ...
            if ($model->save()) {
                $result = $this->saveFile($model, $model->f_baseFile);

                if ($result['error']) {
                    Yii::$app->session->addFlash('danger', "Загрузка файла завершена с ошибкой: ".$result['error']);
                }
                if ($result['success']) {
                    ...
                }
            }
        }
    }

    public function saveFile(&$model, $file = null, $type = false, $newDate = false)
    {
        /** @var ApprovalIssue $model */
        $result = [];
        if ($file && $file->tempName) {
            if ($model->validate(['f_baseFile']) && StorageHelper::checkConnection()) {
                $s3 = StorageHelper::getInstance();
                if ($s3->doesBucketExist(Yii::$app->params['s3']['defaultBucket'])) {
                    $dir = StorageHelper::getSavePath(FileIssue::UPLOAD_FILES_PATH);
                    $newFile = new FileIssue();
                    $newFile->issue_id = $model->id;
                    $newFile->path = $dir;
                    $newFile->original_name = $file->name;
                    $newFile->type_id = $type ?: ApprovalIssue::FILE_BASE_TYPE;//todo можно заменить на заглушку 1, если к модели прикрепляется только файл baseFile. А если несколько, см. ContractFiles::getTypeByFieldName()
                    $fileName = $model->id.'_'.$newFile->type_id.'_'.uniqid().'.'.$file->extension;
                    $newFile->name = $fileName;
                    $newFile->add_date = $newDate ?: new yii\db\Expression('NOW()');

                    if ($newFile->type_id == ApprovalIssue::FILE_BASE_TYPE && $model->baseFile) {
                        $model->baseFile->deleteFile();
                        if ($model->baseFile->delete()) Yii::$app->session->addFlash('warning', 'Старый файл удалён.');
                    }

                    $insert = $s3->putObject(
                        [
                            'Bucket' => Yii::$app->params['s3']['defaultBucket'],
                            'Key' => $dir.$fileName,
                            'SourceFile' => $file->tempName,
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

    public function actionDeleteFile($id)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        if (!ApprovalIssue::canUpdate()) throw new \app\components\ForbiddenHttpException();
        $model = FileIssue::findOne($id);
        $model->deleteFile();
        return $model->delete() ? ['result' => 'success'] : ['result' => 'save_error'];
    }

    Если есть actionDelete() то в него нужно добавить...


TODO: 
    Создать родительский класс для модели файлов
    deleteFile() поместить внутрь delete()


