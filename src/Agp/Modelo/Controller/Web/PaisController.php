<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:59
 * Controller/Web gerada automaticamente
 *
 */


namespace Agp\Modelo\Controller\Web;


use Agp\Modelo\Controller\Controller;
use Agp\Modelo\Form\PaisForm;
use Agp\Modelo\Model\Entity\Pais;
use Facades\Agp\Modelo\Model\Repository\PaisRepository;
use Facades\Agp\Modelo\Model\Service\PaisService;
use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\Facades\FormBuilder;


class PaisController extends Controller
{
    public function index()
    {
        return view('pais.index');
    }

    public function datatable(Request $request) {
        $request->per_page = $request->pagination['perpage'];
        $request->page = $request->pagination['page'];
        $res = PaisRepository::datatableData();
        return $res;
    }

    public function create(){
        $pais = new Pais();
        $form = $this->getForm($pais);
        return view('pais.create', ['form' => $form]);
    }

    public function store(Pais $pais)
    {
        $form = $this->getForm($pais);
        $data = $form->submit();
        $pais->sync($data);
        PaisService::store($pais);
        return redirect()->route('web.pais.index')->with('success', 'Pais adicionada.');
    }

    public function edit(Pais $pais)
    {
        $form = $this->getForm($pais);
        return view('pais.edit', ['form' => $form]);
    }

    public function update(Pais $pais)
    {
        $form = $this->getForm($pais);
        $data = $form->submit();
        $pais->sync($data);
        PaisService::update($pais);
        return redirect()->route('web.pais.index')->with('info', 'Pais alterada.');
    }

    public function destroy(Pais $pais)
    {
        PaisService::destroy($pais);
        return redirect()->route('web.pais.index')->with('info', 'Pais removida.');
    }

    public function getForm(Pais $pais)
    {
        $form = FormBuilder::create(PaisForm::class, [
            'method' => $pais->exists ? 'PUT' : 'POST',
            'url' => $pais->exists ? route( 'web.pais.update', ['pais' => $pais->id]) : route( 'web.pais.store'),
            'model' => $pais
        ]);
        $form->validate($pais->getRules(),$pais->getMessages());
        return $form;
    }
}