<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:59
 * Form gerada automaticamente
 *
 */


namespace Agp\Modelo\Form;


use Kris\LaravelFormBuilder\Form;


class PaisForm extends BaseForm
{
    public function buildForm()
    {
        $this
            //TODO Dados gerados automaticamente. Altere de acordo com os dados da entidade Pais
              ->add('exonimo_portugues', 'text', [
                  'label' => 'Exonimo Portugues',
              ])
              ->add('exonimo_ingles', 'text', [
                  'label' => 'Exonimo Ingles',
              ])
              ->add('endonimo', 'text', [
                  'label' => 'Endonimo',
              ])
              ->add('imagem', 'text', [
                  'label' => 'Imagem',
              ])
            ->add('submit', 'submit', ['label' => 'Salvar']);
    }
}