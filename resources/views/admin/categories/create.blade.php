@extends('layouts.app')

@section('title', 'Создать новую категорию')

@section('main_content')
<div class="container min-vh-100 d-flex flex-column">
    <div class="row flex-grow-1" style="margin-top: 220px; margin-bottom: 50px;">
        <div class="col-12">
            <h1>Создать новую категорию</h1>

            @if ($errors->any())
                <div style="background-color: #f8d7da; border: 1px solid #f5c2c7; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px; color: #842029;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="name_category" class="form-label">Название категории</label>
                    <input type="text" class="form-control @error('name_category') is-invalid @enderror" 
                           id="name_category" name="name_category" value="{{ old('name_category') }}" required>
                    @error('name_category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Создать категорию</button>
            </form>
        </div>
    </div>
</div>
@endsection
