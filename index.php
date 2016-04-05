<!DOCTYPE html>
<html>
	<!-- Created by Tyler Thompson on 6/9/15-->
	<?php
		//service arrays
		$baldwin_times = [
			'service_name' => 'Baldwin',
			'sun' => '11:30-13:15 17:00-18:00',
			'mon_thru_thurs' => '7:00-10:00 11:00-14:00 17:00-18:30 22:30-23:30',
			'fri' => '7:00-10:00 11:00-14:00 17:00-18:30',
			'sat' => '11:30-13:30 17:00-18:00',
		];
		$wildcat_times = [
			'service_name' => 'Wildcat',
			'sun' => '18:00-22:00',
			'mon_thru_thurs' => '8:30-21:00',
			'fri' => '8:30-21:00',
			'sat' => '12:30-17:00',
		];
		$marios_and_wings_times = [
			'service_name' => 'Mario\'s/Wilbur\'s Wild Wings',
			'sun' => '18:00-24:00',
			'mon_thru_thurs' => '10:00-24:00',
			'fri' => '10:00-24:00',
			'sat' => '18:00-24:00',
		];
		$cstore_times = [
			'service_name' => 'The C-Store',
			'sun' => '19:00-24:00',
			'mon_thru_thurs' => '10:00-24:00',
			'fri' => '10:00-24:00',
			'sat' => '14:00-24:00',
		];

		//all services to loop through
		$services = array($baldwin_times, $wildcat_times, $marios_and_wings_times, $cstore_times);

		//isItOpen
		//@param $times_array A service array, such as $baldwin_times
		//@returns a string with information about whether a service is OPEN, OPENING SOON, CLOSING SOON, or CLOSED. Also includes a relevant timestamp.
		function isItOpen($times_array){
			if (date('N') == 7) //if day is Sunday
			{
				$times_array = $times_array['sun'];
			}
			else if (date('N') == 6) //if day is Saturday
			{
				$times_array = $times_array['sat'];
			}
			else if (date('N') == 5) //if day is Friday
			{
				$times_array = $times_array['fri'];
			}
			else //day must be Mon-Thurs
			{
				$times_array = $times_array['mon_thru_thurs'];
			}

			$next_open = ''; //stores next open time in case of closure
			$times_ranges = explode(" ", $times_array); //parses individual time ranges (e.g. breakfast, lunch, and dinner)
			$am_or_pm = 'AM';
			for ($i = 0; $i < sizeof($times_ranges); $i++)
			{
				$pieces = explode("-", $times_ranges[$i]); //parses open and closing times

				$open_hour = (int)(explode(":", $pieces[0])[0]);
				$open_min = (int)(explode(":", $pieces[0])[1]);
				$close_hour = (int)(explode(":", $pieces[1])[0]);
				$close_min = (int)(explode(":", $pieces[1])[1]);

				$curr_hour = (int)date('H');
				$curr_min = (int)date('i');

				if ($i == 0)
				{
					if ((($curr_hour*60+$curr_min)-($open_hour*60+$open_min))<0)
					{
						if ($open_hour > 12) {
							$am_or_pm = 'PM';
							if ($open_hour == 12 && $am_or_pm == 'PM') { $am_or_pm = 'AM'; }
							$next_open = ($open_hour-12).":".str_pad($open_min,2,'0',STR_PAD_LEFT).$am_or_pm;
						} else if ($open_hour < 12)
						{
							$am_or_pm = 'AM';
							$next_open = ($open_hour).":".str_pad($open_min,2,'0',STR_PAD_LEFT).$am_or_pm;
						} else
						{
							$am_or_pm = 'PM';
							$next_open = ($open_hour).":".str_pad($open_min,2,'0',STR_PAD_LEFT).$am_or_pm;
						}
					}
				}
				if (($i !== sizeof($times_ranges)-1)&&($next_open == ''))
				{
					$next_pieces = explode("-", $times_ranges[$i+1]);
					$next_open_hour = (int)(explode(":", $next_pieces[0])[0]);
					$next_open_min = (int)(explode(":", $next_pieces[0])[1]);

					if ((($curr_hour*60)+$curr_min)-(($next_open_hour*60)+$next_open_min) < 0)
					{
						//close time is in future
						if ($next_open_hour >= 12) { $next_open_hour -= 12; $am_or_pm = "PM"; } //since times are stored in military time, if hour > 12 subtract 12 from hours before returning when it closes
						if ($next_open_hour == 12 && $am_or_pm == 'PM') { $am_or_pm = 'AM'; }
						if ($next_open_hour == 0) { $next_open_hour = 12; }
						$next_open = $next_open_hour.":".str_pad($next_open_min, 2, '0', STR_PAD_LEFT).$am_or_pm;
					}
				}

				if (($curr_hour > $open_hour && $curr_hour < $close_hour)||($curr_hour == $open_hour && $curr_min >= $open_min)||($curr_hour == $close_hour && $curr_min < $close_min)) {
					if (($close_hour*60+$close_min)-($curr_hour*60+$curr_min) <= 15)
					{
						if ($close_hour >= 12) { $close_hour -= 12; $am_or_pm="PM"; } //since times are stored in military time, if hour > 12 subtract 12 from hours before returning when it closes
						if ($close_hour == 12 && $am_or_pm == 'PM') { $am_or_pm = 'AM'; }
						if ($close_hour == 0) { $close_hour = 12; }
						return 'OPEN but CLOSES SOON at '.$close_hour.":".str_pad($close_min, 2, '0', STR_PAD_LEFT).$am_or_pm; //closes within 15 min
					}
					if ($close_hour >= 12) { $close_hour -= 12; $am_or_pm="PM";} //since times are stored in military time, if hour > 12 subtract 12 from hours before returning when it closes
					if ($close_hour == 12 && $am_or_pm == 'PM') { $am_or_pm = 'AM'; }
					if ($close_hour == 0) { $close_hour = 12; }
					return 'OPEN until '.$close_hour.':'.str_pad($close_min, 2, '0', STR_PAD_LEFT).$am_or_pm; //it's open! :)
				}

				if ((($open_hour*60)+$open_min)-(($curr_hour*60)+$curr_min) <= 15 && (($open_hour*60)+$open_min)-(($curr_hour*60)+$curr_min) >= 0)
				{
					if ($open_hour >= 12) { $open_hour -= 12; $am_or_pm="PM";} //since times are stored in military time, if hour > 12 subtract 12 from hours before returning when it closes
					if ($open_hour == 12 && $am_or_pm == 'PM') { $am_or_pm = 'AM'; }
					if ($open_hour == 0) { $open_hour = 12; }
					return 'OPENING SOON at '.$open_hour.':'.str_pad($open_min, 2, '0', STR_PAD_LEFT).$am_or_pm; //opens within 15 min
				}
			}
			if ($next_open === '')
			{
				return 'CLOSED for the day!'; //no open times found-- it's closed :(
			}
			else
			{
				return 'CLOSED until '.$next_open;
			}
		}
	?>
	<head>
		<title>IWU Is It Open</title>
		<link rel="stylesheet" type="text/css" href="css/styles.css">
		<script src="scripts/jquery-1.11.3.min.js"></script>
		<script src="scripts/bookmark.js"></script>
	</head>
	<body>
		<div id="global_wrapper">
			<div id="heading">
				<p>IWU Is It Open</p>
			</div>
			<?php
				for ($i = 0; $i < sizeof($services); $i++)
				{
					$service_array = $services[$i];
					$openState = isItOpen($service_array);
					$businessTitle = $service_array['service_name'];
					if (strpos($openState, 'OPEN until') !== FALSE)
					{
						echo "<div class='open'><p><b>".$businessTitle."</b> is ".$openState."</p></div>";
					}
					else if (strpos($openState, 'CLOSED') !== FALSE)
					{
						echo "<div class='closed'><p><b>".$businessTitle."</b> is ".$openState."</p></div>";
					}
					else if (strpos($openState, 'OPENING SOON') !== FALSE)
					{
						echo "<div class='opening_soon'><p><b>".$businessTitle."</b> is ".$openState."</p></div>";
					}
					else
					{
						echo "<div class='closing_soon'><p><b>".$businessTitle."</b> is ".$openState."</p></div>";
					}
				}
			?>
			</br>
			<div id="foot">
				<a id="bookmark_button" href="#" rel="sidebar" title="Bookmark IWU Is It Open">Bookmark This Page!</a>
				<p>IWU Is It Open</br>Open/closed status based on normal food services hours during the school year.</p>
			</div>
		</div> <!-- global wrapper -->
	</body>
</html>