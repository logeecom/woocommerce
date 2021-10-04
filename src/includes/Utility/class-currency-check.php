<?php

namespace ChannelEngine\Utility;

use ChannelEngine\BusinessLogic\API\Authorization\Http\Proxy;
use ChannelEngine\Infrastructure\ServiceRegister;

class Currency_Check {
	public static function match( $currency ) {
		/** @var Proxy $authProxy */
		$authProxy   = ServiceRegister::getService( Proxy::class );
		$accountInfo = $authProxy->getAccountInfo();

		return strtolower( $currency ) === strtolower( $accountInfo->getCurrencyCode() );
	}
}
