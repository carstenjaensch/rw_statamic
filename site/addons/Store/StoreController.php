<?php

namespace Statamic\Addons\Store;

use Statamic\Extend\Controller;
use Statamic\API\Collection;
use Statamic\API\Entry;
//use Illuminate\Support\Carbon;

use Carbon\Carbon;
use League\Csv\Writer;
use SplTempFileObject;
use Illuminate\Http\Response;


//https://github.com/kawax/laravel-amazon-product-api
use AmazonProduct;

class StoreController extends Controller
{
    /**
     * Maps to your route definition in routes.yaml
     *
     * @return mixed
     */
    public function index()
    {
        return $this->view('index');
    }
    /**
     * Maps to your route definition in routes.yaml
     *
     * @return mixed
     */
    public function getSaugroboter()
    {
        $this->getAmazonCategory('saugroboter', 1);
        return $this->view('saugroboter');
    }
    /**
     * Maps to your route definition in routes.yaml
     *
     * @return mixed
     */
    public function export()
    {

      $handle = 'saugroboter';
      $filename = slugify("{$handle}-".Carbon::now()->timestamp);
      //$filename = slugify("{$handle}-".get_date());

      $this->writer = Writer::createFromFileObject(new SplTempFileObject);

      $this->insertHeaders($handle);
      $this->insertData($handle);
      $this->view('saugroboter');
      return response((string) $this->writer, 200, [
          'Content-type' => 'text/csv',
          'Content-Disposition' => "attachment; filename={$filename}.csv",
      ]);

    }
    // Creates and inserts CSV Header based on collection fieldset
    private function insertHeaders($handle)
    {
        $header_data = array("title", "Brand", "EAN", "rw_price", "rw_offer_price", "photo");;

        $this->csv_header = $header_data;

        $this->writer->insertOne($header_data);
    }
    // Creates content based on fieldset fields
    private function insertData($handle)
    {
        $header_data = $this->csv_header;

        $collectiondata = Entry::whereCollection($handle);

        $data = collect($collectiondata)->map(function ($entry) use ($header_data) {
            $ret = [];
            $entry = $entry->toArray();

            foreach ($header_data as $key => $value) {
                if (array_key_exists($value, $entry)) {
                    // convert entry array to pipe delimited string
                    $entry_value = '';
                    if (is_array($entry[$value])) {
                        $entry_value = implode('|', $entry[$value]);
                    } else {
                        $entry_value = $entry[$value];
                    }

                    $ret[] = $entry_value;
                } else {
                    $ret[] = '';
                }
            }

            return $ret;
        })->toArray();

        $this->writer->insertAll($data);
    }

    /**
     * Maps to your route definition in routes.yaml
     *
     * @return mixed
     */
    public function getMaehroboter()
    {
        return $this->view('maehroboter');
    }

    private function getAmazonCategory ($keyword="mähroboter", $page=1)
    {
      # string $category, string $keyword = null, int $page = 1
      // Damit kannst du einfach alle Produkte auf Amazon durchsuchen, z.b.:
      // http://www.roboterwelt.de/amazon?q=saugroboter&category=All
      // Und dir dann ein paar Details zu jedem Produkt ansehen:
      // http://www.roboterwelt.de/amazon/getItemByAsin?asin=B01I9QKQBE

      $response = AmazonProduct::search('All', $keyword , $page);
      $results = array_get($response, 'Items.TotalResults');
      $pages = array_get($response, 'Items.TotalPages');
      $maxPages = 5;
      for ($i = 1; $i <= $maxPages;$i++) {
        $response = AmazonProduct::search('All', $keyword , $i);
        $nodes = array_get($response, 'Items.Item');

        forEach ( $nodes as $item  ) {
          $this->fillCollection($keyword, $item);

        }
      }
      # returns normal array

      // # string $browse Browse node
      // $response = AmazonProduct::browse('1');
      // # sort by TopSeller
      //
      // # Response Group: NewReleases
      // $response = AmazonProduct::browse('1', 'NewReleases');
      //
      // # string $asin ASIN
      // $response = AmazonProduct::item('ASIN1');
      //
      // # array $asin ASIN
      // $response = AmazonProduct::items(['ASIN1', 'ASIN2']);
      //
      // # setIdType: support only item() and items()
      // $response = AmazonProduct::setIdType('EAN')->item('EAN');
      // # reset to ASIN
      // AmazonProduct::setIdType('ASIN');
    }

    private function fillCollection ($collection="things", $item){

      //dd (Entry::whereCollection($collection));

      $attributes = array_get($item, 'ItemAttributes');
      $photo = array_get($item, 'MediumImage.URL');
      //dd($item);
      //dd($attributes);
      // "title" => "Proscenic 790T WLAN Staubsauger Roboter (2 in 1: Saugroboter & Wischroboter), Selbstaufladung, visuelle Karte, Alexa-Sprachsteuerung, Hohe Saugkraft für Tierhaare und Allergene, Hartböden und Teppiche"
      //           "amazon_id" => "B06XDNF5ZV"
      //           "amazon_product_url" => "https://www.amazon.de/Proscenic-790T-Staubsauger-Roboter-Alexa-Sprachsteuerung/dp/B06XDNF5ZV?SubscriptionId=AKIAI5DK2NO2GWSMS6CQ&tag=wwwroboterwel-21&linkCode=xm2&camp=2025&creative=165953&creativeASIN=B06XDNF5ZV"
      //           "Brand" => "Proscenic"
      //           "photo" => "https://images-eu.ssl-images-amazon.com/images/I/51Xw0YDaQ2L._SL160_.jpg"
      //           "amazon_price" => "27900"
      //           "amazon_offer_price" => "26847"
      if (!Entry::slugExists(str_slug($attributes['Title']), $collection)) {
        try {
                $factory = Entry::create(str_slug($attributes['Title']))
                          ->collection($collection)
                          ->with(['title' => $attributes['Title'],
                                  'amazon_id' => $item['ASIN'],
                                  'photo' => $photo,
                                  "amazon_product_url" => $item["DetailPageURL"],
                                  "Brand" => $attributes['Brand'],
                                  "amazon_offer_price" => array_get($item, 'OfferSummary.LowestNewPrice.Amount'),
                                  "amazon_price" => array_get($attributes,'ListPrice.Amount')
                                ])
                          //->date(date("Y-m-d"))
                          ->date(Carbon::now()->timestamp);

                $entry = $factory->get();
                $entry->save();
            } catch (\Exception $e) {
                 echo 'Caught exception: ',  $e->getMessage(), "\n";
            }

     } else {
       try {
         $entry = Entry::whereSlug(str_slug($attributes['Title']), $collection)
//dd($factory);
          // ->collection($collection)
           ->set('title' , $attributes['Title'])
           ->set('amazon_id' , $item['ASIN'])
           ->set('photo' , $photo)
           ->set("amazon_product_url" , $item["DetailPageURL"])
           ->set("Brand" , $attributes['Brand'])
           ->set("amazon_offer_price" , array_get($item, 'OfferSummary.LowestNewPrice.Amount'))
           ->set("amazon_price" , array_get($attributes,'ListPrice.Amount'));

           $entry->save();

       } catch (\Exception $e) {
          echo 'Caught exception: ',  $e->getMessage(), "\n";
       };


     };

      //dd($entry);
    }
}
