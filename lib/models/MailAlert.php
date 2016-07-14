<?php

class MailAlert{
    public $id;
    public $target;
    public $email;
    
    public function __construct($id, $target, $email) {
        $this->id = $id;
        $this->target = $target;
        $this->email = $email;
    }
}

