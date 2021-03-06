<?php
/**
 * Created by PhpStorm.
 * User: exonintrendo
 * Date: 4/12/14
 * Time: 1:44 PM
 */

Router::route('/', array('controller' => 'posts', 'action' => 'index'));
Router::route('/user/:username', array('controller' => 'users', 'action' => 'view', 'username'));
Router::route('/post/:slug', array('controller' => 'posts', 'action' => 'view'));
Router::route('/login/', array('controller' => 'users', 'action' => 'login'));
Router::route('/logout/', array('controller' => 'users', 'action' => 'logout'));
Router::route('/register/', array('controller' => 'users', 'action' => 'add'));
Router::route('/movies', array('controller' => 'pages', 'action' => 'movies'));
Router::route('/api/', array('controller' => 'apis', 'action' => 'index'));
Router::route('/admin', array('controller' => 'users', 'action' => 'view', 'admin'));

/*
 * Default Primer routes. If you do not want to support the default routes,
 * remove these lines.
 */
Router::route('/:controller', array('action' => 'index'));
Router::route('/:controller/:action/*');