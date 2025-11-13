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

?>