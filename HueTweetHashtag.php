<?php


//Set the IP of your Hue Bridge
$gatewayIP = "192.168.1.2";

//Set the app secret:
$app_secret = "34fcc05c38c1a66f9cc1a34394809e7";


//Set the tweet threshold for changing the lamp color to blue:
$max_tweets = 5;

//Set time interval for checking the Twitter hashtags
$sleeptime = 10;


$url = 'http://'.$gatewayIP.'/api/'.$app_secret.'/lights/2/state';

$temp = "null";
$counter = 0;



function updateLamp($data){
	global $url;	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data))
        );

        $result = curl_exec($ch);
        return $result;
}


function getTweets($hash_tag) {
    global $temp;
    global $counter;

    $url = 'http://search.twitter.com/search.atom?q='.urlencode($hash_tag) ;
    //echo "<p>Connecting to <strong>$url</strong> ...</p>";
    $ch = curl_init($url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $xml = curl_exec ($ch);
    curl_close ($ch);

    //If you want to see the response from Twitter, uncomment this next part out:
    //echo "<p>Response:</p>";
    //echo "<pre>".htmlspecialchars($xml)."</pre>";


    $twelement = new SimpleXMLElement($xml);
    foreach ($twelement->entry as $entry) {
        $text = trim($entry->title);
        $author = trim($entry->author->name);
        //$time = strtotime($entry->published);
        $id = $entry->id;
        //echo "<p>Tweet from ".$author.": <strong>".$text."</strong>  <em>Posted ".date('n/j/y g:i a',$time)."</em></p>";
	
	$time = trim($entry->published);
	break;
    }

   // echo $time.":".$temp."---".strlen($time).":".strlen($temp)."\n";
    if ( strcmp ( $time, $temp) != 0  ){
                $temp = $time;
                $counter = $counter + 1;
                echo "".$temp."\n";
		echo $counter."\n";
		return true;
    }

    else
    	return false ;
}


//Startup sequnce, turn on and off the lamp:
updateLamp("{\"hue\": 25000, \"sat\": 1, \"on\":true}");
sleep(1);
updateLamp("{\"on\":false}");


//Listen for the hashtag and blink the lamp
//When tweets exeed the threshold change the lamp color
while(true) {
	 
	if(getTweets('#iot')==true) {
		updateLamp("{\"alert\":\"select\", \"hue\": 50000, \"sat\": 254}");
	}

	if($counter>max_tweets) {
                updateLamp("{\"hue\": 42588, \"sat\": 254, \"on\":true}");
		break;
        }
	
	sleep($sleeptime);
}

?>
