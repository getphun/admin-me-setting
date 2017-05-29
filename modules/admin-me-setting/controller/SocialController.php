<?php
/**
 * Current user setting
 * @package admin-me-setting
 * @version 0.0.1
 * @upgrade true
 */

namespace AdminMeSetting\Controller;
use User\Model\User;
use UserSocial\Model\UserSocial as USocial;

class SocialController extends AdminMeController
{
    private function _defaultParams(){
        return [
            'title'             => 'My Social',
            'nav_title'         => 'My Setting',
            'active_menu'       => 'my-setting',
            'active_submenu'    => 'my-setting'
        ];
    }
    
    private function next($next='adminMeSocial'){
        $ref = $this->req->getQuery('ref');
        if(!$ref)
            $ref = $this->router->to($next);
        $this->redirect($ref);
    }
    
    public function editAction(){
        if(!$this->user->login)
            return $this->loginFirst('adminLogin');
        
        if(!module_exists('user-social'))
            return $this->show404();
        
        $params = $this->_defaultParams();
        $params['next'] = $this->req->getQuery('ref');
        if(!$params['next'])
            $params['next'] = $this->router->to('adminMeSocial');
        $params['jses'] = ['js/admin-me-setting.js'];
        
        $id = $this->param->id;
        
        if($id){
            $social = USocial::get($id, false);
            if(!$social || $social->user != $this->user->id)
                return $this->show404();
            $params['social'] = $social;
            $params['title'] = 'Update Social Account';
        }else{
            $social = new \stdClass();
            $social->user = $this->user->id;
            $params['title'] = 'Create New Social Account';
        }
        
        if(false === ($form = $this->form->validate('admin-me-social', $social)))
            return $this->respond('me/setting/social-edit', $params);
        
        if($id){
            if($social->vendor != $form->vendor)
                $form->vuid = '';
            USocial::set($form, ['id'=>$social->id]);
        }else{
            foreach($form as $field => $value)
                $social->$field = $value;
            USocial::create($social);
        }
        
        $this->next();
    }
    
    public function indexAction(){
        if(!$this->user->login)
            return $this->loginFirst('adminLogin');
        
        if(!module_exists('user-social'))
            return $this->show404();
        
        $params = $this->_defaultParams();
        
        $params['socials'] = USocial::get(['user'=>$this->user->id]);
        $params['title']   = 'My Social';
        
        $this->respond('me/setting/social', $params);
    }
    
    public function removeAction(){
        if(!$this->user->login)
            return $this->show404();
        
        if(!module_exists('user-social'))
            return $this->show404();
        
        $id = $this->param->id;
        $social = USocial::get($id, false);
        // we can remove social if
        // - the social is exists
        // - the social is mine
        if($social && $social->user == $this->user->id)
            USocial::remove($id);
        
        $this->next();
    }
}