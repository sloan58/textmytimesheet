<!-- Modal -->
<div id="report">
    <div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel">TMTS Reporting</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => ['report']]) !!}
                    <div class="form-group" v-show="custom_dates">
                        <label for="start_date" class="col-sm-3 control-label">Start Date</label>
                        <input data-provide="datepicker" type="text" class="form-control" name="start_date" placeholder="Report Start Date">
                    </div>
                    <div class="form-group" v-show="custom_dates">
                        <label for="end_date" class="col-sm-3 control-label">End Date</label>
                        <input data-provide="datepicker" type="text" class="form-control" name="end_date" placeholder="Report End Date">
                    </div>
                    {!! Form::submit('Send Report   ', ['class' => 'btn btn-wd btn-info']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

@push('modal_scripts')
    <script>
        <!-- javascript for init -->
        $('.datepicker').datepicker({
            format: 'YYYY-MM-DD',
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            }
        });
    </script>
@endpush