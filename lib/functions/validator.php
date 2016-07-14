<?php
function validateIdentifier($identifier) {
	switch ($identifier) {
		case 'bps' :
			return 'bps';
		case 'pps' :
			return 'pps';
		case 'fps' :
			return 'fps';
		default :
			return 'bps';
	}
}
