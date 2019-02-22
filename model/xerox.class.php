<?php
class xerox{
  private $products;
  private $xerox_products;
  private $filters;
  private $product_listing;
  private $url;
  protected $configs;
  private $excerpt_limit = 250;
  public function __construct($modx,$configs = []){
    $this->test = $test;
    $this->configs = $configs;
    $this->products = $this->getData();
    $this->xerox_products = [];
    $this->filters = [
      'office-printers-en' => 1572,
      'multifunction-printers-copiers-all-in-one-en' => 1573,
      'production-printers-and-digital-presses-en' => 1574,
    ];

    $this->product_listing = [
      'tv15', //firstKeyFeature
      'tv16', //secondKeyFeature
      'tv17', //thirdKeyFeature
      'tv18', //fourthKeyFeature
      'tv19', //fithKeyFeature
      'tv20', //sixthKeyFeature
    ];

    if(isset($config['production']) && $config['production']){
      $this->url = 'https://www.perfectcolours.com';
    }else{
      $this->url = 'https://c0125.paas1.lon.modxcloud.com';
    }


  }

  public function getXeroxProducts(){
    $xerox_printers = [];
    foreach($this->products as $product){
      $parent = '';
      if(isset($product->categories)){
        $counter = 0;
        foreach($product->categories as $category){
          //Filter products
          if(in_array($category->slug,array_keys($this->filters))){
            //Get parent
            $parent = $this->getParent($category->slug);
            $counter++;
          }
        }
        //Skip product if not on the filter list
        if(!$counter){
          continue;
        }
      }
      $features = $this->getFeaturesAttribute($product->custom_fields->features->value);
      $pagetitle = $this->setPageTitle($product->title->rendered);
      $fields = array(
        'pagetitle' => $pagetitle, //title
        'alias' =>  $this->setAlias($pagetitle), //alias
        'longtitle' =>  $pagetitle,//$product->seo->metakeywords, //longtitle
        'description' =>  $product->seo->metadesc, //description
        'template' =>  33, //template
        'richtext' =>  0, //richtext
        'content' =>  $this->setContent($product), //content
        'tv5' =>  $product->featured_image->source_url, // image //(this is not accurate )$this->url .'/'. $this->configs['root_data']. '/' . $this->configs['hash'] . '/assets/'. $product->slug. '/' .$product->featured_image->media_details->sizes->medium->file, //productFeaturedImage
        'tv28' =>  'Xerox', //productBrand
        'tv4' =>  $this->setExcerpt($product->excerpt->raw), //listExcerpt
        'parent' => $parent, //parent
        'tv25' =>  $this->getProductSpecPdf($product), //productSpecSheet
        'published' => 1, //published
        'context_key' => 'web',
        'tvs' => true,
      );

      $xerox_products_merged = array_merge($fields,$features);
      $xerox_printers[] = $xerox_products_merged;
    }
    $this->xerox_products  = $xerox_printers;
    return $this;
  }

  private function getProductSpecPdf($product){
    if(isset($product->custom_fields->liens_url->value) && count($product->custom_fields->liens_url->value)){
      foreach($product->custom_fields->liens_url->value as $pdf){
        if($pdf->txt_bt_liens_widget == 'Specifications'){
          return $pdf->url;
        }
      }
    }
  }

  private function getParent($slug){
    foreach($this->filters as $filter => $parent){
      if($filter == $slug){
        return (int)$parent;
      }
    }
  }

  private function parseLi($html){
    $doc = new \DOMDocument();
    $doc->loadHTML($html);
    $liList = $doc->getElementsByTagName('li');
    $liValues = array();
    foreach ($liList as $li) {
        $liValues[] = preg_replace("/[∆лДΛдАÁÀÂÃÄ]/u",     "", $li->nodeValue);
    }

    return $liValues;
  }

  private function getFeaturesAttribute($features){
    $product_features = $this->parseLi($features);
    $results = [];
    for($i = 0;$i <= min(5, count($this->product_listing) -1);$i++){
      $results[$this->product_listing[$i]] = isset($product_features[$i]) ? $product_features[$i] : '' ;
    }
    return $results;
  }

  public function getValues(){
    if(count($this->xerox_products)){
      $array_values = array_map(function($values){
        return array_values($values);
      },$this->xerox_products);
      return $array_values;
    }
    return [];
  }
  //TODO: create a block to make sure data and configs are exist
  public function getData(){

    if(isset($this->configs['root_data'])){
      $data_json = file_get_contents($this->configs['root_data'].'/src/data-posts-en.json');
      return json_decode($data_json);
    }
    return [];
  }

  public function test(){
    if(isset($this->configs['root_data'])){
     
      $data_json = file_get_contents($this->configs['root_data'].'/src/data-posts-en.json');
      $results = [
        'configs' => $this->configs,
        'data' => $data_json
      ];

      return json_encode($results);
  
    }
    return 'failed to open configs';
  }

  public function build(){
    if(count($this->xerox_products)){
      $array_values = array_map(function($values){
        return array_values($values);
      },$this->xerox_products);

      $array_keys = array_keys($this->xerox_products[0]);
      array_unshift($array_values,$array_keys);

      return $array_values;
    }
    return $this->xerox_products;
  }

  private function setContent($product){
    $content = $product->content->rendered;
    $html = '';
    if(isset($product->custom_fields->autre_dinformtion->value) && $product->custom_fields->autre_dinformtion->value){
      //TODO: Change 800 to preg_ex numbers
      $video = preg_replace('/width\=\\"800\\"/','width="100%"',$product->custom_fields->autre_dinformtion->value);
      $video = preg_replace('/height\=\\"450\\"/','height="240"',$video);
      $html = '<div class="container">
      <div class="row [[+rowClass]]" id="[[+rowId]]">
          <div class="col-md-5">
            '. $video . '
          </div>
          <div class="col-md-7">
              '.$content .'
          </div>
      </div>
    </div>';
    
    }else{
      $html = '<div class="container">
      <div class="row [[+rowClass]]" id="[[+rowId]]">
    
          <div class="col-md-12">
              '.$content .'
          </div>
      </div>
    </div>';
    }

    return preg_replace('/\s+/S', " ",$html);
   
  }

  private function setExcerpt($excerpt){
    $excerpt = preg_replace('/\s+/S', " ",$excerpt);
    if(strlen($excerpt) > $this->excerpt_limit){
      return  substr($excerpt, 0, strrpos(substr($excerpt, 0, $this->excerpt_limit), ' ')) . '...';
    }
    return $excerpt;
  }

  public function asArray(){
    return $this->xerox_products;
  }

  public function implode_tab($arr){
      return implode("\n", array_map(function($value){
        return implode("\t", $value);
      }, $arr));
  }

  private function setPageTitle($title){
    $result = '';
    if(preg_match('/^(Xerox)/',$title)){
      $result = $title;
    }else{
      $result = 'Xerox® '. $title;
    }
    
    return html_entity_decode($result);
  }

  private function setAlias($alias){
    return preg_replace('/[^a-z0-9]+/','-',preg_replace('/(-en)$/','',strtolower($alias)));
  }




}

?>