@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            <div class="form-group">
                <a href="{{ url('/rooms/create') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-plus"></i> Add new room
                </a>
            </div>
            <div class="panel panel-default">
                <div id="rooms" class="panel-body rooms">

                    @foreach ($items as $item)
                        <div class="rooms-item col-md-4">
                            <a href="{{ url('/rooms/'.$item->id) }}">
                                <h4 class="text-info">{{ $item->name }}</h4>
                                <div>{{ $item->description }}</div>
                            </a>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
