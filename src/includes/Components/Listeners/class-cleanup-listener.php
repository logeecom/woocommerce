<?php

namespace ChannelEngine\Components\Listeners;

use ChannelEngine\BusinessLogic\Utility\Listeners\SystemCleanupListener;
use ChannelEngine\Components\Services\Plugin_Status_Service;
use ChannelEngine\Components\Services\State_Service;

class Cleanup_Listener extends SystemCleanupListener {
	protected static function canHandle() {
		$state_service         = new State_Service();
		$plugin_status_service = new Plugin_Status_Service();

		return $state_service->get_current_state() === 'dashboard' && $plugin_status_service->is_enabled();
	}
}
