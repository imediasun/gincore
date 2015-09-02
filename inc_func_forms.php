<?php


/**
 * Добавить стили на формы
 * на лоадер
 *
 *
 *
 *
 * @param type $content
 * @return type
 */
function content_form($content, $values = array())
{
    if (strpos($content, '{-form_') !== false) {
        preg_match_all('/{-form_([0-9]+)-}/', $content, $forms);
        foreach ($forms[1] as $form_id) {
            $val = isset($values[$form_id]) ? $values[$form_id] : '';
            
            //return array('html' => count($val), 'state' => true);
            
            $content = str_replace('{-form_' . $form_id . '-}', gen_data_form($form_id, $val), $content);
        }
    }
    return $content;
}

function gen_data_form($id, $values, $return_parts = false, $use_placeholders = false, $msg_tag = 'span')
{
    global $db, $prefix, $lang, $def_lang, $template_vars;

    $form_settings = $db->query("SELECT active FROM {forms} WHERE id = ?i", array($id), 'row');
    $form_active = $form_settings['active'];
    $form = '';
    if ($form_active) {
        $langss = $db->query("SELECT * FROM {forms_strings} 
                                 WHERE forms_id = ?i", array($id), 'assoc:lang');
        $form_data = translates_for_page($lang, $def_lang, $langss, array(), true);
        $form_parsts = array();
        $form_parts['form_title'] = $form_data['name'];
        $form_parts['form']['header'] = '<form class="data_form data_form-' . $id . '" method="post" action="' . $prefix . 'ajax_forms.php?form_id=' . $id . '" data-id="' . $id . '">';
        $form_parts['form']['footer'] = '</form>';
        $form_parts['fields'] = '';
        $fields_arr = $db->query("SELECT * FROM {forms_fields} WHERE form_id = ?i AND active = 1 ORDER BY prio", array($id), 'assoc:id');
        $translates = get_few_translates(
            'forms_fields', 
            'field_id', 
            $db->makeQuery("field_id IN (?q)", array(implode(',', array_keys($fields_arr))))
        );
        foreach ($fields_arr as $field) {
            $field = translates_for_page($lang, $def_lang, $translates[$field['id']], $field, true);
            $required = $field['required'] ? '<span class="required_form_field">*</span>' : '';
            $form_parts['fields'] .= '<div class="form-group">';
            if ($field['active'] == 1) {
                if ($field['type'] != 'checkbox' && !$use_placeholders) {
                    $form_parts['fields'] .= '<span>' . $required . $field['name'] . '</span>';
                }
                $placeholder = '';
                if($use_placeholders){
//                    $placeholder = ' placeholder="'.($field['required'] ? '*' : '').' '.$field['name'].'"';
                }
                switch ($field['type']) {
                    case 'text':
                        $form_parts['fields'] .= '<input'.$placeholder.' class="form-control" type="text" name="fields[' . $field['id'] . ']">';
                        break;

                    case 'phone':
                        $value = isset($values['phone']) ? $values['phone'] : '';
                        $form_parts['fields'] .= '<input'.$placeholder.' value="' . $value . '" class="input-phone form-control" type="tel" name="fields[' . $field['id'] . ']">';
                        break;

                    case 'textarea':
                        $form_parts['fields'] .= '<textarea'.$placeholder.' class="form-control" rows="4" name="fields[' . $field['id'] . ']"></textarea>';
                        break;

                    case 'checkbox':
                        $form_parts['fields'] .= '<label>' . $required . $field['name'] . ' <input type="checkbox" name="fields[' . $field['id'] . ']" value="1"></label>';
                        break;

                    default:
                        $form_parts['fields'] .= '';
                }
            } else {
                $value = isset($values['hidden']) ? $values['hidden'] : '';
                $form_parts['fields'] .= '<input value="' . $value . '" type="hidden" name="hiddens[' . $field['id'] . ']"><br>';
            }
            $form_parts['fields'] .= '</div>';
        }
        $form_parts['submit'] = '<input type="submit" class="btn black_btn" value="'.$template_vars['l_module_forms_submit'].'"> ';
        $form_parts['form_message'] = '<'.$msg_tag.' class="data_form_message"></'.$msg_tag.'>';
        $form_parts['form_final_message'] = '<div class="data_form_final_message"></div>';
        
        if(!$return_parts){
            $form = 
                $form_parts['form']['header'].
                    '<div class="data_fields">'.
                        $form_parts['fields'].
                        '<div class="form_el_submit">'.
                            $form_parts['submit'].
                            $form_parts['form_message'].
                        '</div>' .
                    '</div>' .
                    $form_parts['form_final_message'].
                $form_parts['form']['footer'];
        }else{
            $form = $form_parts;
        }
    }

    return $form;
}
