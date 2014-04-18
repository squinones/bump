#! /usr/local/bin/php
<?php

$major = 0;
$minor = 0;
$patch = 0;

if (empty($argv[1]) || !in_array($argv[1], ['major', 'minor', 'patch'])) {
	echo 'Please supply a bump type: major, minor, patch.' . PHP_EOL;
	exit;
}

$bump = $argv[1];

$tags = explode(PHP_EOL, shell_exec('git tag'));
foreach ($tags as $tag) {
	$parts = explode('.', $tag);
	if (count($parts) !== 3) {
		continue;
	}

	if ($parts[0] > $major) {
		$major = $parts[0];
		$minor = $parts[1];
		$patch = $parts[2];
	} elseif ($parts[0] == $major) {
		if ($parts[1] > $minor) {
			$minor = $parts[1];
			$patch = $parts[2];
		} elseif ($parts[1] == $minor && $parts[2] > $patch) {
			$patch = $parts[2];
		}
	}
}

$current_version = implode('.', [$major, $minor, $patch]);

switch ($bump) {
	case 'major':
		$major++;
		$minor = 0;
		$patch = 0;
		break;

	case 'minor':
		$minor++;
		$patch = 0;
		break;

	case 'patch':
		$patch++;
		break;
}

$bumped_version = implode('.', [$major, $minor, $patch]);

$cmd = 'git shortlog ' . $current_version . '..';
echo PHP_EOL . 'git shortlog ' . $current_version . '..' . PHP_EOL;
passthru($cmd);

$tagit = readline('Do you want to create and push a new tag (' . $bumped_version . ') with the above changes? (y/n):');
if ($tagit !== 'y') {
	echo 'Its cool. I understand.' . PHP_EOL;
	exit;
}

passthru('git tag ' . $bumped_version);
echo $bumped_version . ' has been tagged.' . PHP_EOL;
