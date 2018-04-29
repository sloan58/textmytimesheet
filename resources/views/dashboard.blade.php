@extends('layout')

@section('header')
    <section class="content-header">
    </section>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-title">Dashboard</div>
                </div>
            </div>
            <button class="btn btn-success" type="submit">Generate Report</button>
            <div>
                {!! $chartjs->render() !!}
            </div>
        </div>
    </div>
@endsection
