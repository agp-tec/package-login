@extends('layout.app')

@section('content')
    <div class="card card-custom gutter-b">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label font-weight-bolder text-success">Alterar {{ $form->getModel()->nome }}</span>
            </h3>
            <div class="card-toolbar">
                <a href="{{route('web.cidade.index')}}" class="btn btn-icon btn-light btn-hover-primary btn-sm mx-3">
                    <span class="svg-icon svg-icon-md svg-icon-primary mr-0">{{ Metronic::getSVG('media/svg/icons/Code/Error-circle.svg') }}</span>
                </a>
            </div>
        </div>
        <div class="card-body py-0">
            {!! form_start($form); !!}
                        <div class="form-group col-12 col-md-12 col-xl-12">
                {!! form_label($form->nome) !!}
                {!! form_widget($form->nome) !!}
                <span class="form-text text-muted">Nome da cidade.</span>
            </div>
            <div class="form-group col-12 col-md-12 col-xl-12">
                {!! form_label($form->uf) !!}
                {!! form_widget($form->uf) !!}
                <span class="form-text text-muted">Código do estado no formato ISO 3166-2. Exemplo: SP</span>
            </div>
            <div class="form-group col-12 col-md-12 col-xl-12">
                {!! form_label($form->id_uf) !!}
                {!! form_widget($form->id_uf) !!}
                <span class="form-text text-muted">Código da UF de acordo com tabela do IBGE.</span>
            </div>
            <div class="form-group col-12 col-md-12 col-xl-12">
                {!! form_label($form->sigla_pais) !!}
                {!! form_widget($form->sigla_pais) !!}
                <span class="form-text text-muted">Código do país no formato ISO 3166-1 alpha-2. Exemplo: BR</span>
            </div>
            <div class="form-group col-12 col-md-12 col-xl-12">
                {!! form_label($form->pais) !!}
                {!! form_widget($form->pais) !!}
                <span class="form-text text-muted">Chave estrangeira de adm_pais.id.</span>
            </div>
            <div class="form-row">
                {!! form_row($form->submit) !!}
            </div>
            {!! form_end($form, false); !!}

            {{-- OU
            {!! form($form) !!} --}}

        </div>
    </div>
@endsection