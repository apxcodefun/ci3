<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function index()
    {   
        $this->form_validation->set_rules('email', 'Email','required|trim|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if($this->form_validation->run ()== false) {
            $data['title'] = 'Login';
            $this->load->view('templates/auth_header',$data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');

        } else {
            // validation successful
            $this->_login();
        }
    }

    private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();
        
        if ($user) { 
            if($user['is_active'] == 1) {
                if (password_verify($password, $user['password'])) {
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    if ($user['role_id'] == 1) {
                        redirect('admin');
                    } else {
                        redirect('user');
                    }
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Password Salah!</div>');
                    redirect('auth');
                }

            }else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Your account is not active! Please Verification Your Email!
                </div>');
                redirect('auth');
            }

        }else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email atau Password Salah!</div>');
            redirect('auth');
        }
    }

    public function register () 
    {
        $this->form_validation->set_rules('name','Name','required|trim');
        $this->form_validation->set_rules('email','Email','required|trim|valid_email|is_unique[user.email]',[
            'is_unique' => 'This email has already registered!'
        ]);
        $this->form_validation->set_rules('password1','Password','required|trim|min_length[3]|matches[password2]',[
            'matches' => 'Password Not matches!',
            'min_length' => 'Minimal Length 3'
        ]);
        $this->form_validation->set_rules('password2','Password','required|trim|matches[password1]');


        if($this->form_validation->run()== false) {
            $data['title'] = 'Register';
            $this->load->view('templates/auth_header',$data);
            $this->load->view('auth/register');
            $this->load->view('templates/auth_footer');
        }else {
            $data = [
                'name' => htmlspecialchars($this->input->post('name',true)),
                'email' => htmlspecialchars($this->input->post('email',true)),
                'password' => password_hash($this->input->post('password1'),PASSWORD_DEFAULT),
                'image' => 'default.jpg',
                'role_id' => 2,
                'is_active' => 0,
                'date_created' => time()
            ];

            $token = base64_encode(random_bytes(32));
            $user_token = [
                'email' => $this->input->post('email'),
                'token' => $token,
                'date_created' => time()
            ];

            $this->db->insert('user',$data);
            $this->db->insert('user_token',$user_token);

            $this->_sendEmail($token,'verify');

            $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">
            Congratulation! Your account has been created. Please Verified Your Account
            </div>');
            redirect('auth');
        }        

    }

    private function _sendEmail($token, $type)
    {
        $config = [
            'protocol' =>'smtp',
         'smtp_host' =>'ssl://smtp.gmail.com',
         'smtp_user' => 'akunspecial22222@gmail.com',
          'smtp_pass' => 'xkli llhp apbk zyay',
          'smtp_port' => 465,
          'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        ];

        $this->load->library('email', $config);
        $this->email->initialize($config);

        $this->email->from('akunspecial22222@gmail.com','Akun Special');
        $this->email->to($this->input->post('email'));
        if($type == 'verify') {
            $this->email->subject('Account Verification');
            $this->email->message('Click this link to verify your account: <a href="'.base_url().'auth/verification?email='.$this->input->post('email').'&token='.urlencode($token).'">Activate</a>');

        }else if($type == 'forgot') {
            $this->email->subject('Reset Password');
            $this->email->message('Click this link to Reset your account: <a href="'.base_url().'auth/resetpassword?email='.$this->input->post('email').'&token='.urlencode($token).'">Reset Password</a>');
        }
        
        if($this->email->send()) {
            return true;
        }else {
            echo $this->email->print_debugger();
            die;
        }

    }

    public function verification()
    {
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();
        if($user) {
            $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();

            if($user_token){
                if(time() - $user_token['date_created'] < (60*60*24)) {
                    $this->db->set('is_active', 1);
                    $this->db->where('email', $email);
                    $this->db->update('user');
                    $this->db->delete('user_token', ['email' => $email]);
                    $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">'.$email.'
                    Has been Activited. Please login
                    </div>');
                    redirect('auth');
                    

            }else {

                $this->db->delete('user', ['email' => $email]);
                $this->db->delete('user_token', ['email' => $email]);
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Your account has expired! Please create a new account!
                </div>');
                redirect('auth');
            }

        }else {
            show_404();
        }
        }
    }

    public function logout() {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');
        $this->session->set_flashdata('message','<div class="alert alert-success" role="alert">
        You have been logged out!
        </div>');
        redirect('auth');
    }


    public function blocked()
    {
        $this->load->view('auth/blocked');
    }


    public function forgotpassword() {


        $this->form_validation->set_rules('email','Email','trim|required|valid_email');
        if($this->form_validation->run() == FALSE) {
            
            $data['title'] = 'Forgot Password';
            $this->load->view('templates/auth_header',$data);
            $this->load->view('auth/forgotpassword');
            $this->load->view('templates/auth_footer');
        }else {
            $email = $this->input->post('email');
            $user = $this->db->get_where('user', ['email' => $email, 'is_active'=> 1])->row_array();

            if($user){
                $token = base64_encode(random_bytes(32));
                $user_token = [
                    'email' => $email,
                    'token' => $token,
                    'date_created' => time()
                ];
                $this->db->insert('user_token', $user_token);

                $this->_sendEmail($token, 'forgot');
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
                Reset Password Link has been sent to your email! Please check your email!
                </div>');
                redirect('auth/forgotpassword');

            }else{
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Your account does not exist or activated!
                </div>');
                redirect('auth/forgotpassword');
            }

        }
    }

    public function resetpassword() {
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user',['email' => $email,])->row_array();
        if($user) {
            $user_token = $this->db->get_where('user_token',['token' => $token])->row_array();
            if($user_token){
            
                $this->session->set_userdata('reset_email',$email);
                $this->changepassword();

            }else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Reset Password Link has been expired! Please create a new account!
                </div>');
                redirect('auth/forgotpassword');
            }
        }else {
            show_404();
        }
    }

    public function changepassword() 
    {

        if(!$this->session->userdata('reset_email')){
            redirect('auth');
        }

        $this->form_validation->set_rules('password1','Password','required|trim|min_length[3]|matches[password2]',[
          'matches' => 'Password Not matches!',
          'min_length' => 'Minimal Length 3'
        ]);
        $this->form_validation->set_rules('password2','Password','required|trim|matches[password1]');
        if($this->form_validation->run() == false) {
        $data['title'] = 'Change Password';
        $this->load->view('templates/auth_header',$data);
        $this->load->view('auth/changepassword');
        $this->load->view('templates/auth_footer');
        
        }else{
            $email = $this->session->userdata('reset_email');
            $password = password_hash($this->input->post('password1'),PASSWORD_DEFAULT);
            $this->db->set('password', $password);
            $this->db->where('email', $email);
            $this->db->update('user');

            $this->session->unset_userdata('reset_email');
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Password has been changed! Please login
            </div>');
            redirect('auth');
        }
    }
}
