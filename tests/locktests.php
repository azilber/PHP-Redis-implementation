<?php

$DEBUG=false;

require dirname ( __FILE__ ) .'/redis.php';

define ( 'NL', ( PHP_SAPI == 'cli' ? "\n" : '<br />' ) );

$redis_uptime_min_seconds=300;

$redis = new redis_cli ( '127.0.0.1', 6379 );

$redis_token=uniqid();
$redis_ttl=5;

echo "redis_token=$redis_token\n";

function unlock($resource, $token)
    {
	return $resource -> cmd ( 'DEL', "$token" )->get();
    }


function checksetlock($resource, $token, $ttl=300)
	{
	   $red_ret=0; $result="";

		$red_ret = $resource -> cmd ( 'EXISTS', "$token" )->get();
		if ( $red_ret == 0 )
		   {
			$result=$resource -> cmd ( 'SETEX', "$token", $ttl, 1 ) -> set();
			if($result[0]!="OK") return 2;
		   } else {
			return 1;
		   }
	   return 0;
	}



/*  Testing Code below. */

$x=0;
$y=12;

for ($x=0; $x<=$y; $x++){

  $result=checksetlock($redis,$redis_token,$redis_ttl);

	if($result == 1 ){
		echo "Lock exists!\n";
	} elseif ($result == 0 ) {
		echo "Lock set!\n";
	} elseif ($result == 2) {
		echo "result=$result\n.... problem\n";
	}
if ($DEBUG){ echo $result . "\n"; }
	sleep(2);

}

$ret=unlock($redis,$redis_token);
if($DEBUG){ echo "ret=$ret\n"; }

if($ret == 1){
	echo "Lock removed.\n";
} else {
	echo "Lock must have expired, did not remove.\n";
}

