<?php

####### Ajax #######
Router::all(RouteMap::ROUTES['ajax'], function($name, $action) {
	return Controller::load('ajaxController', compact('name', 'action'));
});

####### Ajax search #######
Router::post(RouteMap::ROUTES['search_manga'], function() {
	return Controller::load('mangaController@search_ajax');
});

####### Tool leech #######
Router::match(['GET', 'POST'], RouteMap::ROUTES['tool_leech'], function($block) {
	return Controller::load('toolLeechController@index', compact('block'));
});

####### Messenger #######
Router::post(RouteMap::ROUTES['messenger'], function() {
	return Controller::load('messengerController@loadMessage');
});

####### Comment #######
Router::group(RouteMap::ROUTES['comment'], function() {
	Router::get('/', function() {
		return Controller::load('commentController@list');
	});

	Router::post('/', function() {
		return Controller::load('commentController@insert');
	});

	Router::put('/', function() {
		return Controller::load('commentController@update');
	});

	Router::delete('/', function() {
		return Controller::load('commentController@delete');
	});
});

?>