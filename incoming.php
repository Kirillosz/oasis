<?

define("NO_AGENT_CHECK", true);
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/backup/";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
global $USER;
use Bitrix\Main\Loader;
use Bitrix\Crm\Service;
use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Container;

//тут записывам json
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);
global $data;
global $_REQUEST;
$date = date('Y-m-d H:i:s');
$data = file_get_contents('php://input');
$arr = json_decode($data,true);
if(!empty($arr)):
    if(array_key_exists('NumberContract',$arr['OAZIS'])):
        file_put_contents(getcwd() . '/incoming/contract_'.$date.'.json', $data, FILE_APPEND);  
    else:   
file_put_contents(getcwd() . '/incoming/meeting_'.$date.'.json', $data, FILE_APPEND);
    endif;
//include ('meeting_add.php'); !!!на будущее чтобы разбить логику на файлы!f!!
//createSmartElement($arr);
//if(createSmartElement($arr)):
  //  file_put_contents(getcwd() . '/log/succes_'.$date.'.log', 'success calling createSmartElement;', FILE_APPEND);
//else: 
  //  file_put_contents(getcwd() . '/log/fail_'.$date.'.log', 'failed calling createSmartElement;', FILE_APPEND);
//endif;
else:
file_put_contents(getcwd() . '/incoming/error_'.$date.'.json', 'error: object не содержит ключа OAZIS;', FILE_APPEND);
endif;

//global $arr;
//создаем фабрики
if($arr['OAZIS']['USER_TABLE']) {
    //Создание фабрики 
    //use Bitrix\Crm\Service\Container;s
    Loader::IncludeModule("crm");
    $factory = Container::getInstance()->getFactory(156); //Идентификатор типа CRM 
//проверяем элемент на существование
$elementExists = $factory->getItems(['filter' => ['UF_CRM_18_1701859759995' => $arr['OAZIS']['OASIS_ID']]]);
$elementIdCrm = explode('_', $arr['OAZIS']['ID_CRM']);
$elementIdCrm = $factory->getItems(['filter' => ['ID' => $elementIdCrm[2]]]);
	if($elementExists || $elementIdCrm):
	//если существует то пишем что это операция обновления данных для этого получаем элемент getItem
    if($elementExists):
$elementExistsId = $elementExists[0]["id"];
    else:
        $elementExistsId = $elementIdCrm;
    endif;
$mode = 'edit';
$new_item = $factory->getItem($elementExistsId);
$element_id = $elementExistsId;
	else:
	//если нет создаем
$mode = 'add';
$new_item = $factory->createItem();
	endif; 
	//Создание/изменение элемента смарт-процесса
    $meeting_name = $arr['OAZIS']['EVENT'];
								 //$new_item = $factory->eval($mode);
    $new_item->setTitle($meeting_name);
    $new_item->set("ASSIGNED_BY_ID", 3037); //Задаём ответственного
    $new_item->set("UF_CRM_18_1701677872870", $arr['OAZIS']['DATE']);
    $new_item->set("UF_CRM_18_1701677915765", $arr['OAZIS']['DATE_KONEC']);
    $new_item->set("UF_CRM_18_1701678093989", $arr['OAZIS']['PLACE']);
    $new_item->set("UF_CRM_18_1701678131893", $arr['OAZIS']['FORMAT_EVENT']);
    $new_item->set("UF_CRM_18_1701951036443", $arr['OAZIS']['EXTRA_INFO']);
    $new_item->set("UF_CRM_18_1701951058152", $arr['OAZIS']['EXTRA_INFO_PUBLIC']);
  $new_item->set("UF_CRM_18_1701859759995", $arr['OAZIS']['OASIS_ID']);
    //если не пусто страна
  if (!empty($arr['OAZIS']['COUNTRY'][0]['OASIS_ID']) || $arr['OAZIS']['COUNTRY'][0]['OASIS_ID'] != ''):
$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_1838");
$arFilter = Array("IBLOCK_ID"=>IntVal(190), "PROPERTY_1838_VALUE"=>$arr['OAZIS']['COUNTRY'][0]['OASIS_ID']);
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>9999), $arSelect);
while($ob = $res->GetNextElement()) 
{ 
 $arField = $ob->GetFields();  
}
$new_item->set("UF_CRM_18_1702040773",  $arField['ID']);
    endif;
    //сохраняем элемент смарт процесса и после добавляем связанные списки
    if($new_element = $new_item->save()):
        $element_id = $new_item->get('ID');
        else: file_put_contents(getcwd() . '/log/error_on_save_'.$date.'.log', 'error added element: '.$new_element->LAST_ERROR, FILE_APPEND);
        endif;
