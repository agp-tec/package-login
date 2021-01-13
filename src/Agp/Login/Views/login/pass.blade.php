<form class="form" novalidate="novalidate" method="post"
      action="{{ \Illuminate\Support\Facades\URL::signedRoute('web.login.do',['user' => $user->email]) }}">
    @csrf
    <input type="hidden" name="email" value="{{ $user->email }}">
    <div class="card bg-primary-o-30 card-custom card-stretch gutter-b">
        <div class="card-body pt-2">
            <div class="d-flex flex-wrap align-items-center mt-5">
                <div class="symbol symbol-60 symbol-2by3 flex-shrink-0 mr-4">
                    <img class="rounded-circle" src="{{ Helper::getAvatarUrl($user->email, 60) }}" alt="">
                </div>
                <div class="d-flex flex-column flex-grow-1 my-lg-0 my-2 mr-2">
                    <a href="javascript:;"
                       class="text-dark-75 font-weight-bold text-hover-primary font-size-lg mb-1">{{ $user->nome }} {{ $user->sobrenome }}</a>
                    <span class="text-muted font-weight-bold">{{ $user->email }}</span>
                </div>
                <a href="{{ route('web.login.index') }}" class="text-dark-50 text-hover-primary text-right">Não é
                    você?</a>
            </div>
        </div>
    </div>
    @if(isset($errors) && $errors->any())
        <div
            class="alert alert-custom @if(Route::current()->getName() == 'login') alert-outline-2x alert-outline-danger @else alert-light-danger @endif fade show mb-5">
            <div class="alert-icon">
                <i class="flaticon-warning"></i>
            </div>
            <ul class="alert-text mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <div class="alert-close">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">
                        <i class="ki ki-close"></i>
                    </span>
                </button>
            </div>
        </div>
    @endif
    <input type="hidden" id="empresa" name="empresa" value="">
    <div class="form-group">
        <div class="input-group" id="show_hide_password">
            <input class="form-control form-control-solid h-auto py-5 px-6" type="password"
                   placeholder="Senha" id="password" name="password" autocomplete="off" required
                   min="1"/>
            <button type="button" toggle="#password" class="btn toggle-password">
                <i class="fa fa-fw fa-eye field-icon"></i>
            </button>
        </div>
    </div>
    <div class="form-group d-flex flex-wrap justify-content-between align-items-center">
        <a href="javascript:;" class="text-dark-50 text-hover-primary my-3 mr-2">Esqueci a senha</a>
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
