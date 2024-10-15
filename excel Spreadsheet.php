<?php /** @noinspection PhpUnreachableStatementInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedClassInspection */
/*
 * Обучение на примерах: открыть папку vendor/phpoffice/phpspreadsheet/samples/Basic
 * GitHub https://github.com/PHPOffice/PhpSpreadsheet/issues
 */

/**
 * Миграция с \PhpOffice\PhpSpreadsheet\Spreadsheet
 */
/*
1) Автоматически все файлы в папке:
    cd protected\controllers
    php D:\OSPanel\domains\planning1-2.local/vendor/phpoffice/phpspreadsheet/bin/migrate-from-phpexcel
2) Потом вручную
$objPHPExcel = app()->XPHPExcel... (comment + 3 строчки -- 2)
...$objWriter->save('php://output'); (блок -- 1 строчку)

borders
    'allborders' -- 'allBorders'
    'style' -- 'borderStyle'
fill
    'type' -- 'fillType'

 */


class ReportSssController extends Controller
{
    public function actionMechanicsXLS(){
        $data=[
            '1'=>'One',
            '2'=>'Two',
            '3'=>'Three',
            '4'=>'Four',
        ];


        //$this->layout = '/empty';return $this->renderContent('');
        set_time_limit(0);
        ini_set("memory_limit", "512M");
        $filename = "таблица";

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Лист');
        date_default_timezone_set('UTC');//для вывода дат
        //Для печати
        $sheet->getPageMargins()->setTop(0.2)->setRight(0.2)->setLeft(0.2)->setBottom(0.2);
        //$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        //Заголовок таблицы
        $sheet->mergeCells("A1:G1");
        $sheet->setCellValue("A1", $filename);
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getStyle("A1:G1")->applyFromArray(ExcelHelper::style(true,null,true));
        //Шапка
        $sheet->setCellValue('A2', 'Имя')->getColumnDimension('A')->setWidth(14);
        $sheet->setCellValue('B2', 'ID');
        $sheet->setCellValue('C2', 'Кол-во');
        $sheet->setCellValue('G2', 'Итого');
//        $sheet->getRowDimension(2)->setRowHeight(30);//Высота
//        $sheet->getStyle("A2:G2")->getAlignment()->setWrapText(true);//Перенос строк
//        $sheet->getStyle("A2:G2")->getAlignment()->setIndent(1);//Отступ

        $pos = 3;
        foreach ($data as $ind=>$item) {
            $sheet->setCellValue("A$pos", $item);
            $sheet->setCellValue("B$pos", $ind);
            $sheet->setCellValue("C$pos", 2);
            $sheet->setCellValue("G$pos", "=B".$pos."*C".$pos);
            $pos++;
        }
        //Сумма по вертикали
        if($pos>3){
            $sheet->setCellValue("G$pos", "=SUM(G3:G".($pos-1).")");
            $sheet->getStyle("G$pos")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);//Числовой формат с пробелами
        }


        /*
         * Оформление
         */
        //вся таблица
        $sheet->getStyle("A2:G".$pos)->applyFromArray(ExcelHelper::styleBorderAll());
        //Шапка
        $sheet->getStyle("A2:G2")->applyFromArray(ExcelHelper::styleHeaderRow());
        //Итого строка
        $sheet->getStyle("A$pos:G$pos")->applyFromArray(ExcelHelper::styleBorderTop());
        //Итого колонка
        $sheet->getStyle("G2:G".$pos)->applyFromArray(ExcelHelper::style(true));

        //$this->layout = '/empty';return $this->renderContent('');
        return ExcelHelper::save($filename, $spreadsheet);





        /**
         * Yii1
         */
        //$this->renderPartial('//site/index', [], false, true);app()->end();









        //todo-s Раскоментировать что проверено на PhpSpreadsheet
        /** @var PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet */
        /*********************************
         * Доп. оформление
         */
//        //Дата
//        $sheet->setCellValue("A".$pos, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime("2016-07-09")))->getStyle("A".$pos)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY);
        //Числовой формат с пробелами
        $sheet->getStyle("G$pos")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            //->setFormatCode(ExcelHelper::FORMAT_INT);
//        //Денежные форматы
//        $sheet->getStyle('A'.$pos)->getNumberFormat()->setFormatCode('#,##0.00 [$р.-419]'); //р.
//        $sheet->getStyle('A'.$pos)->getNumberFormat()->setFormatCode('#,##0.00 [$$-409]'); //$
//        $sheet->getStyle('A'.$pos)->getNumberFormat()->setFormatCode('#,##0.00 [$€-1809]'); //€
//
//
//        //Разделительные полосы
//        $styleCell4borderTop=[];
//        $styleCell4borderTop[]='A'.$pos.':K'.$pos;//разделительная полоса
//        foreach ($styleCell4borderTop as $cell) {
//            $sheet->getStyle($cell)->applyFromArray(['borders' => ['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]]]);
//        }
//
//        //Разворот(поворот) текста
//        $sheet->getStyle('A2')->getAlignment()->setTextRotation(90);
        //Выравнивание
        $sheet->getStyle("A$pos")->getAlignment()->setHorizontal('right');
        //Цвет текста
        $sheet->getStyle("A2")->getFont()->getColor()->applyFromArray(['rgb' => '00B050']);
//        //Авто ширина
//        $sheet->getColumnDimension('A')->setAutoSize(true);
//        //Шрифт
//        $sheet->getStyle("A2:F$pos")->getFont()->setName('Times New Roman');



