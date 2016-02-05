<?php
/**
 * Project: AmazonScrapper
 *
 * @author Amado Martinez <amado@projectivemotion.com>
 */



// Used for testing. Run from command line.
if(!isset($argv))
    die("Run from command line.");

// copied this from doctrine's bin/doctrine.php
$autoload_files = array( __DIR__ . '/../vendor/autoload.php',
                        __DIR__ . '/../../../autoload.php');

foreach($autoload_files as $autoload_file)
{
    if(!file_exists($autoload_file)) continue;
    require_once $autoload_file;
}
// end autoloader finder




$testf = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'AmazonScrapperDemo.html';

if(!file_exists($testf))
{
    $url = 'https://www.amazon.com/s/ref=sr_il_ti_movies-tv?rh=n%3A2625373011%2Cp_n_format_browse-bin%3A2650306011&ie=UTF8&qid=1454640631&lo=movies-tv';
    $content = file_get_contents($url);

    file_put_contents($testf, $content);
}else{
    $content = file_get_contents($testf);
}



// Demo:
$f = new AmazonScrapper();

//  in case you want to set a category_id, etc..
$f->setBaseData(array('my_id'  => '9999'));
$results = $f->getPageItems($content);

print_r($results);

