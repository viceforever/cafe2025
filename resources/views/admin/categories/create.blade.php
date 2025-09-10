@extends('layouts.app')

@section('title', 'Создать новую категорию')

@section('main_content')
<div class="container min-vh-100 d-flex flex-column">
    <div class="row flex-grow-1" style="margin-top: 220px; margin-bottom: 50px;">
        <div class="col-12">
            <h1>Создать новую категорию</h1>

            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="name_category" class="form-label">Название категории</label>
                    <input type="text" class="form-control" id="name_category" name="name_category" required>
                </div>
                <button type="submit" class="btn btn-primary">Создать категорию</button>
            </form>
        </div>
    </div>
</div>
@endsection