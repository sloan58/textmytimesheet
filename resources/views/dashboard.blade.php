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
                    <button type="button"
                            class="btn btn-success btn-wd bootstrap-modal-form-open"
                            data-toggle="modal"
                            data-target="#reportModal">
                <span class="btn-label">
                    <i class="fa fa-plus"></i>
                </span>
                        Generate Report
                    </button>
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Hours This Week</h3>
                </div>
                <div class="panel-body">
                    {!! $hoursThisWeek->render() !!}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Top Contributors</h3>
                </div>
                <div class="panel-body">
                    {!! $topContributors->render() !!}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Bud Light's Slammed This Week</h3>
                </div>
                <div class="panel-body">
                    {!! $budLightsThisWeek->render() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@include('report_modal')
