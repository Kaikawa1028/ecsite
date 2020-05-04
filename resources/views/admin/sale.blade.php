@extends('layouts.app_admin')

@section('content')
    <div class="container">
        <div class="row">
            <form method="GET" action="{{ route('admin.sale') }}" class="mb-3">
                <div class="form-group">
                    <select name="targetMonth" class="form-control">
                        @foreach($displayed_months as $displayed_month)
                        <option value="{{ $displayed_month }}" @if( $target_month == $displayed_month ) selected @endif>{{ $displayed_month }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="submit" value="検索">
            </form>
    
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>商品名</th>
                        <th>購入数</th>
                        <th>単価</th>
                        <th>売り上げ</th>
                        <th>購入者</th>
                        <th>購入日時</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->item->name }}</td>
                        <td>{{ $sale->quantity }}</td>
                        <td>{{ $sale->amount }}</td>
                        <td>{{ $sale->quantity *  $sale->amount}}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>{{ $sale->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>

            </table>

            <form method="GET" action="{{ route('admin.export.csv') }}" class="mb-3">
                <input type="hidden" value="{{ $target_month }}" name="targetMonth">
                <input type="submit" value="csvダウンロード">
            </form>
        </div>
        <div>
            <a href="{{ route('admin.home') }}">トップへ戻る</a>
        </div>
    </div>
@endsection
