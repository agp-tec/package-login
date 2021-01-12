@extends('layout.app')

@section('content')
     <div class="card card-custom gutter-b">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label font-weight-bolder text-success">Cidades</span>
                <span class="text-muted mt-3 font-weight-bold font-size-sm">Aqui estão seus cidades.</span>
            </h3>
            <div class="card-toolbar">
                <a href="{{route('web.cidade.create')}}" class="btn btn-success font-weight-bolder font-size-sm">
                    {{--TODO Implatar Metronic--}}
                    <span class="svg-icon svg-icon-md svg-icon-white">{{--{{ Metronic::getSVG('media/svg/icons/Code/Plus.svg') }}--}}</span>
                    Adicionar</a>
            </div>
        </div>

         {{--TODO Implatar Datatable--}}
        {{--{{ CidadeComposer::getDatatable() }}--}}

    </div>

     {{--TODO Implatar Log--}}
    {{--{{ LogComposer::get('adm_cidade') }}--}}
@endsection