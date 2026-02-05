<?php

use kartik\mpdf\Pdf;
use yii\web\Controller;

class ReportPdfController extends Controller
{
    /**
     * @link https://github.com/kartik-v/yii2-mpdf
     */
    public function actionPrint($id)
    {
        $pdf = new Pdf([
            'content' => $this->renderPartial('_print'),
            'cssInline' => file_get_contents(Yii::getAlias('@app/assets/css/pdf.css')),
            'marginLeft' => 10, 'marginRight' => 9, 'marginTop' => 5, 'marginBottom' => 10, 'marginHeader' => 0, 'marginFooter' => 5,
            'methods' => [
                'SetFooter' => [asDate().'||Страница {PAGENO} из {nb}'],
            ],
        ]);
        $pdf->filename = 'pdf.pdf';
        //return $this->renderEmpty($pdf);
        return $pdf->render();
    }
}

/**
 * Дополнительно
 */
$pdf = new Pdf([
    'cssFile' => '',
    'orientation'=>'L',
    'methods' => [
        'SetHeader'=>['Report_Header'],
    ]
]);

//$isHtml
    $content = $this->renderPartial('_print', ['isHtml'=>$isHtml]);
    //...
    if($isHtml){
        $pdf->cssInline .= 'body, .container{width: 722px;margin: 0 auto;} body{margin-top: 20px}' .
            'table tr:hover td{background: #C4C4C4;}';
        return $this->renderEmpty($pdf);
    }



/**
 * ШАБЛОН
 */
?>
    <h2 class="text-center">Техническое задание <strong>№111</strong></h2>
    <br>

    стр1<br>
    стр2<br>
    <p>стр3 p</p>
    <p class="bg-light">стр4 серый фон</p>
    <div class="text-right small">
        стр5 small<br>
        стр6<br>
    </div>

    <div>
        <div style="width: 100px; float: left;">Слева</div>
        <div>Справа</div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-xs-6 p-0">
            </div>
            <div class="col-xs-6 p-0">
                Иногда нужно поправить последнюю col style="width: 24.9%;"
            </div>
        </div>
    </div>

    FLOAT: RIGHT работает не всегда в pdf. Только если слева "p" или справа img
    <div style="float: right;width: 320px;" class="text-right">Справа</div>
    <p class="m-0">Слева</p>

    <div class="double_strip_header">Заголовок</div><br>
    <!-- .table-p-1  -->
    <table class="big table-p-0 table-width-auto align-top">
        <tr>
            <td>11</td>
            <td>22</td>
        </tr>
        <tr>
            <td>33</td>
            <td>44</td>
        </tr>
    </table>
    <table>
        <tr>
            <td width="200">11</td>
            <td>22</td>
        </tr>
    </table>

    <table class="big p-0 text-center" style="width: 300px;">
        <tr><td class="border-bottom">Зуй Андрей Анатольевич</td></tr>
        <tr><td><small>фамилия, имя, отчество</small></td></tr>
    </table>

    <img src="<?=Helper::getImgBase64('/images/icon.png')?>" style="max-width: 14px;" alt="">
    <?=($isShow ? '<img src="'.Helper::getImgBase64('/images/icon.png').'" style="max-width: 14px;" >' : '')?>

<?php
/**
 * Производительность
 *//*
(Yii1)
(↓ 1000-5000ms) Добавление стилей kv-mpdf-bootstrap.min.css
*/


/**
 * ERRORS
 *//*
Распадывается col-xs-
    Если поправить 'marginLeft'=>10, 'marginRight'=>9 на 1

(Yii1)
Не показывает картинки
    //$mPDF->showImageErrors = true;//включить отображение ошибок

    Error parsing image file - image type not recognised, and not supported by GD imagecreate
        СИТУАЦИЯ: На локалке работает, а на сервере не показывает без указания прокси
        ВАРИАНТ №1: <img src="<?=Helper::getImgBase64('/images/icon.png')?>">
        ВАРИАНТ №2: <?=CHtml::image(app()->basePath.'/../images/logo2.png','',['width'=>'150px;'])?>

*/


/**
 * Переход с Yii1 на Yii2
 *//*
align-right -- text-right
*/