@if($datatable->isModal)
    <!--begin::Modal-->
    <div id="dtm_{{$datatable->id}}" class="modal fade" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" id="modal-content_{{$datatable->id}}">
                <div class="modal-header py-5">
                    <h5 class="modal-title">{{$datatable->title}}<span class="d-block text-muted font-size-sm">{{$datatable->subtitle}}</span></h5>
                    <a href="#" class="btn btn-icon btn-light btn-hover-primary btn-sm mx-3" data-dismiss="modal" aria-label="Close">
                        <span class="svg-icon svg-icon-md svg-icon-primary mr-0">{{ Metronic::getSVG('media/svg/icons/Code/Error-circle.svg') }}</span>
                    </a>
                </div>
                <div class="modal-body">
                    <!--begin: Search Form-->
                    <!--begin::Search Form-->
                    <div class="mb-5">
                        <div class="row align-items-center">
                            <div class="col-lg-9 col-xl-8">
                                <div class="row align-items-center">
                                    <div class="col-md-4 my-2 my-md-0">
                                        <div class="input-icon">
                                            <input class="form-control" id="generalSearch_{{$datatable->id}}" type="text" placeholder="Pesquisar...">
                                            <span><i class="flaticon2-search-1 text-muted"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Search Form-->
                    <!--end: Search Form-->
                    <!--begin: Datatable-->
                    <div class="datatable datatable-bordered datatable-head-custom" id="dt_{{$datatable->id}}"></div>
                    <!--end: Datatable-->
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal-->
@else
    <div class="card-body py-0 mb-5">
        <div class="table-responsive">
            <input class="form-control" id="generalSearch_{{$datatable->id}}" type="text" placeholder="Pesquisar...">
            <div id="dt_{{$datatable->id}}">

            </div>
        </div>
    </div>
@endif

@section('scripts')
    <script>
        //Os eventos window.onload são sobreescritos ou não funcionaram se chamar 2 vezes em lugares ou valores diferentes.
        //Por isso, o modal não funciona em uma página com outra datatable já carregada.
        window.onload = function() {
            var params = {!! $datatable !!};
            //Adiciona métodos função que não é possível adicionar no PHP
            params.search.input = $("#generalSearch_{{$datatable->id}}")

            var datatable = $('#dt_{{$datatable->id}}').KTDatatable(params);
            var actions = datatable.getColumnByField('actions');
            if (actions) {
                actions.template = function (row, index, datatable) {
                    var obj = {!! \ViewComposer\PaisComposer::getActions(null,true) !!};
                    var mapObj = {
                        ___rowid: row.id
                    };
                    return (obj.data)
                        .replace(/___rowid/gi, function (matched) {
                            return mapObj[matched];
                        });
                };
            }

            @if($datatable->isModal)
                // fix datatable layout after modal shown
                var modal = $('#dtm_{{$datatable->id}}');
                datatable.hide();
                var alreadyReloaded = false;
                modal.on('shown.bs.modal', function() {
                    if (!alreadyReloaded) {
                        var modalContent = $(this).find('#modal-content_{{$datatable->id}}');
                        datatable.spinnerCallback(true, modalContent);

                        datatable.reload();

                        datatable.on('datatable-on-layout-updated', function() {
                            datatable.show();
                            datatable.spinnerCallback(false, modalContent);
                            datatable.redraw();
                        });

                        alreadyReloaded = true;
                    }
                });
            @endif
        };
    </script>
@endsection