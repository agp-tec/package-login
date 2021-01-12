<?php


namespace Agp\Modelo\ViewComposer;


use App\Helper\Datatable;
use App\Helper\DatatableJSData;
use Model\Entity\Cidade;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CidadeComposer
 * Contém os fragmentos de layout relacionados a cidade
 * @package ViewComposer
 */
class CidadeComposer
{
    /**
     * Retorna as ações da listagem
     *
     * @param mixed $cidade Entidade cidade ou null caso render == true
     * @param bool $render Se true, retorna o html com wildcards para ser substituido no JS da datatable
     * @return DatatableJSData|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public static function getActions($cidade, $render) {
        if ($render) {
            $res = new DatatableJSData();
            $res->data = view('cidade.actions', compact('cidade'))->render();
            return $res;
        }
        return view('cidade.actions',compact('cidade'));
    }

    public static function getDatatable($id = 'cidade') {
        $datatable = new Datatable($id,false);
        $datatable->addIDColumn();
        //TODO Dados gerados automaticamente. Altere de acordo com os dados da entidade Cidade
        CidadeComposer::setColumnNomeDatatable($datatable);
        $datatable->addActions('Ações')
            ->set('width',150)
            ->set('textAlign','center');
        $datatable->setAjaxUrl(route('web.cidade.datatable'));
        return view('cidade.datatable',compact('datatable'));
    }

    private static function setColumnNomeDatatable(&$datatable) {
        $datatable->addColumn('nome','Nome')
            ->set('autoHide',false)
            ->set('sortable','asc')
            ->set('width',400);
    }
}