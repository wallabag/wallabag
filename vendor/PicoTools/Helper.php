<?php

namespace Helper;

function get_current_base_url()
{
    $url = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    $url .= $_SERVER['HTTP_HOST'];
    $url .= $_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443 ? '' : ':'.$_SERVER['SERVER_PORT'];
    $url .= dirname($_SERVER['PHP_SELF']) !== '/' ? dirname($_SERVER['PHP_SELF']).'/' : '/';

    return $url;
}

function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
}

function flash($html)
{
    $data = '';

    if (isset($_SESSION['flash_message'])) {
        $data = sprintf($html, escape($_SESSION['flash_message']));
        unset($_SESSION['flash_message']);
    }

    return $data;
}

function flash_error($html)
{
    $data = '';

    if (isset($_SESSION['flash_error_message'])) {
        $data = sprintf($html, escape($_SESSION['flash_error_message']));
        unset($_SESSION['flash_error_message']);
    }

    return $data;
}

function format_bytes($size, $precision = 2)
{
    $base = log($size) / log(1024);
    $suffixes = array('', 'k', 'M', 'G', 'T');

    return round(pow(1024, $base - floor($base)), $precision).$suffixes[floor($base)];
}

function get_host_from_url($url)
{
    return escape(parse_url($url, PHP_URL_HOST));
}

function summary($value, $min_length = 5, $max_length = 120, $end = '[...]')
{
    $length = strlen($value);

    if ($length > $max_length) {
        return substr($value, 0, strpos($value, ' ', $max_length)).' '.$end;
    }
    else if ($length < $min_length) {
        return '';
    }

    return $value;
}

function in_list($id, array $listing)
{
    if (isset($listing[$id])) {
        return escape($listing[$id]);
    }

    return '?';
}

function error_class(array $errors, $name)
{
    return ! isset($errors[$name]) ? '' : ' form-error';
}

function error_list(array $errors, $name)
{
    $html = '';

    if (isset($errors[$name])) {

        $html .= '<ul class="form-errors">';

        foreach ($errors[$name] as $error) {
            $html .= '<li>'.escape($error).'</li>';
        }

        $html .= '</ul>';
    }

    return $html;
}

function form_value($values, $name)
{
    if (isset($values->$name)) {
        return 'value="'.escape($values->$name).'"';
    }

    return isset($values[$name]) ? 'value="'.escape($values[$name]).'"' : '';
}

function form_hidden($name, $values = array())
{
    return '<input type="hidden" name="'.$name.'" id="form-'.$name.'" '.form_value($values, $name).'/>';
}

function form_default_select($name, array $options, $values = array(), array $errors = array(), $class = '')
{
    $options = array('' => '?') + $options;
    return form_select($name, $options, $values, $errors, $class);
}

function form_select($name, array $options, $values = array(), array $errors = array(), $class = '')
{
    $html = '<select name="'.$name.'" id="form-'.$name.'" class="'.$class.'">';

    foreach ($options as $id => $value) {

        $html .= '<option value="'.escape($id).'"';

        if (isset($values->$name) && $id == $values->$name) $html .= ' selected="selected"';
        if (isset($values[$name]) && $id == $values[$name]) $html .= ' selected="selected"';

        $html .= '>'.escape($value).'</option>';
    }

    $html .= '</select>';
    $html .= error_list($errors, $name);

    return $html;
}

function form_radios($name, array $options, array $values = array(), $required = false, array $errors = array())
{
    $html = '';

    foreach ($options as $value => $label) {
        $html .= form_radio($name, $label, $value, isset($values[$name]) && $values[$name] == $value, $required, '', $errors);
    }

    $html .= error_list($errors, $name);

    return $html;
}

function form_radio($name, $label, $value, $selected = false, $required, $class = '', $errors)
{
    $class .= error_class($errors, $name);

    return '<label><input type="radio" name="'.$name.'" class="'.$class.'" value="'.escape($value).'" '.($selected ? 'selected="selected"' : '').' '.($required ? 'required="required"' : '').'>'.escape($label).'</label>';
}

function form_checkbox($name, $label, $value, $checked = false, $class = '')
{
    return '<label><input type="checkbox" name="'.$name.'" class="'.$class.'" value="'.escape($value).'" '.($checked ? 'checked="checked"' : '').'>&nbsp;'.escape($label).'</label>';
}

function form_label($label, $name, $class = '')
{
    return '<label for="form-'.$name.'" class="'.$class.'">'.escape($label).'</label>';
}

function form_textarea($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    $class .= error_class($errors, $name);

    $html = '<textarea name="'.$name.'" id="form-'.$name.'" class="'.$class.'" ';
    $html .= implode(' ', $attributes).'>';
    $html .= isset($values->$name) ? escape($values->$name) : isset($values[$name]) ? $values[$name] : '';
    $html .= '</textarea>';
    $html .= error_list($errors, $name);

    return $html;
}

function form_input($type, $name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    $class .= error_class($errors, $name);

    $html = '<input type="'.$type.'" name="'.$name.'" id="form-'.$name.'" '.form_value($values, $name).' class="'.$class.'" ';
    $html .= implode(' ', $attributes).'/>';
    $html .= error_list($errors, $name);

    return $html;
}

function form_text($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    return form_input('text', $name, $values, $errors, $attributes, $class);
}

function form_password($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    return form_input('password', $name, $values, $errors, $attributes, $class);
}

function form_email($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    return form_input('email', $name, $values, $errors, $attributes, $class);
}

function form_date($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    return form_input('date', $name, $values, $errors, $attributes, $class);
}
