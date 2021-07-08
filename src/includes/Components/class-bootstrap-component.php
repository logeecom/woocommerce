<?php

namespace ChannelEngine\Components;

use ChannelEngine\BusinessLogic\BootstrapComponent;
use ChannelEngine\BusinessLogic\Cancellation\Contracts\CancellationService;
use ChannelEngine\BusinessLogic\Notifications\Entities\Notification;
use ChannelEngine\BusinessLogic\Orders\ChannelSupport\OrdersChannelSupportEntity;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigEntity;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrdersConfigurationService;
use ChannelEngine\BusinessLogic\Orders\Configuration\OrderSyncConfig;
use ChannelEngine\BusinessLogic\Orders\Contracts\OrdersService;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\Products\Entities\ProductEvent;
use ChannelEngine\BusinessLogic\Products\Entities\SyncConfig;
use ChannelEngine\BusinessLogic\Shipments\Contracts\ShipmentsService;
use ChannelEngine\BusinessLogic\SupportConsole\Contracts\SupportService;
use ChannelEngine\BusinessLogic\TransactionLog\Entities\Details;
use ChannelEngine\BusinessLogic\TransactionLog\Entities\TransactionLog;
use ChannelEngine\BusinessLogic\Webhooks\Contracts\WebhooksService;
use ChannelEngine\Components\Listeners\Cleanup_Listener;
use ChannelEngine\Components\Listeners\Order_Tick_Event_Listener;
use ChannelEngine\Components\Services\Cancellation_Service;
use ChannelEngine\Components\Services\Configuration_Service;
use ChannelEngine\Components\Services\Logger_Service;
use ChannelEngine\Components\Services\Order_Config_Service;
use ChannelEngine\Components\Services\Orders_Service;
use ChannelEngine\Components\Services\Plugin_Status_Service;
use ChannelEngine\Components\Services\Products_Service;
use ChannelEngine\Components\Services\Shipments_Service;
use ChannelEngine\Components\Services\State_Service;
use ChannelEngine\Components\Services\Support_Service;
use ChannelEngine\Components\Services\Webhooks_Service;
use ChannelEngine\Components\StateTransition\Order_State_Transition_Listener;
use ChannelEngine\Components\StateTransition\Product_State_Transition_Listener;
use ChannelEngine\Infrastructure\Configuration\ConfigEntity;
use ChannelEngine\Infrastructure\Configuration\Configuration;
use ChannelEngine\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use ChannelEngine\Infrastructure\Logger\LogData;
use ChannelEngine\Infrastructure\ORM\Exceptions\RepositoryClassException;
use ChannelEngine\Infrastructure\ORM\RepositoryRegistry;
use ChannelEngine\Infrastructure\Serializer\Concrete\JsonSerializer;
use ChannelEngine\Infrastructure\Serializer\Serializer;
use ChannelEngine\Infrastructure\ServiceRegister;
use ChannelEngine\Infrastructure\TaskExecution\Events\QueueStatusChangedEvent;
use ChannelEngine\Infrastructure\TaskExecution\Process;
use ChannelEngine\Infrastructure\TaskExecution\QueueItem;
use ChannelEngine\Infrastructure\TaskExecution\TaskEvents\TickEvent;
use ChannelEngine\Infrastructure\Utility\Events\EventBus;
use ChannelEngine\Repositories\Base_Repository;
use ChannelEngine\Repositories\Log_Repository;
use ChannelEngine\Repositories\Product_Event_Repository;
use ChannelEngine\Repositories\Queue_Repository;

/**
 * Class Bootstrap_Component
 *
 * @package ChannelEngine\Components
 */
class Bootstrap_Component extends BootstrapComponent {
	private static $is_init = false;

	public static function init() {
		if ( static::$is_init ) {
			return;
		}

		parent::init();

		static::$is_init = true;
	}

	protected static function initServices() {
		parent::initServices();

		ServiceRegister::registerService(
			ShopLoggerAdapter::CLASS_NAME,
			static function () {
				return Logger_Service::getInstance();
			}
		);

		ServiceRegister::registerService(
			Configuration::CLASS_NAME,
			static function () {
				return Configuration_Service::getInstance();
			}
		);

		ServiceRegister::registerService(
			OrdersService::CLASS_NAME,
			static function () {
				return new Orders_Service();
			}
		);

		ServiceRegister::registerService(
			Serializer::CLASS_NAME,
			static function () {
				return new JsonSerializer();
			}
		);

		ServiceRegister::registerService(
			ProductsService::class,
			static function () {
				return new Products_Service();
			}
		);

		ServiceRegister::registerService(
			Plugin_Status_Service::class,
			static function () {
				return new Plugin_Status_Service();
			}
		);

		ServiceRegister::registerService(
			State_Service::class,
			static function () {
				return new State_Service();
			}
		);

		ServiceRegister::registerService(
			OrdersConfigurationService::class,
			static function () {
				return new Order_Config_Service();
			}
		);

		ServiceRegister::registerService(
			CancellationService::class,
			static function () {
				return new Cancellation_Service();
			}
		);

		ServiceRegister::registerService(
			ShipmentsService::class,
			static function () {
				return new Shipments_Service();
			}
		);

		ServiceRegister::registerService(
			SupportService::class,
			static function () {
				return new Support_Service();
			}
		);

		ServiceRegister::registerService(
			WebhooksService::class,
			static function () {
				return new Webhooks_Service();
			}
		);
	}

	/**
	 * @throws RepositoryClassException
	 */
	protected static function initRepositories() {
		parent::initRepositories();

		RepositoryRegistry::registerRepository( LogData::CLASS_NAME, Base_Repository::getClassName() );
		RepositoryRegistry::registerRepository( ConfigEntity::CLASS_NAME, Base_Repository::getClassName() );
		RepositoryRegistry::registerRepository( QueueItem::getClassName(), Queue_Repository::class );
		RepositoryRegistry::registerRepository( Process::getClassName(), Base_Repository::getClassName() );
		RepositoryRegistry::registerRepository( OrdersChannelSupportEntity::getClassName(), Base_Repository::getClassName() );
		RepositoryRegistry::registerRepository( ProductEvent::getClassName(), Product_Event_Repository::getClassName() );
		RepositoryRegistry::registerRepository( OrdersConfigEntity::getClassName(), Base_Repository::getClassName() );
		RepositoryRegistry::registerRepository( SyncConfig::getClassName(), Base_Repository::getClassName() );
		RepositoryRegistry::registerRepository( OrderSyncConfig::getClassName(), Base_Repository::getClassName() );
		RepositoryRegistry::registerRepository( Details::getClassName(), Log_Repository::getClassName() );
		RepositoryRegistry::registerRepository( TransactionLog::getClassName(), Log_Repository::getClassName() );
		RepositoryRegistry::registerRepository( Notification::getClassName(), Log_Repository::getClassName() );
	}

	protected static function initEvents() {
		parent::initEvents();

		EventBus::getInstance()->when(
			QueueStatusChangedEvent::class,
			[ Product_State_Transition_Listener::class, 'handle' ]
		);

		EventBus::getInstance()->when(
			QueueStatusChangedEvent::class,
			[ Order_State_Transition_Listener::class, 'handle' ]
		);


		EventBus::getInstance()->when(
			TickEvent::CLASS_NAME,
			[ new Order_Tick_Event_Listener(), 'handle' ]
		);

		EventBus::getInstance()->when(
			TickEvent::CLASS_NAME,
			Cleanup_Listener::class . "::handle"
		);
	}
}