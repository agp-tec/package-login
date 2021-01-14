<form class="form" novalidate="novalidate" method="post" action="{{ route('web.login.store') }}">
    @csrf
    @include('Login::flash-message')
    <div class="form-group">
        <input class="form-control form-control-solid h-auto py-5 px-6 textodebug" type="text" autofocus
               placeholder="Nome completo" name="nome" value="{{ old('nome') }}" autocomplete="off"/>
    </div>
    <div class="form-group">
        <input class="form-control form-control-solid h-auto py-5 px-6 cpfcnpj" type="text"
               placeholder="CPF ou CNPJ" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}" autocomplete="off"/>
    </div>
    <div class="form-group">
        <input class="form-control form-control-solid h-auto py-5 px-6 emaildebug" type="email"
               placeholder="E-mail"
               name="usuario[email]" value="{{ old('usuario.email') }}" autocomplete="off"/>
    </div>
    <div class="form-group">
        <div class="input-group" id="show_hide_password">
            <input class="form-control form-control-solid h-auto py-5 px-6" type="password"
                   placeholder="Senha" id="password" name="usuario[senha]" autocomplete="off" required
                   min="1"/>
            <button type="button" toggle="#password" class="btn toggle-password">
                <i class="fa fa-fw fa-eye field-icon"></i>
            </button>
        </div>
    </div>
    <div class="form-group d-flex flex-wrap flex-center">
        <button type="submit" class="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4">Salvar</button>
        <a href="{{ route('web.login.index') }}"
           class="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4">Cancelar
        </a>
    </div>
</form>

@section('scripts')
    @parent
    <script>
        $(document).ready(function () {
            $('.toggle-password').on('click', function () {
                $('.field-icon').toggleClass('fa-eye fa-eye-slash');
                let input = $($(this).attr('toggle'));
                if (input.attr('type') == 'password') {
                    input.attr('type', 'text');
                } else {
                    input.attr('type', 'password');
                }
            });
        })
    </script>
@endsection
