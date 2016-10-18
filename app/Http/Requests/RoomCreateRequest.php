<?php

namespace App\Http\Requests;

class RoomCreateRequest extends Request
{

    /**
     * @inheritdoc
     */
    protected $dontFlash = ['name', 'description'];

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
            'name' => 'required|string|max:128',
            'description' => 'string|max:255',
        ];
    }
}
