<?PHP

	function run_tests( $Adapters, $Tester )
	{
		/*$exchanges = [];
		$currencies = [];
		$markets = [];
		$market_summaries = [];
		$balances = [];
		$open_orders = [];
		$completed_orders = [];
		$deposit_addresses = [];
		$trades = [];
		$orderbooks = [];
		$volumes = [];
		$worths = [];*/

		foreach( $Adapters as $Adapter ) {
			echo "******* " . get_class( $Adapter ) . " ******\n";
			array_push( $exchanges, $exchange_name );
			
			echo " -> getting currencies\n";
			$Tester->test( 'currencies', $Adapter->get_currencies() );

			echo " -> getting markets\n";
			$Tester->test( 'markets', $Adapter->get_markets() );

			echo " -> getting market summaries\n";
			$Tester->test( 'market_summaries', $Adapter->get_market_summaries() );

			echo " -> getting balances\n";
			$Tester->test( 'balances', $Adapter->get_balances() );

			echo " -> generating deposit addresses\n";
			$Tester->test( 'deposit_addresses', $Adapter->deposit_addresses() );
			
			echo " -> getting open orders\n";
			foreach( $Adapter->get_markets() as $market )
				$Tester->test( 'open_orders', $Adapter->get_open_orders( $market ) );

			echo " -> getting completed orders\n";
			foreach( $Adapter->get_markets() as $market )
				$Tester->test( 'completed_orders', $Adapter->get_completed_orders( $market ) );

			echo " -> cancelling all orders\n";
			$Tester->test( 'cancel_all', $Adapter->cancel_all() );

			echo " -> getting all recent trades\n";
			$Tester->test( 'trades', $Adapter->get_all_trades( $time = 0 ) );

			echo " -> getting some depth of orderbook\n";
			$Tester->test( 'orderbooks', $Adapter->get_orderbooks( $depth = 20 ) );

			//_____Utilities: they do not directly access native API libraries where as Adapters must access native API or self

			echo " -> getting volumes\n";
			$Tester->test( 'volumes', Utilities::get_total_volumes( $Adapter->get_market_summaries() ) );

			echo " -> getting worths\n";
			$Tester->test( 'worths', Utilities::get_worth( $Adapter->get_balances(), $Adapter->get_market_summaries() ) );

		}
	}

?>