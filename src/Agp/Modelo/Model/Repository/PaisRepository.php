<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:59
 * Model/Repository gerada automaticamente
 *
 */


namespace Agp\Modelo\Model\Repository;


use App\Exception\CustomUnauthorizedException;
use App\Helper\Datatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Agp\Modelo\Model\Entity\Pais;


class PaisRepository  extends BaseRepository
{
    protected $className = Pais::class;

    public function __construct() {
        $this->hasAdmEmpresa = false;
    }

    public function getList()
    {
        return Pais::query()->get();
    }

    public function procuraGenerica($expressao)
    {
        return Pais::query()
            
            ->where(function ($query) use ($expressao) {
                $query
                    ->orWhere('id','=',$expressao)
                    //TODO Dados gerados automaticamente. Altere de acordo com os dados da entidade Pais
                    ->orWhere('nome','like','%'.$expressao.'%');
            })
            ->get();
    }
}