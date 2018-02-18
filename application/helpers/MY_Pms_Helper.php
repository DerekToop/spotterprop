<?php defined('BASEPATH') OR exit('No direct script access allowed');

function send_pms($userid, $propId, $title, $message)
{
    $CI =& get_instance();
    $CI->load->model("Property_model");
    $propModel = new Property_model();

    $property_json = $propModel->get_json($propId);
    $propertyUserId = $property_json->data->person_id;

    echo "userid = $userid";
    echo "person_id = $propertyUserId";

    $CI->load->library('session');
    $CI->load->library('aauth',  array(),  'auth');
    $CI->auth->send_pm($userid, $propertyUserId, $title, $message);
}

function list_pms($receiver_id)
{
    $CI =& get_instance();
    
    $CI->load->library('session');
    $CI->load->library('aauth',  array(),  'auth');
    $list = $CI->auth->list_pms(20, 0, $receiver_id);
    return $list;
}

function list_unread_pms($receiver_id)
{
    $CI =& get_instance();
    
    $CI->load->model('Pms_model');
    $model = new Pms_model();
    $result = $model->list_unread($receiver_id);

    return $result;
}

function set_as_read_pm($pm_id)
{
    $CI =& get_instance();
    
    $CI->load->model('Pms_model');
    $model = new Pms_model();
    $result = $model->set_as_read($pm_id);

    return $result;
}