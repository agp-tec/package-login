<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:57
 * Controller/Web gerada automaticamente
 *
 */


namespace Agp\Modelo\Controller\Web;


use Agp\Modelo\Controller\Controller;
use Agp\Modelo\Form\CidadeForm;
use Agp\Modelo\Model\Entity\Cidade;
use Facades\Agp\Modelo\Model\Repository\CidadeRepository;
use Facades\Agp\Modelo\Model\Service\CidadeService;
use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\Facades\FormBuilder;


class CidadeController extends Controller
{
    public function index()
    {
        return view('cidade.index');
    }

    public function datatable(Request $request) {
        $request->per_page = $request->pagination['perpage'];
        $request->page = $request->pagination['page'];
        $res = CidadeRepository::datatableData();
        return $res;
    }

    public function create(){
        $cidade = new Cidade();
        $form = $this->getForm($cidade);
        return view('cidade.create', ['form' => $form]);
    }

    public function store(Cidade $cidade)
    {
        $form = $this->getForm($cidade);
        $data = $form->submit();
        $cidade->sync($data);
        CidadeService::store($cidade);
        return redirect()->route('web.cidade.index')->with('success', 'Cidade adicionada.');
    }

    public function edit(Cidade $cidade)
    {
        $form = $this->getForm($cidade);
        return view('cidade.edit', ['form' => $form]);
    }

    public function update(Cidade $cidade)
    {
        $form = $this->getForm($cidade);
        $data = $form->submit();
        $cidade->sync($data);
        CidadeService::update($cidade);
        return redirect()->route('web.cidade.index')->with('info', 'Cidade alterada.');
    }

    public function destroy(Cidade $cidade)
    {
        CidadeService::destroy($cidade);
        return redirect()->route('web.cidade.index')->with('info', 'Cidade removida.');
    }

    public function getForm(Cidade $cidade)
    {
        $form = FormBuilder::create(CidadeForm::class, [
            'method' => $cidade->exists ? 'PUT' : 'POST',
            'url' => $cidade->exists ? route( 'web.cidade.update', ['cidade' => $cidade->id]) : route( 'web.cidade.store'),
            'model' => $cidade
        ]);
        $form->validate($cidade->getRules(),$cidade->getMessages());
        return $form;
    }
}