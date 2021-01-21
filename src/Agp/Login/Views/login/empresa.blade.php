@include('Login::flash-message')
<div class="col-12">
    <ul class="list-group">
        @foreach ($empresas as $empresa)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{$empresa->nome}}
                <span class="d-flex">
                    <form method="post"
                          action="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('web.login.empresa', now()->addMinutes(15), ['user' => auth()->user()->getKey()])}}">
                        @csrf
                        @method('POST')
                        <input type="hidden" name="empresa" value="{{ $empresa->id }}">
                        <input type="hidden" name="nome" value="{{ $empresa->nome }}">
                        <button
                            class="btn btn-sm btn-icon btn-primary">{{ Metronic::getSVG('media/svg/icons/Navigation/Right-2.svg') }}</button>
                    </form>
                </span>
            </li>
        @endforeach
    </ul>
</div>
