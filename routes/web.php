<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Rooms
Route::get('/rooms', 'RoomController@showList');
Route::get('/rooms/{id}', 'RoomController@showRoom')->where('id', '\d+');

Route::group(['middleware' => 'auth'], function ()
{
    // User data
    Route::get('/profile', 'User\UserController@showProfileForm');
    Route::post('/profile', 'User\UserController@updateProfile');

    // Rooms
    Route::get('/rooms/create', 'RoomController@showRoomForm');
    Route::post('/rooms/create', 'RoomController@createRoom');

    // Messages
    Route::post('/messages/create', 'RoomController@createMessage');
});