//если не пусто цели
if (!empty($arr['OAZIS']['PURPOSE']) || $arr['OAZIS']['PURPOSE'] != ''): 
    $n = 0;
    while($n < count($arr['OAZIS']['PURPOSE'])): 
        $name = "Цель № ".$n." по мероприятию ID-".$arr['OAZIS']['EVENT'];
$prop['1882'] = $arr['OAZIS']['PURPOSE'][$n]['PURPOSE'];
$prop['1883'] = $arr['OAZIS']['PURPOSE'][$n]['RESULT'];
$prop['1884'] = $arr['OAZIS']['PURPOSE'][$n]['FAILS'];
$prop['1885'] = $arr['OAZIS']['PURPOSE'][$n]['LEARNED'];
$prop['1886'] = $element_id;
$n++;
   
        Loader::includeModule('iblock'); 
        //добавляем эл инфоблока
        //$new_iblock_el_name = 'Hello again!';
        $el = new CIBlockElement;
        //$PROP = array();
        //$PROP[1881] = $element_id;  // свойству с кодом 12 присваиваем значение "Белый"
        //$PROP[3] = 38;        // свойству с кодом 3 присваиваем значение 38
        $arLoadProductArray = Array(
            "MODIFIED_BY"    =>  3037, // элемент изменен текущим пользователем
            //"IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
            "IBLOCK_ID"      => 198,
            "PROPERTY_VALUES"=> $prop,
            "NAME"           => $name,
            "ACTIVE"         => "Y",            // активен
            "PREVIEW_TEXT"   => $name,
            //"DETAIL_TEXT"    => "текст для детального просмотра",
            //"DETAIL_PICTURE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/image.gif")
        );
        $arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_1886");
        $arFilter = Array("IBLOCK_ID"=>IntVal(198), "NAME"=>$name, "PROPERTY_1886_VALUE"=>$element_id);
        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>9999), $arSelect);
        while($ob = $res->GetNextElement()) 
        { 
         $arFields = $ob->GetFields();  
        }
        if($arFields['ID'] != '' || !empty($arFields['ID'])):
        if($PURPOSE_ID = $el->Update($arFields['ID'], $arLoadProductArray)):
            file_put_contents(getcwd() . '/log/successUpdateIblockElement_'.$date.'.log', 'success update element цели '.$PURPOSE_ID, FILE_APPEND);
        else:
            file_put_contents(getcwd() . '/log/failedUpdateIblockElement_'.$date.'.log', 'failed update element цели '.$el->LAST_ERROR, FILE_APPEND);
        endif;
            else:
                if($PURPOSE_ID = $el->Add($arLoadProductArray)):
                    file_put_contents(getcwd() . '/log/successAddedIblockElement_'.$date.'.log', 'success added element'.$PURPOSE_ID, FILE_APPEND);
                else:
                echo "Error: ".$el->LAST_ERROR;
                file_put_contents(getcwd() . '/log/failedIAddedIblockElement_'.$date.'.log', 'failed added element'.$el->LAST_ERROR, FILE_APPEND);
                endif;
            endif;
endwhile;
   
   
endif;


	//добавляем участников
