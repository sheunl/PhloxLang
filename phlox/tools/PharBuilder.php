<?php

$build_dir = __DIR__.'/../build';

if(! file_exists($build_dir)){
    mkdir($build_dir);
}

$p = new Phar($build_dir.'/phlox.phar',0,'phlox.phar');
$p->buildFromDirectory(__DIR__.'/../src');
$p->setDefaultStub('phloxc');
$p->stopBuffering();