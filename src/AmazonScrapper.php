<?php
/**
 * Project: AmazonScrapper
 *
 * According to Urban Dictionary: A Scrapper is: Someone who looks small but is really wired and can kick some major ass even though he doesn't look like it.
 * They started pickin on that guy over there and the little scrapper took 'em all.
 *
 * @license MIT
 *
 * @author Amado Martinez <amado@projectivemotion.com>
 */

/**
 * Project: AmazonScrapper
 *
 * @author Amado Martinez <amado@projectivemotion.com>
 */
class AmazonScrapper
{

    protected $basedata = array();
    protected $column_prefix    =   '';

    public function setBaseData($basedata)
    {
        $this->basedata = $basedata;
        return $this;
    }

    public function revealHiddenResults($doc)
    {
        // un-comment some hidden results

        $hidden = pq($doc['#results-atf-next']);
        $hidden_code = $hidden->html();

        $uncommented = preg_replace('#^[\s\S]*?<!--[\s\Ss]*?([\s\S]*?)-->\s*$#', '$1', $hidden_code);

        $hidden->html($uncommented);
    }

    public function getPageItems($html)
    {
        // @todo writre a comprehensive test case to verify the number of results found after revealing coommented elements.
        $doc = phpQuery::newDocument($html);
        $this->revealHiddenResults($doc);
        $items = $doc['.s-result-list .s-result-item'];

        $extracted = array();
        foreach($items as $node)
        {
            $data = $this->basedata;
            $extracted[]  = $this->extractItemData($node, $data);
        }
        return $extracted;
    }

    public function getFilePageItems($filename)
    {
        $content = file_get_contents($filename);
        return $this->getPageItems($content);
    }

    protected function extractItemData($node, &$data)
    {
        $item_node = pq($node);
        $is_prime = $item_node->find('.a-icon-prime')->length != 0  ? true : false;
        $title = $item_node->find('.s-access-detail-page');

        $data[$this->column_prefix . 'title']  =   trim($title->text());
        $data[$this->column_prefix . 'url']    =   trim($title->attr('href'));
        $data[$this->column_prefix . 'price']  =   floatval(str_replace('$', '', trim($item_node->find('.a-color-price')->text())));
        $data[$this->column_prefix . 'is_prime']   =   $is_prime ? 1 : 0;
        $data[$this->column_prefix . 'reviews'] = intval($item_node->find('.a-spacing-none .a-size-small:last')->text());
        $data[$this->column_prefix . 'pic_url'] = trim($item_node->find('.s-access-image')->attr('src'));
        $data[$this->column_prefix . 'ship']   =   '';

        $item_code = $item_node->html();
        $data['ship']   =   $this->findShipping($item_code);

        return $data;
    }

    function findShipping($html)
    {
        $p = preg_match('#\s*([^>]*?)\s*Shipping[^<]*?#', $html, $matches);
        return $p > 0 ? $matches[0] : 'Unknown';
    }
}


// Used for testing. Run from command line.
if(isset($argv))
{
    require __DIR__ . '/../vendor/autoload.php';

    $testf = getcwd() . '/testfile.html';
    if(!file_exists($testf))
    {
        $url = 'https://www.amazon.com/s/ref=sr_il_ti_movies-tv?rh=n%3A2625373011%2Cp_n_format_browse-bin%3A2650306011&ie=UTF8&qid=1454640631&lo=movies-tv';
        $content = file_get_contents($url);

        file_put_contents($testf, $content);
    }else{
        $content = file_get_contents($testf);
    }

    $f = new AmazonScrapper();

    //  in case you want to set a category_id, etc..
    $f->setBaseData(array('my_id'  => '9999'));

    $results = $f->getPageItems($content);

    print_r($results);
}