if (!empty($arr['OAZIS']['USER_TABLE']) || $arr['OAZIS']['USER_TABLE'] != ''): 
    $n = 0;
    while($n < count($arr['OAZIS']['USER_TABLE'])): 
        //указываем персону
	if($arr['OAZIS']['USER_TABLE'][$n]['PERSON'] || $arr['OAZIS']['USER_TABLE'][$n]['PERSON'] != ''):
        $arNewContact=array(
        "UF_CRM_1703446692577" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['OASIS_ID'],
        "UF_CRM_1703516205221" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['NSI_ID'],
        "NAME" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['NAME'],
        "LAST_NAME" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['LAST_NAME'],
        "SECOND_NAME" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['SECOND_NAME'],
        "DISPLAY_NAME" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['NAME'],
        "GENDER" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['GENDER'],
        "EMAIL" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['EMAIL'],
        "PHONE" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['PHONE']
        //"TAGS" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['TAGS'] тут какие о теги
            //"company-id"
            //"ASSIGNED_BY_ID"
            //"POST"
        );
        $contact = new CCrmContact();
        //ищем контакт среди имеющихся, если нет то создаем новый
        $res = CCrmContact::GetList($arOrder = ['ID'=>'DESC'], $arFilter = ['UF_CRM_1703446692577'=>
         $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['OASIS_ID'], 'CHECK_PERMISSIONS'=>'N'], $arSelect = ['ID']);
            while($cont = $res->fetch()):
        $contId = IntVal($cont['ID']);
        endwhile;
            if($cont['ID'] != '' || !empty($cont['ID'])):
        if($contactID = $contact->Update($contId, $arNewContact)):
            file_put_contents(getcwd() . '/log/successUpdateIblockElement_'.$date.'.log', 'success updates element участники контакт '.$contactID, FILE_APPEND);
        else:
            file_put_contents(getcwd() . '/log/failedUpdateIblockElement_'.$date.'.log', 'failed update element участники контакт '.$contId, FILE_APPEND);
        endif;
        $contactID = $contId;
            else:
        if($contactID = $contact->Add($arNewContact, true,  $arOptions = ['CURRENT_USER' => '3037'])):
            file_put_contents(getcwd() . '/log/successAddedIblockElement_'.$date.'.log', 'success added element участники контакт '.$contactID, FILE_APPEND);
            else:
                file_put_contents(getcwd() . '/log/failedAddedIblockElement_'.$date.'.log', 'failed added element участники контакт '.$contact->LAST_ERROR, FILE_APPEND);
            endif;
        endif;
        endif;
//ищем имеющиеся цели
        $arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_1881");
        $arFilter = Array("IBLOCK_ID"=>IntVal(197), "NAME"=>$name, "PROPERTY_1881_VALUE"=>$element_id);
        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>9999), $arSelect);
        while($ob = $res->GetNextElement()) 
        { 
         $arFields = $ob->GetFields();  
        }
        if($arFields['ID'] != '' || !empty($arFields['ID'])):
        $USER_TABLE_ID = $el->Update($arFields['ID'], $arLoadProductArray);
        file_put_contents(getcwd() . '/log/successAddedIblockElement_'.$date.'.log', 'success updated element участники '.$arFields['ID'], FILE_APPEND);
        //если нет то добавляем
        else: 
            if($USER_TABLE_ID = $el->Add($arLoadProductArray)):
            file_put_contents(getcwd() . '/log/successAddedIblockElement_'.$date.'.log', 'success added element участники '.$USER_TABLE_ID, FILE_APPEND);
        else:
            file_put_contents(getcwd() . '/log/failedAddedIblockElement_'.$date.'.log', 'failed added element участники '.$el->LAST_ERROR, FILE_APPEND);
        endif;
        endif;
        
        if($arr['OAZIS']['USER_TABLE'][$n]['COMPANY'] || $arr['OAZIS']['USER_TABLE'][$n]['COMPANY'] != ''):
