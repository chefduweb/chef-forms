<?php
namespace ChefForms\Builders\Fields;

class HiddenField extends DefaultField{


    /**
     * Method to override to define the input type
     * that handles the value.
     *
     * @return void
     */
    protected function fieldType(){
        $this->type = 'hidden';
    }



}