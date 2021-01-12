<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:57
 * Model/Resource gerada automaticamente
 *
 */


namespace Agp\Modelo\Model\Resource;


use Illuminate\Http\Resources\Json\JsonResource;


class CidadeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
          'id' => $this->id,
          'nome' => $this->nome,
          'uf' => $this->uf,
          'id_uf' => $this->id_uf,
          'sigla_pais' => $this->sigla_pais,
          'adm_pais_id' => $this->adm_pais_id,
          'pais' => new PaisResource($this->pais),
        ];
    }
}