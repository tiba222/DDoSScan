<?php

class ActionExlusion {

    public $id;
    public $target;
    public $excluded_action;

    public function __construct($id, $target, $excluded_action) {
        $this->id = $id;
        $this->target = $target;
        $this->excluded_action = $excluded_action;
    }

}
