<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        is_login();
    }

    public function index() {

        $data['title']= 'Menu Managements';
        $data['user'] = $this->db->get_where('user',['email' => $this->session->userdata('email')]) ->row_array();


        $data['menu']= $this->db->get('user_menu')->result_array();

        $this->form_validation->set_rules('menu','Menu','required');

        if($this->form_validation->run()==false){
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('templates/footer');
        }else {
            $this->db->insert('user_menu', ['menu' => $this->input->post('menu')]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">New Menu Added!</div>');
            redirect('menu');
        }
    }

    public function submenu() 
    {
        $data['title']= ' Sub Menu Managements';
        $data['user'] = $this->db->get_where('user',['email' => $this->session->userdata('email')]) ->row_array();
        $this->load->model('menu_model','menu');

        $data['subMenu']= $this->menu->getSubMenu();
        $data['menu'] = $this->db->get('user_menu')->result_array();

        $this->form_validation->set_rules('title','Title','required');
        $this->form_validation->set_rules('menu_id','Menu','required');
        $this->form_validation->set_rules('url','Url','required');
        $this->form_validation->set_rules('icon','Icon','required');

        if($this->form_validation->run() == FALSE){
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('menu/submenu', $data);
            $this->load->view('templates/footer');
        }else{
            $data = [
                'title' => $this->input->post('title'),
              'menu_id' => $this->input->post('menu_id'),
                'url' => $this->input->post('url'),
                'icon' => $this->input->post('icon'),
                'is_active' => $this->input->post('is_active')
            ];
            $this->db->insert('user_sub', $data);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">New Sub Menu Added!</div>');
            redirect('menu/submenu');
        }

        
    }

    public function edit($id) 
    {

        $data['title']= 'Menu Managements';
        $data['user'] = $this->db->get_where('user',['email' => $this->session->userdata('email')]) ->row_array();
        $this->load->model('menu_model','menu');


        $data['menu']= $this->db->get('user_menu')->result_array();

        $this->form_validation->set_rules('menu','Menu','required');

        if($this->form_validation->run()==false){
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('templates/footer');
        }else {
            $this->menu->editMenu();
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Menu Updated!</div>');

            redirect('menu');
            
        }


    }

    public function hapus($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('user_menu');
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Menu Deleted!</div>');
        redirect('menu');
    }
    public function subhapus($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('user_sub');
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Menu Deleted!</div>');
        redirect('menu/submenu');
    }

    public function subedit($id) {
        $data['title']= 'Edit Sub Menu';
        $data['user'] = $this->db->get_where('user',['email' => $this->session->userdata('email')]) ->row_array();
        $this->load->model('menu_model','menu');
    }

}