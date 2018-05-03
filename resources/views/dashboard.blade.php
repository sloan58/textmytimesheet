@extends('layout')

@section('header')
    <section class="content-header">
    </section>
@endsection


@section('content')
    <div class="row" id="report">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-title">Dashboard</div>
                </div>
            </div>
            <button type="button"
                    class="btn btn-success btn-wd bootstrap-modal-form-open"
                    data-toggle="modal"
                    data-target="#reportModal">
                <span class="btn-label">
                    <i class="fa fa-plus"></i>
                </span>
                Generate Report
            </button>
            <div>
                {!! $chartjs->render() !!}
            </div>
        </div>
    </div>
@endsection

@include('report_modal')