//тут нужен if company есть
$arNewCompany=array(
    //"UF_CRM_1703446692577" => $arr['OAZIS']['USER_TABLE'][$n]['COMPANY']['OASIS_ID'],
    //"UF_CRM_1703516205221" => $arr['OAZIS']['USER_TABLE'][$n]['COMPANY']['NSI_ID'],
    "TITLE" => $arr['OAZIS']['USER_TABLE'][$n]['COMPANY']['NAME'],
    //"LAST_NAME" => $arr['OAZIS']['USER_TABLE'][$n]['COPMANY']['LAST_NAME'],
    //"SECOND_NAME" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['SECOND_NAME'],
    //"DISPLAY_NAME" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['NAME'],
    //"GENDER" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['GENDER'],
   // "EMAIL" => $arr['OAZIS']['USER_TABLE'][$n]['COMPANY']['EMAIL'],
    //"PHONE" => $arr['OAZIS']['USER_TABLE'][$n]['COMPANY']['PHONE']
    //"TAGS" => $arr['OAZIS']['USER_TABLE'][$n]['PERSON']['TAGS'] тут какие о теги
        //"company-id"
        //"ASSIGNED_BY_ID"
        //"POST"
    );
        $company = new CCrmCompany();
        //ищем компанию среди имеющихся, если нет то создаем новую
        $res = CCrmCompany::GetList($arOrder = ['ID'=>'DESC'], $arFilter = ['UF_CRM_1698843074694'=>
         $arr['OAZIS']['USER_TABLE'][$n]['COMPANY']['OASIS_ID'], 'CHECK_PERMISSIONS'=>'N'], $arSelect = ['ID']);
            while($comp = $res->fetch()):
        $compId = IntVal($comp['ID']);
        endwhile;
            if($comp['ID'] != '' || !empty($comp['ID'])):
        if($companyID = $contact->Update($compId, $arNewCompany)):
            file_put_contents(getcwd() . '/log/successUpdateIblockElement_'.$date.'.log', 'success updates element участники компания '.$companyID, FILE_APPEND);
        else:
            file_put_contents(getcwd() . '/log/failedUpdateIblockElement_'.$date.'.log', 'failed update element участники компания '.$compId, FILE_APPEND);
        endif;
        $companyID = $compId;
            else:
        if($companyID = $company->Add($arNewCompany, true,  $arOptions = ['CURRENT_USER' => '3037'])):
            file_put_contents(getcwd() . '/log/successAddedIblockElement_'.$date.'.log', 'success added element участники компания '.$companyID, FILE_APPEND);
            else:
                file_put_contents(getcwd() . '/log/failedAddedIblockElement_'.$date.'.log', 'failed added element участники компания '.$company->LAST_ERROR, FILE_APPEND);
            endif;
        endif;
    endif;

        //собираем участника
        $name = "Участник № ".$n." по мероприятию ID-".$arr['OAZIS']['OASIS_ID']." на ".$arr['OAZIS']['DATE'];
	$prop['1888'] = $contactID;
    $prop['1887'] = $companyID;
	//$prop['1883'] = $arr['OAZIS']['PURPOSE'][$n]['RESULT'];
	//$prop['1884'] = $arr['OAZIS']['PURPOSE'][$n]['FAILS'];
	//$prop['1885'] = $arr['OAZIS']['PURPOSE'][$n]['LEARNED'];
	$prop['1881'] = $element_id;
$n++;

        Loader::includeModule('iblock'); 
        //добавляем эл инфоблока
        //$new_iblock_el_name = 'Hello again!';
        $el = new CIBlockElement;
        //$PROP = array();
        //$PROP[1881] = $element_id;  // свойству с кодом 12 присваиваем значение "Белый"
        //$PROP[3] = 38;        // свойству с кодом 3 присваиваем значение 38
        $arLoadProductArray = Array(
            "MODIFIED_BY"    =>  3037, // элемент изменен текущим пользователем
            //"IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
            "IBLOCK_ID"      => 197,
            "PROPERTY_VALUES"=> $prop,
            "NAME"           => $name,
            "ACTIVE"         => "Y",            // активен
            "PREVIEW_TEXT"   => $name,
            //"DETAIL_TEXT"    => "текст для детального просмотра",
            //"DETAIL_PICTURE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/image.gif")
        );
