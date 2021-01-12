@extends('layout.app')

@section('content')
    <div class="card card-custom gutter-b">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label font-weight-bolder text-success">Alterar {{ $form->getModel()->nome }}</span>
            </h3>
            <div class="card-toolbar">
                <a href="{{route('web.pais.index')}}" class="btn btn-icon btn-light btn-hover-primary btn-sm mx-3">
                    <span class="svg-icon svg-icon-md svg-icon-primary mr-0">{{ Metronic::getSVG('media/svg/icons/Code/Error-circle.svg') }}</span>
                </a>
            </div>
        </div>
        <div class="card-body py-0">
            {!! form_start($form); !!}
                        <div class="form-group col-12 col-md-12 col-xl-12">
                {!! form_label($form->exonimo_portugues) !!}
                {!! form_widget($form->exonimo_portugues) !!}
                <span class="form-text text-muted">Nome do pais em portugues.</span>
            </div>
            <div class="form-group col-12 col-md-12 col-xl-12">
                {!! form_label($form->exonimo_ingles) !!}
                {!! form_widget($form->exonimo_ingles) !!}
                <span class="form-text text-muted">Nome do pais em ingles (EUA).</span>
            </div>
            <div class="form-group col-12 col-md-12 col-xl-12">
                {!! form_label($form->endonimo) !!}
                {!! form_widget($form->endonimo) !!}
                <span class="form-text text-muted">Nome do pais no idioma nativo.</span>
            </div>
            <div class="form-group col-12 col-md-12 col-xl-12">
                {!! form_label($form->imagem) !!}
                {!! form_widget($form->imagem) !!}
                <span class="form-text text-muted">Nome do arquivo de bandeira do pais.</span>
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