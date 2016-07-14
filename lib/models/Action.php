<?php

class Action {

    public $id;
    public $description;
    public $action;
    public $action_parameters;
    public $once;

    public function __construct($id, $description, $action, $action_parameters, $once) {
        $this->id = $id;
        $this->description = $description;
        $this->action = $action;
        $this->action_parameters = $action_parameters;
        $this->once = $once;
    }

}
