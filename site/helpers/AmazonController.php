<?php

namespace Statamic\SiteHelpers;

//use Statamic\Extend\Controller as AbstractController;
use Statamic\Extend\Controller ;
//use AmazonProduct;

class AmazonController extends Controller
{
    /**
     * @return mixed
     */
    public function amazon()
    {

      return $this->view('amazon', [
                  'title' => 'Amazon'
              ]);
# string $category, string $keyword = null, int $page = 1
//$response = AmazonProduct::search('All', 'amazon' , 1);
//dd($response);
}
}
