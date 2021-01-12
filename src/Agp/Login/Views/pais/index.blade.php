@extends('layout.app')

@section('content')

{{--    Data e hora: 2020-09-23 09:39:59           --}}
{{--    View/index.blade gerada automaticamente       --}}

     <div class="card card-custom gutter-b">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label font-weight-bolder text-success">Paiss</span>
                <span class="text-muted mt-3 font-weight-bold font-size-sm">Aqui estão seus paiss.</span>
            </h3>
            <div class="card-toolbar">
                <a href="{{route('web.pais.create')}}" class="btn btn-success font-weight-bolder font-size-sm">
                    <span class="svg-icon svg-icon-md svg-icon-white">{{ Metronic::getSVG('media/svg/icons/Code/Plus.svg') }}</span>
                    Adicionar</a>
            </div>
        </div>

        {{ PaisComposer::getDatatable() }}

    </div>

    {{ LogComposer::get('adm_pais') }}
@endsection