<?php


namespace Agp\Modelo\ViewComposer;


use App\Helper\Datatable;
use App\Helper\DatatableJSData;
use Agp\Modelo\Model\Entity\Pais;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PaisComposer
 * Contém os fragmentos de layout relacionados a pais
 * @package ViewComposer
 */
class PaisComposer
{
    /**
     * Retorna as ações da listagem
     *
     * @param mixed $pais Entidade pais ou null caso render == true
     * @param bool $render Se true, retorna o html com wildcards para ser substituido no JS da datatable
     * @return DatatableJSData|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public static function getActions($pais, $render) {
        if ($render) {
            $res = new DatatableJSData();
            $res->data = view('pais.actions', compact('pais'))->render();
            return $res;
        }
        return view('pais.actions',compact('pais'));
    }

    public static function getDatatable($id = 'pais') {
        $datatable = new Datatable($id,false);
        $datatable->addIDColumn();
        //TODO Dados gerados automaticamente. Altere de acordo com os dados da entidade Pais
        PaisComposer::setColumnNomeDatatable($datatable);
        $datatable->addActions('Ações')
            ->set('width',150)
            ->set('textAlign','center');
        $datatable->setAjaxUrl(route('web.pais.datatable'));
        return view('pais.datatable',compact('datatable'));
    }

    private static function setColumnNomeDatatable(&$datatable) {
        $datatable->addColumn('nome','Nome')
            ->set('autoHide',false)
            ->set('sortable','asc')
            ->set('width',400);
    }
}