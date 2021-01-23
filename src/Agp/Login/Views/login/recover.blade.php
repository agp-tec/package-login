<div class="login-form login-signin">
    @if ($user && $token)
        <form method="post" action="{{ route('web.login.reset', ['token' => $token, 'email' => $user->email]) }}">
            @csrf
            @method('PUT')
            @include('Login::flash-message')
            <div class="form-group">
                <div class="input-group">
                    <input class="form-control form-control-solid h-auto py-5 px-6 input-password" type="password"
                           placeholder="Senha" name="senha" autocomplete="off" required autofocus
                           min="1"/>
                    <button type="button" toggle=".input-password" class="btn toggle-password">
                        <i class="fa fa-fw fa-eye field-icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <input class="form-control form-control-solid h-auto py-5 px-6 input-password" type="password"
                           placeholder="Senha" name="senha_confirmation" autocomplete="off" required
                           min="1"/>
                    <button type="button" toggle=".input-password" class="btn toggle-password">
                        <i class="fa fa-fw fa-eye field-icon"></i>
                    </button>
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-end align-items-center">
                <button type="submit" class="btn btn-primary font-weight-bold px-9 py-4 my-3">
                    Salvar
                </button>
            </div>
        </form>
    @else
        <div class="alert alert-primary bg-primary-o-20 text-primary" role="alert">
            <h2 class="h1 alert-heading mt-1 mb-2">Ops!</h2>
            <p class="mb-0">Esse token não foi encontrado ou está inválido.</p>
            <p>Você pode solicitar um novo na pagina de login em recuperar senha!</p>
            <hr>
            <div class="d-flex justify-content-between mt-4 ">
                <a class="btn btn-outline-primary" href="{{route('web.login.index')}}">Solicitar novo</a>
                <a class="btn btn-primary" href="{{route('web.login.index')}}">Ir para Login</a>
            </div>
        </div>
    @endif
</div>

@section('scripts')
    @parent
    <script>
        $(document).ready(function () {
            $('.toggle-password').on('click', function () {
                $('.field-icon').toggleClass('fa-eye fa-eye-slash');
                $($(this).attr('toggle')).each(function (input, el) {
                    if ($(el).attr('type') == 'password') {
                        $(el).attr('type', 'text');
                    } else {
                        $(el).attr('type', 'password');
                    }
                });
            });
        })
    </script>
@endsection
