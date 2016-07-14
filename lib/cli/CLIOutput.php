<?php

class CLIOutput {

    public function __construct() {
        
    }

    public function CLIPrint($headers, $data, $display = 'column') {
        if (isset($display)) {
            switch ($display) {
                case 'json':
                    $this->_printJson($data);
                    return;
                    
                case 'column':
                    $this->_printColumn($headers, $data);
                    return;
                    
                case 'object':
                    $this->_printObject($headers, $data);
                    return;

                default:
                    echo("Unknown display style '$display'\n");
                    return;
            }
        }
    }

    protected function _printJson($data) {
        echo(json_encode($data));
    }

    protected function _printColumn($headers, $data) {
        $array = $this->_buildOutputArray($headers, $data);
        $len = $this->_getColumnSizes($array);

        for ($i = 0; $i < sizeof($array[0]); $i++) {
            for ($p = 0; $p < sizeof($array); $p++) {
                echo(str_pad($array[$p][$i], $len[$p]));
            }
            echo("\n");
        }
    }
    
    protected function _printObject($headers, $data){
        for($i=0; $i < sizeof($headers); $i++){
        echo(str_pad("$headers[$i]:", 25) . $data[$i] . "\n");
        }
    }

    protected function _buildOutputArray($headers, $data) {
        $o = array();

        for ($i = 0; $i < sizeof($headers); $i++) {
            $o[$i][0] = $headers[$i];
        }

        for ($i = 1; $i <= sizeof($data); $i++) {
            $pos = 0;
            foreach ($data[($i - 1)] as $d) {
                $o[$pos][$i] = $d;
                $pos++;
            }
        }

        return $o;
    }

    protected function _getColumnSizes($array) {
        $sizes = array();

        for ($i = 0; $i < sizeof($array); $i++) {
            $longest = 0;
            foreach ($array[$i] as $value) {
                if (strlen($value) > $longest) {
                    $longest = strlen($value);
                }
            }
            $sizes[$i] = ($longest + 5);
        }

        return $sizes;
    }

}
