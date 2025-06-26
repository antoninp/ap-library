<?php
require_once __DIR__ . '/ActionInterface.php';

class SecondAction implements ActionInterface {
    public function execute() {
        // Place your run_second_action logic here.
        return true;
    }
}