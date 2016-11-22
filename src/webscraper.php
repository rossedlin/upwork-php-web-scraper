<?php
  require 'vendor/autoload.php';
  use GuzzleHttp\Client;

  function placeholder($someParam)
  {
    $client = new Client([
      'base_uri' => 'http://archive-grbj-2.s3-website-us-west-1.amazonaws.com/',
      'timeout'  => 5.0,
    ]);

    # Request / or root
    $response = $client->request('GET', '/');
    $body = $response->getBody();

    echo $someParam;
  }

?>
