Динамическая форма (DynamicForm)
Есть на сайтах: http://plan2.sp.com/
Документация http://wbraganca.com/yii2extensions/dynamicform-demo1/index

ИНСТРУКЦИЯ ПО УСТАНОВКЕ:
1) Refactor
	modelsWish --  (many)
	modelWish -- 
	Прошу -- 
	просьбу -- 
	OnWish --  (модель)
	wishes --  (relation)
	on-official-note-form (1-2шт) -- 

	official_note_id (2шт) -- (1)  (поле не д.б. required)

2) php composer.phar update wbraganca/yii2-dynamicform
3) Указать поля в 'formFields' => [  и ниже
4) У relation "wishes" сделать ->orderBy('id')
	return $this->hasMany(...)->orderBy('id');



/**************************************
* Доп. функционал
*/
select2. Достаточно добавить js:
	function initSelect2Loading() {
        $('.kv-plugin-loading').hide();
    }
    function initSelect2DropStyle() {}

datepicker
	в .on("afterInsert"...)
		datePickerInit();

JS получить индекс эл-ты. ПР: из Post[1][field] получить 1
	var ind = $(el).attr('name');
	ind=ind.match(/.*?\[(.*?)\].*/);
	ind=ind[1];