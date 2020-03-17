#!/usr/bin/env php

<?php
  if ($argc < 2) die('You have to provide repository');

  $isFlag = function(string $opt) { return $opt[0] === '-' && strpos($opt, '=') !== false; };
  $repo = $argv[1];

  $services = ['github'];
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
        $branch = $consider[1];
      } else if ($isFlag($consider[1])) {
        $service = $extractFlag($consider[1]);
        $branch = $consider[0];
      } else {
        $branch = $consider[0];
      }
    }
  }

  var_dump($repo, $branch, $service);

