<div class="row d-flex">
    <div class="col ml-3">
        <a href="{{route("web.cidade.edit", ["cidade" => isset($cidade)?$cidade->id:"___rowid"])}}" class="btn btn-icon btn-light btn-hover-primary btn-sm">
    <span class="svg-icon svg-icon-md svg-icon-primary">
        {{ Metronic::getSVG('media/svg/icons/Communication/Write.svg') }}
    </span>
        </a>
    </div>
    <div class="col ml-3">
        <form method='post' action='{{ route("web.cidade.destroy", ["cidade" => isset($cidade)?$cidade->id:'___rowid']) }}' onsubmit='return confirm("Tem certeza que deseja remover?")'>
            @csrf
            @method('DELETE')
            <button class="btn btn-icon btn-light btn-hover-primary btn-sm">
        <span class="svg-icon svg-icon-md svg-icon-primary">
            {{ Metronic::getSVG('media/svg/icons/General/Trash.svg') }}
        </span>
            </button>
        </form>
    </div>
</div>