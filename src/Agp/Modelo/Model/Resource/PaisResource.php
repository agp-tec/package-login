<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:59
 * Model/Resource gerada automaticamente
 *
 */


namespace Agp\Modelo\Model\Resource;


use Illuminate\Http\Resources\Json\JsonResource;


class PaisResource extends JsonResource
{
    public function toArray($request)
    {
        return [
          'id' => $this->id,
          'exonimo_portugues' => $this->exonimo_portugues,
          'exonimo_ingles' => $this->exonimo_ingles,
          'endonimo' => $this->endonimo,
          'imagem' => $this->imagem,
        ];
    }
}