        //Подзаголовок h1
        $sheet->setCellValue("A$pos", "Подзаголовок")->getStyle("A$pos")->applyFromArray(ExcelHelper::styleH1());



//        //Вставка html в ячейку (выделение жирным и т.д.)
//        $rich_text = ' ';
//        if($post->text){
//            $html = mb_convert_encoding(html_entity_decode($post->text), 'HTML-ENTITIES', 'UTF-8');
//            //$html = "<!--suppress HtmlDeprecatedTag, HtmlDeprecatedAttribute --><font size='14'>$html</font>";
//            $rich_text = (new \PhpOffice\PhpSpreadsheet\Helper\Html())->toRichTextObject($html);
//        }
//        $sheet->setCellValue("F$pos", $rich_text);
//            //Чтобы работал <br>, добавить: $sheet->getStyle("F$pos")->getAlignment()->setWrapText(true);








        /*********************************
         * Доп. функционал
         */
//        $sheet->setAutoFilter('A1:G1'); //Автофильтры

        //Динамическое добавление колонок (переменная вместо прописанных букв)
        //Всегда лучше делать? Кроме случаев, когда много формул и придётся много колонок добавлять в переменные как $lastCol
        $col = 0;
        ExcelHelper::c(++$col);
        $lastCol = ExcelHelper::c($col);
        ExcelHelper::c('A',2); //вернёт C (на 2 колонки больше A)


        //Группировка строк
        $sheet->setShowSummaryBelow(false); //задается 1 раз вверху
        $pos=4;
        $sheet->getRowDimension($pos)->setOutlineLevel(1)->setVisible(false);
        $sheet->getRowDimension($pos)->setRowHeight(16);
            $sheet->getStyle("A$pos:G$pos")->applyFromArray(ExcelHelper::styleFillGray());
        //Группировка колонок (скрытие)
        $sheet->setCellValue("B2", 'Заголовок')->getColumnDimension('B')->setWidth(5)
            ->setOutlineLevel(1)->setVisible(false)->setCollapsed(true);

        //Сумма при группировке строк
        $pos_sum=[];
        $pos_sum[]=$pos;
        if($pos_sum){
            $sheet->setCellValue("G$pos", "=G".implode('+G', $pos_sum));
        }

        //Примечание
        $note = new \PhpOffice\PhpSpreadsheet\RichText\RichText; $note->createText("Примечание"); $sheet->getComment("A1")->setText($note);
            //(!) В LibreOffice до v6.3.4 постоянно открыт, закрывает текст. Данил обещал обновиться
            //->setWidth(200)->setHeight(90)

        return '';
    }

    
















    public function severalLists()
    {
    }


    public function readXls()
    {
    }
}




use PhpOffice\PhpSpreadsheet\Style\Border;
class ExcelHelper {
    const FORMAT_INT = '#,##0';

    public static function c($i, $plus=0)
    {
        if($plus){
            $i = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($i);
            $i += $plus;
        }
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
    }

    public static function save($filename, $spreadsheet)
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');//IE 9
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        if(Yii::getVersion() >= 2){/** @noinspection PhpUndefinedFieldInspection */
            Yii::$app->response->isSent = true;
        }
        return '';
    }

    public static function style($bold = false, $size=null, $center=false)
    {
        $s=[];
        $s = array_merge_recursive($s, ['font' => [
            'bold' => $bold,
        ]]);
        if($size){
            $s = array_merge_recursive($s, ['font' => [
                'size' => $size,
            ]]);
        }
        if($center){
            $s = array_merge($s, ['alignment' => [
                'horizontal' 	=> \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   	=> \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]]);
        }
        return $s;
    }

    public static function styleH1()
    {
        return self::style(true, 14);
    }

    public static function styleH2()
    {
        return self::style(true, 12);
    }

    public static function styleHeaderRow()
    {
        return [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'rgb' => 'FCE5CD',
                ]
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
    }

    public static function styleFillGray()
    {
        return [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'rgb' => 'EFEFEF',
                ]
            ]
        ];
    }

    public static function styleBorderAll()
    {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ];
    }

    public static function styleBorderTop($thin=false)
    {
        return [
            'borders' => [
                'top'     => [
                    'borderStyle' => $thin ? Border::BORDER_THIN : Border::BORDER_MEDIUM,
                ],
            ],
        ];
    }
}