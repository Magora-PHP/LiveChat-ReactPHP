<?php

namespace App\Http\Requests;

class MessageCreateRequest extends Request
{

    /**
     * @inheritdoc
     */
    protected $dontFlash = ['room_id', 'body'];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'room_id' => 'required|integer',
            'body' => 'required|string',
        ];
    }
}
