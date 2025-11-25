<?php

####### index #######
Router::all(RouteMap::ROUTES['home'], function() {
	return Controller::load('homeController@index');
});


####### auth ########
Router::match(['GET', 'POST'], RouteMap::ROUTES['login'], function(){
	return Controller::load('authController@login');
}, 'is_auth_route');

Router::get(RouteMap::ROUTES['logout'], function(){
	return Controller::load('authController@logout');
}, 'is_auth_route');

Router::match(['GET', 'POST'], RouteMap::ROUTES['register'], function(){
	return Controller::load('authController@register');
}, 'is_auth_route');

Router::match(['GET', 'POST'], RouteMap::ROUTES['reset_password'], function(){
	return Controller::load('authController@reset_password');
}, 'is_auth_route');

Router::match(['GET', 'POST'], RouteMap::ROUTES['change_password'], function(){
	return Controller::load('authController@change_password');
});

Router::group(RouteMap::ROUTES['forgot_password'], function(){

	Router::match(['GET', 'POST'], '/', function(){
		return Controller::load('authController@forgot_password_request');
	});

	Router::match(['GET', 'POST'], '/{key}', function($key) {
		return Controller::load('authController@forgot_password_change', $key);
	});
}, 'is_auth_route');

####### profile #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['profile'], function($id, $block, $action){
	$id = strtolower($id);
	if($id == 'me' || $id == Auth::$id)
	{
		return Controller::load('profileController@me', compact('block', 'action'));
	}

	return Controller::load('profileController@user', compact('id', 'block'));

});

####### Notification #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['notification'], function($id) {
	return Controller::load('notificationController@index', ['id' => $id]);
});

####### History #######
Router::get(RouteMap::ROUTES['history'], function() {
	return Controller::load('historyController@index');
});

####### Bookmark #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['bookmark'], function($type) {
	if(strtolower($type ?? '') == 'api')
	{
		return Controller::load('bookmarkController@api');
	}
	return Controller::load('bookmarkController@index', $type);
});

####### Messenger #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['messenger'], function($block, $id) {
	return Controller::load('messengerController@index', ['block' => $block, 'id' => $id]);
});

####### Manga #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['manga'], function($id) {
	if($id)
	{
		return Controller::load('mangaController@manga', ['id' => $id]);
	}
	return Controller::load('mangaController@list');
}, 'manga');

####### Chapter #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['chapter'], function($id_manga, $id_chapter) {
	return Controller::load('mangaController@chapter', compact('id_manga', 'id_chapter'));
});

####### Search #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['search_manga'], function() {
	return Controller::load('mangaController@search');
});

####### Team #######
Router::get(RouteMap::ROUTES['team'], function($name) {
	return Controller::load('mangaController@team', compact('name'));
});

####### Comment #######
Router::get(RouteMap::ROUTES['comments'], function() {
	return Controller::load('commentController@all');
});


####### Admin-Panel #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['admin_panel'], function($group, $block = null, $action = null) {
	return Controller::load('adminPanelController@index', compact('group', 'block', 'action'));
});

####### My-Team #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['my_team'], function($block = null, $action = null) {
	return Controller::load('teamController@index', compact('block', 'action'));
});


####### Report #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['report'], function($block = null) {
	return Controller::load('reportController@index', compact('block'));
}, 'report');

####### Manga Management #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['manga_management'], function($action, $id = null) {
	return Controller::load('mangaManagementController@index', compact('action', 'id'));
});


?>