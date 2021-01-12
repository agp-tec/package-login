<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:57
 * Model/Repository gerada automaticamente
 *
 */


namespace Agp\Modelo\Model\Repository;


use App\Exception\CustomUnauthorizedException;
use App\Helper\Datatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Model\Entity\Cidade;


class CidadeRepository  extends BaseRepository
{
    protected $className = Cidade::class;

    public function __construct() {
        $this->hasAdmEmpresa = false;
    }

    public function getList()
    {
        return Cidade::query()->get();
    }

    public function procuraGenerica($expressao)
    {
        return Cidade::query()
            
            ->where(function ($query) use ($expressao) {
                $query
                    ->orWhere('id','=',$expressao)
                    //TODO Dados gerados automaticamente. Altere de acordo com os dados da entidade Cidade
                    ->orWhere('nome','like','%'.$expressao.'%');
            })
            ->get();
    }
}