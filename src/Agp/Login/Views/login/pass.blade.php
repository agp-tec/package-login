<a href="{{ route('web.login.index') }}" class="text-dark-25 text-hover-primary">Não é você?</a>
<form class="form" novalidate="novalidate" method="post"
      action="{{ \Illuminate\Support\Facades\URL::signedRoute('web.login.do',['user' => $user->email]) }}">
    @csrf
    @include('Login::flash-message')
    <input type="hidden" name="email" value="{{ $user->email }}">
    <input type="hidden" id="empresa" name="empresa" value="">
    <div class="form-group">
        <div class="input-group" id="show_hide_password">
            <input class="form-control form-control-solid h-auto py-5 px-6" type="password"
                   placeholder="Senha" id="password" name="password" autocomplete="off" required autofocus
                   min="1"/>
            <button type="button" toggle="#password" class="btn toggle-password">
                <i class="fa fa-fw fa-eye field-icon"></i>
            </button>
        </div>
    </div>
    <div class="form-group d-flex flex-wrap justify-content-between align-items-center">
        <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('web.login.recover',['user' => $user->email]) }}"
           class="text-dark-50 text-hover-primary my-3 mr-2">Esqueci a senha</a>
        <button type="submit" class="btn btn-primary font-weight-bold px-9 py-4 my-3">Entrar</button>
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
