<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require __DIR__ . '/Lib/class-resource-copier.php';
\ChannelEngine\Lib\Resource_Copier::copy();
