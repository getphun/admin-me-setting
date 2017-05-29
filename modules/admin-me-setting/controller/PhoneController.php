<?php
/**
 * Current user setting
 * @package admin-me-setting
 * @version 0.0.1
 * @upgrade true
 */

namespace AdminMeSetting\Controller;
use User\Model\User;
use UserPhone\Model\UserPhone as UPhone;

class PhoneController extends AdminMeController
{
    private function _defaultParams(){
        return [
            'title'             => 'My Phone',
            'nav_title'         => 'My Setting',
            'active_menu'       => 'my-setting',
            'active_submenu'    => 'my-setting'
        ];
    }
    
    private function next($next='adminMePhone'){
        $ref = $this->req->getQuery('ref');
        if(!$ref)
            $ref = $this->router->to($next);
        $this->redirect($ref);
    }
    
    public function indexAction(){
        if(!$this->user->login)
            return $this->loginFirst('adminLogin');
        
        if(!module_exists('user-phone'))
            return $this->show404();
        
        $params = $this->_defaultParams();
        
        $params['phones']  = UPhone::get(['user'=>$this->user->id]);
        $params['title']   = 'My Phone';
        $params['success'] = false;
        
        if(false === ($form = $this->form->validate('admin-me-phone')))
            return $this->respond('me/setting/phone', $params);
        
        $phone = [
            'user'   => $this->user->id,
            'number' => strtolower($form->phone),
            'status' => 1
        ];
        
        $phone['id'] = UPhone::create($phone);
        $phone['created'] = $phone['updated'] = date('Y-m-d H:i:s');
        $params['phones'][] = (object)$phone;
        
        $params['success'] = true;
        
        $this->respond('me/setting/phone', $params);
    }
    
    public function primaryAction(){
        if(!$this->user->login)
            return $this->show404();
        
        if(!module_exists('user-phone'))
            return $this->show404();
        
        $id = $this->param->id;
        $phone = UPhone::get($id, false);
        // we can set the phone default if
        // - the phone is exists
        // - the phone is mine
        if($phone && $phone->user == $this->user->id){
            // set other phone to verified
            UPhone::set(['status'=>1], ['user'=>$this->user->id, 'status'=>3]);
            UPhone::set(['status'=>3], $phone->id);
        }
        
        $this->next();
    }
    
    public function removeAction(){
        if(!$this->user->login)
            return $this->show404();
        
        if(!module_exists('user-phone'))
            return $this->show404();
        
        $id = $this->param->id;
        $phone = UPhone::get($id, false);
        // we can remove phone if
        // - the phone is exists 
        // - the phone is mine
        // - the phone is not default 
        if( $phone && $phone->user == $this->user->id && $phone->status != 3 ){
            UPhone::remove($id);
        }
        
        $this->next();
    }
}