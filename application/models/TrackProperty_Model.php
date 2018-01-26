<?php

defined('__ROOT__') OR define('__ROOT__', dirname(dirname(__FILE__))); 
require_once(__ROOT__.'/models/BaseTable_model.php'); 
require_once(__ROOT__.'/models/Property_JsonModel.php'); 
require_once(__ROOT__.'/models/Gallery_model.php'); 
require_once(__ROOT__.'/models/PropertyFeature_model.php'); 
require_once(__ROOT__.'/models/PropertySpec_model.php'); 
require_once(__ROOT__.'/models/DefinedType_model.php'); 

class TrackProperty_model extends BaseTable_model {
    
    /**
     * 
     */
    function __construct()
    {
        $tablename = "trackproperty";
        parent::__construct($tablename);
    }

    /**
     * 
     */
    function insert($operation, $propData, $userid, $property_id)
    {
        $data = array(
            'PropertyId'    => $property_id,
            'Operation'     => $operation,
            'DetailJson'    => json_encode($propData),
            'CreatedOn'     => date('Y-m-d H:i:s'),
            'CreatedBy'     => $userid,
        );
        $ok = $this->db->insert($this->tableName, $data);
        
        return $ok;
    }
}