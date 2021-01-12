<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:57
 * Form gerada automaticamente
 *
 */


namespace Agp\Modelo\Form;


use Kris\LaravelFormBuilder\Form;


class CidadeForm extends BaseForm
{
    public function buildForm()
    {
        $this
            //TODO Dados gerados automaticamente. Altere de acordo com os dados da entidade Cidade
              ->add('nome', 'text', [
                  'label' => 'Nome',
              ])
              ->add('uf', 'text', [
                  'label' => 'Uf',
              ])
              ->add('id_uf', 'number', [
                  'label' => 'Id Uf',
              ])
              ->add('sigla_pais', 'text', [
                  'label' => 'Sigla Pais',
              ])
              ->add('pais', 'entity', [
                  'label' => 'Pais',
                  'class' => 'Model\Entity\Pais',
                  'property' => 'nome', //Trocar pelo campo a ser mostrado no formulario
              ])
            ->add('submit', 'submit', ['label' => 'Salvar']);
    }
}