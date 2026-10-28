<?php
if (!defined('BAVARIA_DASHBOARD_ALLOWED')) { http_response_code(403); exit; }
/** Read field metadata only; CRM values still come from permission-checked deal queries. */
function dashboardDealFields(): array {
    global $USER_FIELD_MANAGER;
    $labels = ['model'=>'модель', 'reason'=>'причина обращения', 'cancelReason'=>'запись не состоялась по причине', 'deferred'=>'статус сделки'];
    $fields = is_object($USER_FIELD_MANAGER) ? $USER_FIELD_MANAGER->GetUserFields('CRM_DEAL', 0, 'ru') : [];
    $resolved = [];
    foreach ($labels as $key=>$label) {
        $matches=[];
        foreach ($fields as $field) {
            $name=$field['EDIT_FORM_LABEL']??'';
            if (is_array($name)) { $name=$name['ru']??''; }
            if (dashboardNormalizeLabel((string)$name)===$label) { $matches[]=$field; }
        }
        if (count($matches)!==1) { $resolved[$key]=['error'=>'Поле «'.$label.'»: найдено '.count($matches).'.']; continue; }
        $field=$matches[0];
        $item=['code'=>(string)$field['FIELD_NAME'], 'type'=>(string)$field['USER_TYPE_ID'], 'enum'=>[]];
        if ($item['type']==='enumeration') {
            $rows=CUserFieldEnum::GetList(['SORT'=>'ASC'],['USER_FIELD_ID'=>(int)$field['ID']]);
            while ($row=$rows->Fetch()) { $item['enum'][(string)$row['ID']]=(string)$row['VALUE']; }
        }
        if ($key==='deferred') {
            $item['deferredIds']=[];
            foreach ($item['enum'] as $id=>$name) { if (dashboardNormalizeLabel($name)==='отложенные') { $item['deferredIds'][]=(string)$id; } }
            if (!$item['deferredIds']) { $item['error']='В поле «Статус сделки» не найден вариант «Отложенные».'; }
        } elseif (!in_array($item['type'],['enumeration','string','integer','double','boolean','date','datetime'],true)) {
            $item['error']='Тип поля «'.$label.'» требует настройки отображения.';
        }
        $resolved[$key]=$item;
    }
    return $resolved;
}
function dashboardDealFieldValue(array $row, array $field): string {
    if (isset($field['error'])) { return '—'; }
    $values=$row[$field['code']]??[];
    if (!is_array($values)) { $values=[$values]; }
    $labels=[];
    foreach ($values as $value) {
        if (!is_scalar($value) || (string)$value==='') { continue; }
        $labels[]=$field['type']==='enumeration'?($field['enum'][(string)$value]??'Неизвестное значение'):(string)$value;
    }
    return $labels?implode(', ',$labels):'—';
}
function dashboardDealIsDeferred(array $row, array $field): ?bool {
    if (isset($field['error'])) { return null; }
    $values=$row[$field['code']]??[];
    if (!is_array($values)) { $values=[$values]; }
    foreach ($values as $value) { if (is_scalar($value)&&in_array((string)$value,$field['deferredIds'],true)) { return true; } }
    return false;
}
