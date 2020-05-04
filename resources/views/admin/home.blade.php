@extends('layouts.app_admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card mb-3">
                <div class="card-header">管理画面</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <p><a href="{{ route('admin.sale') }}">売り上げ管理画面へ</a></p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">商品をcsvでアップロードする。</div>

                <div class="card-body">
                    <form action="{{ route('admin.import.csv') }}" method="post" enctype="multipart/form-data" id="csvUpload" onsubmit="return check(this)">
                        <input type="file" value="ファイルを選択" name="csv_file">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-primary mt-2 mb-3">送信する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
