<?PHP

	/*
		@Author NoobSaibot

		This is a simple example of a bot that will make a blink show on Poloniex.

		TODO
		 - a lot
	*/

	function poloniex_light_show( $Adapter, $market ) {
		echo "*** " . get_class( $Adapter ) . " Light Show ***\n";

		//_____get the markets to loop over:

		$market_summary = $Adapter->get_market_summary( $market );
		sleep(1);

		//_____get currencies/balances:
		$market = $market_summary['market'];
		$curs_bq = explode( "-", $market );
		$base_cur = $curs_bq[0];
		$quote_cur = $curs_bq[1];
		$base_bal_arr = $Adapter->get_balance( $base_cur, array( 'type' => 'exchange' ) );
		$base_bal = isset( $bal[ $base_cur ] ) ? $bal[ $base_cur ] : $base_bal_arr['available'];
		$quote_bal_arr = $Adapter->get_balance( $quote_cur, array( 'type' => 'exchange' ) );
		$quote_bal = isset( $bal[ $quote_cur ] ) ? $bal[ $quote_cur ] : $quote_bal_arr['available'];

		echo " -> " . get_class( $Adapter ) . " \n";
		echo " -> base currency ($base_cur) \n";
		echo " -> base currency balance ($base_bal) \n";
		echo " -> quote currency ($quote_cur) \n";
		echo " -> quote currency balance ($quote_bal) \n";

		//_____calculate some variables that are rather trivial:
		$precision = $market_summary['price_precision'];							//_____significant digits - example 1: "1.12" has 2 as PP. example 2: "1.23532" has 5 as PP.
		$epsilon = 1 / pow( 10, $precision );										//_____smallest unit of base currency that exchange recognizes: if PP is 3, then it is 0.001.
		$buy_price = $market_summary['bid'];										//_____buy at same price as highest bid.
		$sell_price = $market_summary['ask'];										//_____sell at same price as lowest ask.
		$spread = number_format( $sell_price - $buy_price, $precision, '.', '' );	//_____difference between highest bid and lowest ask.

		echo " -> precision $precision \n";
		echo " -> epsilon $epsilon \n";
		echo " -> buy price: $buy_price \n";
		echo " -> sell price: $sell_price \n";
		echo " -> spread: $spread \n";

		$buy = array( 'message' => null ); 
		$sell = array( 'message' => null ); 


		$buy_price = number_format( $buy_price + $epsilon, $precision, '.', '' );
		$sell_price = number_format( $sell_price - $epsilon, $precision, '.', '' );
		$buy_size = Utilities::get_min_order_size( $market_summary['minimum_order_size_base'], $market_summary['minimum_order_size_quote'], $buy_price, $precision);
		$sell_size = Utilities::get_min_order_size( $market_summary['minimum_order_size_base'], $market_summary['minimum_order_size_quote'], $sell_price, $precision);

		echo " -> final formatted buy price: $buy_price \n";
		echo " -> final formatted sell price: $sell_price \n";
		echo " -> final formatted buy size: $buy_size \n";
		echo " -> final formatted sell size: $sell_size \n";

		if( $spread > 0.00000010 ) {

			//_____Buy & Sell epsilion into the spread:
			if( ! isset( $buy['error'] ) ) {
				echo " -> buying $buy_size of $base_cur for $buy_price $quote_cur costing " . $buy_size * $buy_price . " \n";
				$buy = $Adapter->buy( $market, $buy_size, $buy_price, 'limit', array( 'market_id' => $market_summary['market_id'] ) );
				sleep(1);
				echo "buy:\n";
				print_r( $buy );
			}
			if( ! isset( $sell['error'] ) ) {
				echo " -> selling $sell_size of $base_cur for $sell_price earning " . $sell_size * $sell_price . " \n";
				$sell = $Adapter->sell( $market, $sell_size, $sell_price, 'limit', array( 'market_id' => $market_summary['market_id'] ) );
				sleep(1);
				echo "\nsell:\n";
				print_r( $sell );
			}

		} else {

			//_____Buy & Sell 1% away from the spread:
			if( ! isset( $buy['error'] ) ) {
				echo " -> buying $buy_size of $base_cur for $buy_price $quote_cur costing " . $buy_size * $buy_price . " \n";
				$buy_price = bcmul($sell_price, 1.01, $precision);
				$buy_size = Utilities::get_min_order_size( $market_summary['minimum_order_size_base'], $market_summary['minimum_order_size_quote'], $buy_price, $precision);
				$buy = $Adapter->buy( $market, $buy_size, $buy_price, 'limit', array( 'market_id' => $market_summary['market_id'] ) );
				sleep(1);
				echo "buy:\n";
				print_r( $buy );
			}
			if( ! isset( $sell['error'] ) ) {
				echo " -> selling $sell_size of $base_cur for $sell_price earning " . $sell_size * $sell_price . " \n";
				$sell_price = bcmul($sell_price, 1.01, $precision);
				$sell_size = Utilities::get_min_order_size( $market_summary['minimum_order_size_base'], $market_summary['minimum_order_size_quote'], $sell_price, $precision);
				$sell = $Adapter->sell( $market, $sell_size, $sell_price, 'limit', array( 'market_id' => $market_summary['market_id'] ) );
				sleep(1);
				echo "\nsell:\n";
				print_r( $sell );
			}
		}

		if( isset( $buy['error'] ) || isset( $sell['error'] ) ) {
			if( rand() % 88 < 2 ) {
				$open_orders = $Adapter->get_open_orders( $market );
				usort($open_orders, function($a, $b) {
					return $b['timestamp_created'] - $a['timestamp_created'];
				});
				//delete the last 44 or so orders every 44 or so buy/sell fails:
				foreach( $open_orders as $open_order ) {
					print_r( $open_order );
					if( rand() % 88 < 2 )
						continue;
					print_r( $Adapter->cancel($open_order['id'], array( 'market' => $open_order['market'] ) ) );
					sleep(3);
				}
			}
			return;
		}


		echo "\n";

	}

?>
