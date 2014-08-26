<?php

abstract class Model_Abstract extends Zend_Db_Table {

    protected $_users_index = 'users_index';
    protected $_users_profile = 'users_profile_';
    protected $_users_base = 'users_base_';
    protected $_users_base_expand = 'users_base_expand_';
    protected $_users_info_first = 'users_info_first_';

    /* PS: 吞食萌将游戏常量ID: 124  */
//	protected define('game_id_static',124);
    protected $_game_id_static = 124;

    // Containing this function
    public function Model_Abstract($config = null) {
        if (isset($this->_use_adapter)) {
            $config = Zend_Registry::get($this->_use_adapter);
        }
        return parent::__construct($config);
    }

}
