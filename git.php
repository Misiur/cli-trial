#!/usr/bin/env php
<?php
  if ($argc < 2) die('You have to provide repository');

  $isFlag = function($opt) { return $opt[0] === '-' && strpos($opt, '=') !== false; };
  $repo = $argv[1];

  $services = ['github'];
  $servicesUrls = ['github' => 'https://api.github.com'];
  $service = null;
  $isValidService = function(string $needle) use ($services) { return in_array($needle, $services); };

  $extractFlag = function(string $flag) use ($services, $isValidService) {
    $considerService = explode('=', $flag)[1];

    if ($isValidService($considerService)) {
      return $considerService;
    }

    die('Unknown service ' . $considerService . ', available: ' . implode(', ', $services));
  };

  $shift = 0;

  if ($isFlag($repo)) {
    $service = $extractFlag($repo);

    if ($argc === 2) die('Repository not specified');
    $repo = $argv[2];
    $shift = 1;
  }

  if (strpos($repo, '/') === false) die('Not a valid repository representation');

  $branch = 'master';

  if ($argc > 2) {
    $consider = array_slice($argv, 2);
    $numopts = count($consider);

    if ($service !== null) {
      $branch = $consider[0] !== '' ? $consider[0] : 'master';
    } else {
      // Try service before branch
      $service = $consider[0];

      if ($isFlag($service)) {
        $service = $extractFlag($service);
        $branch = $consider[1] !== '' ? $consider[1] : 'master';
      } else if (!empty($consider[1]) && $isFlag($consider[1])) {
        $service = $extractFlag($consider[1]);
        $branch = $consider[0];
      } else {
        $branch = $consider[0];
        $service = $services[0];
      }
    }
  } else {
    $service = $services[0];
  }

  $host = empty($service) ? $servicesUrls['github'] : $servicesUrls[$service];
  $url = "$host/repos/$repo/branches/$branch";

  if (filter_var($url, FILTER_VALIDATE_URL) === false) die('Invalid repository name or branch name while accessing ' . $url);

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    // 'Accept' => 'application/vnd.github.nebula-preview+json',
    // 'Accept' => 'application/vnd.github.groot-preview+json',,application/vnd.github.v3+json,sha',
    'Accept: sha',
    'User-Agent: valid',
  ]);

  curl_setopt($ch, CURLOPT_VERBOSE, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  $content = curl_exec($ch);
  $err = curl_errno($ch);
  curl_close($ch);

  $data = json_decode($content);
  if (!empty($data->commit)) {
    echo $data->commit->sha;
  } else {
    die('Commit sha could not be retrieved');
  }

  echo "\r\n";
