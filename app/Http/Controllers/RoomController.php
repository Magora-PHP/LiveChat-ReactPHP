<?php

namespace App\Http\Controllers;

use App\Entities\Message;
use App\Entities\Room;
use App\Http\Requests\RoomCreateRequest;
use App\Http\Requests\MessageCreateRequest;
use Illuminate\Contracts\Auth\Guard;
use Session;

class RoomController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function showList()
    {
        $items = Room::all();

        return view('room.list', compact('items'));
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function showRoomForm()
    {
        return view('room.form');
    }

    /**
     * @param RoomCreateRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createRoom(RoomCreateRequest $request)
    {
        Room::create($request->all());

        Session::flash('success', 'Room created successfully.');
        return redirect('/rooms');
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function showRoom($id)
    {
        $room = Room::with(['messages' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->find($id);

        if (!$room) {
            abort(404, 'Room not found');
        }

        $chatConnUrl = 'ws://'.env('CHAT_HOST').':'.env('CHAT_PORT').env('CHAT_PATH');

        return view('room.view', compact('room', 'chatConnUrl'));
    }

    /**
     * @param MessageCreateRequest $request
     * @param Guard                $guard
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createMessage(MessageCreateRequest $request, Guard $guard)
    {
        $data = $request->all();
        if (!($room = Room::find($data['room_id']))) {
            abort(404, 'Room not found');
        }
        $data['user_id'] = $guard->id();
        Message::create($data);

        return response()->json(['success' => true]);
    }
}
