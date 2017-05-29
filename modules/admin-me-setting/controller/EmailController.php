<?php
/**
 * Current user setting
 * @package admin-me-setting
 * @version 0.0.1
 * @upgrade true
 */

namespace AdminMeSetting\Controller;
use User\Model\User;
use UserEmail\Model\UserEmail as UEmail;

class EmailController extends AdminMeController
{
    private function _defaultParams(){
        return [
            'title'             => 'My Email',
            'nav_title'         => 'My Setting',
            'active_menu'       => 'my-setting',
            'active_submenu'    => 'my-setting'
        ];
    }
    
    private function next($next='adminMeEmail'){
        $ref = $this->req->getQuery('ref');
        if(!$ref)
            $ref = $this->router->to($next);
        $this->redirect($ref);
    }
    
    public function indexAction(){
        if(!$this->user->login)
            return $this->loginFirst('adminLogin');
        
        if(!module_exists('user-email'))
            return $this->show404();
        
        $params = $this->_defaultParams();
        
        $params['emails']  = UEmail::get(['user'=>$this->user->id], true, false, 'address');
        $params['title']   = 'My Email';
        $params['success'] = false;
        
        if(false === ($form = $this->form->validate('admin-me-email')))
            return $this->respond('me/setting/email', $params);
        
        $email = [
            'user'    => $this->user->id,
            'address' => strtolower($form->email),
            'status'  => 1
        ];
        
        // we can't verify if mailer module not there
        // so, we'll just verify the email
        if(!module_exists('mailer'))
            $email['status'] = 2;
        
        $email['id'] = UEmail::create($email);
        $email['created'] = $email['updated'] = date('Y-m-d H:i:s');
        $params['emails'][] = (object)$email;
        
        $params['success'] = true;
        
        $this->respond('me/setting/email', $params);
    }
    
    public function primaryAction(){
        if(!$this->user->login)
            return $this->show404();
        
        if(!module_exists('user-email'))
            return $this->show404();
        
        $id = $this->param->id;
        $email = UEmail::get($id, false);
        // we can set the email default if
        // - the email is exists
        // - the email is mine
        // - the email is verified
        if($email && $email->user == $this->user->id && $email->status == 2){
            // set other email to verified
            UEmail::set(['status'=>2], ['user'=>$this->user->id, 'status'=>3]);
            UEmail::set(['status'=>3], $email->id);
        }
        
        $this->next();
    }
    
    public function removeAction(){
        if(!$this->user->login)
            return $this->show404();
        
        if(!module_exists('user-email'))
            return $this->show404();
        
        $id = $this->param->id;
        $email = UEmail::get($id, false);
        // we can remove email if
        // - the email is exists 
        // - the email is mine
        // - the email is not default 
        if( $email && $email->user == $this->user->id && $email->status != 3 ){
            UEmail::remove($id);
        }
        
        $this->next();
    }
    
    public function verifyAction(){
        if(!$this->user->login)
            return $this->loginFirst('adminLogin');
        
        if(!module_exists('user-email'))
            return $this->show404();
        
        if(!module_exists('mailer'))
            return $this->show404();
        
        $id = $this->param->id;
        $email = UEmail::get($id, false);
        
        if(!$email || $email->user != $this->user->id || $email->status != 1)
            return $this->show404();
        
        $params = $this->_defaultParams();
        $params['title'] = 'Email Verification';
        $params['error'] = false;
        
        $code = $email->code;
        if(!$code){
            $code = md5(time().'-'.$email->id.'-'.$email->address.'-'.uniqid());
            UEmail::set(['code'=>$code], $id);
        }
        
        $target = $this->router->to('adminMeEmailVerifyConfirm', ['id'=>$email->id,'code'=>$code]);
        if(substr($target,0,4) != 'http')
            $target = 'http://' . $this->config->host . $target;
        
        $email_params = [
            'to' => [
                [ 
                    'email' => $email->address,
                    'name' => $this->user->fullname
                ]
            ],
            'subject' => '[' . $this->setting->site_name . '] Email verification',
            'link' => $target,
            'user' => (object)[
                'fullname' => $this->user->fullname
            ]
        ];
        
        if(false === $this->mailer->send('admin-me-setting/verify-email', $email_params))
            $params['error'] = $this->mailer->getError();
        
        $this->respond('me/setting/email-verify', $params);
    }
    
    public function verifyConfirmAction(){
        if(!$this->user->login)
            return $this->loginFirst('adminLogin');
        
        if(!module_exists('user-email'))
            return $this->show404();
        
        if(!module_exists('mailer'))
            return $this->show404();
        
        $id = $this->param->id;
        $email = UEmail::get($id, false);
        
        if(!$email || $email->user != $this->user->id || $email->status != 1)
            return $this->show404();
        
        $params = $this->_defaultParams();
        $params['title'] = 'Email Validation';
        $params['error'] = false;
        
        $code = $this->param->code;
        if($code != $email->code)
            $params['error'] = true;
        else
            UEmail::set(['code' => '', 'status' => 2], $id);
        
        $this->respond('me/setting/email-verify-confirm', $params);
    }
    
}