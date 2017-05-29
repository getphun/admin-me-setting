<?php
/**
 * Admin me controller main
 * @package admin-me-setting
 * @version 0.0.1
 * @upgrade true
 */

namespace AdminMeSetting\Controller;

class AdminMeController extends \AdminController
{
    public function respond($view, $params=[], $cache=null){
        $sidemenu = [
            [ 'adminMeSetting',     'Profile',          'user' ],
            [ 'adminMePassword',    'Password',         'user' ],
            [ 'adminMeEmail',       'Email',            'user-email' ],
            [ 'adminMePhone',       'Phone',            'user-phone' ],
            [ 'adminMeSocial',      'Social Account',   'user-social' ]
        ];
        
        $params['sidemenu'] = [];
        
        foreach($sidemenu as $menu){
            if(!module_exists($menu[2]))
                continue;
            
            $smenu = (object)[
                'label' => $menu[1],
                'target'=> $menu[0],
                'active'=> false
            ];
            
            if($this->router->route['name'] == $menu[0])
                $smenu->active = true;
            
            $params['sidemenu'][] = $smenu;
        }
        
        parent::respond($view, $params, $cache);
    }
}