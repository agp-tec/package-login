<form class="form" id="form-login" novalidate="novalidate" method="post" action="{{ route('web.login.find') }}">
    @csrf
    @if(count($contas) > 0)
        <div class="card bg-primary-o-30 card-custom card-stretch gutter-b">
            <div class="card-body pt-2">
                @foreach ($contas as $conta)
                    <div class="d-flex flex-wrap align-items-center mt-5">
                        <div class="symbol symbol-60 symbol-2by3 flex-shrink-0 mr-4">
                            <img class="rounded-circle" src="{{ Helper::getAvatarUrl($conta->email, 60) }}" alt="">
                        </div>
                        <div class="d-flex flex-column flex-grow-1 my-lg-0 my-2 mr-2">
                            <a href="#"
                               class="text-dark-75 font-weight-bold text-hover-primary font-size-lg mb-1">{{ $conta->nome }}</a>
                            <span class="text-muted font-weight-bold">{{ $conta->email }}</span>
                        </div>
                        <div class="d-flex align-items-center mt-lg-0">
                            <a class="btn btn-icon btn-light btn-sm"
                               href="{{ route(config('login.forget_route','web.login.forget'),['email' => $conta->email]) }}">
                                {{ Metronic::getSVG('media/svg/icons/Home/Trash.svg', 'svg-icon-danger') }}
                            </a>
                            <a class="btn btn-icon btn-light btn-sm ml-1" href="javascript:;"
                               onclick="$('#id').val('{{$conta->id ?? ''}}');$('#form-login').submit();">
                                {{ Metronic::getSVG('media/svg/icons/Navigation/Right-2.svg', 'svg-icon-success') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    @include('Login::flash-message')
    <input type="hidden" id="id" name="id" value="">
    <input type="hidden" name="empresa" value="">
    <div class="form-group">
        <input class="form-control form-control-solid h-auto py-5 px-6 cpfdebug" autofocus
               placeholder="{{ $accept }}" id="login" name="login"/>
    </div>
    <div class="form-group d-flex flex-wrap justify-content-between align-items-center">
        <a href="{{ route('web.login.create') }}" class="text-dark-50 text-hover-primary my-3 mr-2">Criar conta</a>
        <button type="submit" class="btn btn-primary font-weight-bold px-9 py-4 my-3">Entrar</button>
    </div>
</form>
