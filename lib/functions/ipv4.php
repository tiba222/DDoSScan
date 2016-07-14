<?php

function belongsToSubnet($ip, $cidr) {
    list($subnet, $mask) = explode('/', $cidr);

    if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1) ) == ip2long($subnet)) {
        return true;
    }

    return false;
}
