<?php

namespace PicoFarad\Request;


function param($name, $default_value = null)
{
    return isset($_GET[$name]) ? $_GET[$name] : $default_value;
}


function int_param($name, $default_value = 0)
{
    return isset($_GET[$name]) && ctype_digit($_GET[$name]) ? (int) $_GET[$name] : $default_value;
}


function value($name)
{
    $values = values();
    return isset($values[$name]) ? $values[$name] : null;
}


function values()
{
    if (! empty($_POST)) {

        return $_POST;
    }

    $result = json_decode(body(), true);

    if ($result) {
        return $result;
    }

    return array();
}


function body()
{
    return file_get_contents('php://input');
}


function file_content($field)
{
    if (isset($_FILES[$field])) {
        return file_get_contents($_FILES[$field]['tmp_name']);
    }

    return '';
}


function file_info($field)
{
    if (isset($_FILES[$field])) {
        return array(
            'name' => $_FILES[$field]['name'],
            'mimetype' => $_FILES[$field]['type'],
            'size' => $_FILES[$field]['size'],
        );
    }

    return false;
}


function file_move($field, $destination)
{
    if (isset($_FILES[$field]) && ! file_exists($destination)) {
        @mkdir(dirname($destination), 0777, true);
        move_uploaded_file($_FILES[$field]['tmp_name'], $destination);
    }
}