<?php
/**
 * @author: Patsura Dmitry <zaets28rus@gmail.com>
 */

include_once __DIR__ . '/../vendor/autoload.php';

use \Michelf\Markdown;

$articles = json_decode(file_get_contents(__DIR__ . '/articles.json'));

function getRenderedHTML($value) {
    //Build object to send
    $sendObj=new stdClass();
    $sendObj->text=$value;
    $sendObj->mode='markdown';
    $content=json_encode($sendObj);

    //Build headers
    $headers=array("Content-type: application/json", "User-Agent: curl");

    //Build curl request to github's api
    $curl=curl_init('https://api.github.com/markdown');
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);


    //Send request and verify response
    $response=curl_exec($curl);
    $status=curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if($status!=200) {
        user_error("Error: Call to api.github.com failed with status $status, response $response, curl_error ".curl_error($curl).", curl_errno ".curl_errno($curl), E_USER_WARNING);
    }

    //Close curl connection
    curl_close($curl);

    return $response;
}

foreach($articles as $article) {
    $html = getRenderedHTML(file_get_contents(__DIR__ . '/_posts/'.$article->name.'.md'));
	file_put_contents(__DIR__ . '/data/cache/'.$article->name.'.html', $html);
}
