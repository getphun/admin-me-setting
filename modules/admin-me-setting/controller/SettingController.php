<?php
/**
 * Current user setting
 * @package admin-me-setting
 * @version 0.0.1
 * @upgrade true
 */

namespace AdminMeSetting\Controller;
use User\Model\User;
use User\Model\UserSession as USession;
use UserProperty\Model\UserProperty as UProperty;

class SettingController extends AdminMeController
{
    private function _defaultParams(){
        return [
            'title'             => 'My Setting',
            'nav_title'         => 'My Setting',
            'active_menu'       => 'my-setting',
            'active_submenu'    => 'my-setting'
        ];
    }
    
    public function passwordAction(){
        if(!$this->user->login)
            return $this->loginFirst('adminLogin');
        
        $params = $this->_defaultParams();
        $params['title'] = 'My Password';
        $params['saved'] = false;
        $params['jses']  = ['js/admin-me-setting.js'];
        $params['csses'] = ['css/admin-me-setting.css'];
        
        if(false === ($form = $this->form->validate('admin-me-password')))
            return $this->respond('me/setting/password', $params);
        
        // let validate both submited password
        if($form->retype_password != $form->new_password){
            $this->form->setError('retype_password', 'Both password don\'t match');
            return $this->respond('me/setting/password', $params);
        }
        
        if(!$this->user->testPassword($form->current_password, $this->user->password)){
            $this->form->setError('current_password', 'Wrong password');
            return $this->respond('me/setting/password', $params);
        }
        
        if( $form->current_password != $form->new_password ){
            $new_password = $this->user->genPassword($form->new_password);
            User::set(['password'=>$new_password], $this->user->id);
        }
        
        $params['saved'] = true;
        
        if($form->truncate_session){
            USession::remove([
                'user = :user AND id != :id',
                'bind' => [
                    'user' => $this->user->id,
                    'id'   => $this->user->session->id
                ]
            ]);
        }
        
        $this->respond('me/setting/password', $params);
    }
    
    public function profileAction(){
        if(!$this->user->login)
            return $this->loginFirst('adminLogin');
        
        $params = $this->_defaultParams();
        $params['title'] = 'My Profile';
        $params['saved'] = false;
        
        $user = User::get($this->user->id, false);
        
        $form_validation = $this->config->form['admin-me-setting'];
        
        if(module_exists('user-property')){
            $user_properties = $this->config->user_property;
            if($user_properties)
                $form_validation = array_replace($form_validation, $user_properties);
            
            $this->config->set('form', 'admin-me-setting', $form_validation);
            
            $user_prop = UProperty::get(['user'=>$this->user->id]);
            $user_prop_current = [];
            if($user_prop){
                foreach($user_prop as $prop){
                    $user->{$prop->name} = $prop->value;
                    $user_prop_current[$prop->name] = $prop->value;
                }
            }
        }
        
        $params['form'] = $form_validation;
        
        
        if(false === ($form = $this->form->validate('admin-me-setting', $user)))
            return $this->respond('me/setting/profile', $params);
        
        $user = [];
        if(isset($form->name)){
            $form->name = strtolower($form->name);
            if($form->name != $this->user->name)
                $user['name'] = $form->name;
        }
        if(isset($form->fullname) && $form->fullname != $this->user->fullname)
            $user['fullname'] = $form->fullname;
        
        if($user)
            User::set($user, $this->user->id);
        
        // user profiles
        if(module_exists('user-property')){
            $user_profile_update = [];
            $user_profile_insert = [];
            
            foreach($user_properties as $field => $args){
                if(!isset($form->$field))
                    continue;
                if(!isset($user_prop_current[$field]))
                    $user_profile_insert[$field] = $form->$field;
                elseif($user_prop_current[$field] != $form->$field)
                    $user_profile_update[$field] = $form->$field;
            }
            
            if($user_profile_insert){
                $insb = [];
                foreach($user_profile_insert as $field => $value)
                    $insb[] = ['user' => $this->user->id, 'name' => $field, 'value' => $value];
                UProperty::createMany($insb);
            }
            
            if($user_profile_update){
                foreach($user_profile_update as $field => $value)
                    UProperty::set(['value' => $value], ['user'=>$this->user->id, 'name'=>$field]);
            }
        }
        
        $params['saved'] = true;
        $this->respond('me/setting/profile', $params);
    }
}