//ищем имеющиеся цели
        $arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_1881");
        $arFilter = Array("IBLOCK_ID"=>IntVal(197), "NAME"=>$name, "PROPERTY_1881_VALUE"=>$element_id);
        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>9999), $arSelect);
        while($ob = $res->GetNextElement()) 
        { 
         $arFields = $ob->GetFields();  
        }
        if($arFields['ID'] != '' || !empty($arFields['ID'])):
        $USER_TABLE_ID = $el->Update($arFields['ID'], $arLoadProductArray);
        file_put_contents(getcwd() . '/log/successAddedIblockElement_'.$date.'.log', 'success updated element участники '.$arFields['ID'], FILE_APPEND);
        //если нет то добавляем
        else: 
            if($USER_TABLE_ID = $el->Add($arLoadProductArray)):
            file_put_contents(getcwd() . '/log/successAddedIblockElement_'.$date.'.log', 'success added element участники '.$USER_TABLE_ID, FILE_APPEND);
        else:
            file_put_contents(getcwd() . '/log/failedAddedIblockElement_'.$date.'.log', 'failed added element участники '.$el->LAST_ERROR, FILE_APPEND);
        endif;
        endif;

endwhile;
//если не пусто роль - ТУТ ОШИБКА
	/* if (!empty($arr['OAZIS']['USER_TABLE'][$n]['ROLE']['OASIS_ID']) || $arr['OAZIS']['USER_TABLE'][$n]['OASIS_ID'] != ''):
$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_1849");
$arFilter = Array("IBLOCK_ID"=>IntVal(193), "PROPERTY_1849_VALUE"=>IntVal($arr['OAZIS']['COUNTRY'][$n]['OASIS_ID']));
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>9999), $arSelect);
while($ob = $res->GetNextElement()) 
{ 
 $arFields = $ob->GetFields();  
}
 $el = new CIBlockElement;
$el->Update($PRODUCT_ID, array('1877' => $arFields['ID']);
endif; */

endif;
    /*
    //$new_item->set('COMPANY_ID', $arComp['ID']); //Привязка к компании
    function createIblockElement {
    Loader::includeModule('iblock');
    //добавляем эл инфоблока
    $new_iblock_el_name = 'Hello again!';
    $el = new CIBlockElement;
    $PROP = array();
    //$PROP[1881] = $element_id;  // свойству с кодом 12 присваиваем значение "Белый"
    //$PROP[3] = 38;        // свойству с кодом 3 присваиваем значение 38
    $arLoadProductArray = Array(
        //"MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
        //"IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
        "IBLOCK_ID"      => 190,
        "PROPERTY_VALUES"=> $PROP,
        "NAME"           => '213',
        "ACTIVE"         => "Y",            // активен
        "PREVIEW_TEXT"   => $new_iblock_el_name,
        //"DETAIL_TEXT"    => "текст для детального просмотра",
        //"DETAIL_PICTURE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/image.gif")
    );
    if($PRODUCT_ID = $el->Add($arLoadProductArray))
        echo "New ID of iblock element: ".$PRODUCT_ID;
    else
        echo "Error: ".$el->LAST_ERROR;
    
        file_put_contents(getcwd() . '/log/succesAddIblockElement_'.$date.'.log', 'success added element'.$element_id, FILE_APPEND);
}
        */
       
    }
    else {
        file_put_contents(getcwd() . '/log/failedAddElement_'.$date.'.log', 'failed added element'.$element_id, FILE_APPEND);
        }
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"); 
?>
