<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu_model extends CI_Model {

    public function getSubMenu() {
        $query = "SELECT `user_sub`.*,`user_menu`.`menu` FROM `user_sub` JOIN `user_menu` ON `user_sub`.`menu_id` = `user_menu`.`id`";

        return $this->db->query($query)->result_array();
    }



    public function editMenu(){
        $data = [
          'menu' => $this->input->post('menu'),
        ];

        $this->db->where('id', $this->input->post('id'));
        $this->db->update('user_menu', $data);
        
    }
}