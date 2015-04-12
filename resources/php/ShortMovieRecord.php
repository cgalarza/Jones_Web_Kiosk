<?php

  class ShortMovieRecord{

    const BEG_RECORD_URL = "http://libcat.dartmouth.edu/record=";
    const END_RECORD_URL = "~S4";
    const MEDIA_DVD = 'Jones Media DVD';
    const MEDIA_VHS = 'Jones Media Video tape';
    const BEG_IMG_PATH = '/library/mediactr/images/dvd/';
    const JPG_EXTENTION = '.jpg';
    const IMG_NOT_AVAILABLE = 'images/Image_Not_Available.png';
    const IMG_ON_RESERVE = 'images/On_Reserve_at_Jones.png';
    const ON_RESERVE_AT_JMC = 'On Reserve at Jones Media';
    const RESERVE = "On Reserve";
    const DVD = "DVD";
    const VHS = "VHS";
    const MULTIPLE_DVD = "DVD Set";
    const MULTIPLE_VHS = "VHS Set";
    const MULTIPLE_TYPE = "Multiple Types";

    protected $bib_number;
    protected $url;
    protected $title;
    protected $summary;
    protected $media; // DVD or VHS
    protected $accession_number; // -1 if there isn't an accession number
    protected $image_path;
    protected $cast;
    protected $location_array; // multi dimensional array -- each array contains a locations, status and call number

    function __construct($bibnum){
      $this->bib_number = $bibnum;

      $this->load_information($this->get_xpath());

      echo $this->create_JSON_representation();
    }

    function get_xpath(){
      $this->url = self::BEG_RECORD_URL . $this->bib_number . self::END_RECORD_URL;

      $html = file_get_contents($this->url);

      // In order to supress warnings.
      libxml_use_internal_errors(true);

      $doc = new DOMDocument();
      $doc->loadHTML($html);

      return new DOMXPATH($doc);
    }

    function load_information($xpath){

      // Getting Title.
      $this->title = $this->get_title($xpath->query(
        "//td[@class='bibInfoLabel' and text()='Title']/following-sibling::td[@class='bibInfoData']"));

      // Getting Summary.
      $summary_node = $xpath->query(
        "//td[@class='bibInfoLabel' and text()='Summary']/following-sibling::td[@class='bibInfoData']");
      $this->summary = trim($summary_node->item(0)->nodeValue);

      // Getting Cast.
      $cast_node = $xpath->query(
        "//td[@class='bibInfoLabel' and text()='Performer']/following-sibling::td[@class='bibInfoData']");
      // FIX ME: Potentially need to do this check for every item.
      $this->cast = (isset($cast_node->item(0)->nodeValue)) ?
              trim($cast_node->item(0)->nodeValue) :
              '';



      // Getting location array information.
      $this->location_array = $this->get_locations($xpath->query(
        "//tr[@class='bibItemsEntry']"));

      $this->get_type_and_accession_num();

      // Creating image path based on the accession number and media type.
      $this->image_path = $this->get_img_path();

    }

    function get_type_and_accession_num(){
      // Getting media type (DVD or VHS) and accession number.
      $this->accession_number = -1;

      if (sizeof($this->location_array) == 1){
        // If there is only one item then the type can only be DVD,
        // VHS or On Reserve.
        $location_info = $this->location_array[0];

        if ($location_info['type'] == self::MEDIA_DVD){
          $this->media = self::DVD;
          $this->accession_number = $this->get_accession_num($location_info['callnumber']);
          return;
        }
        else if ($location_info['type'] == self::ON_RESERVE_AT_JMC){
          $this->media = self::RESERVE;
          return;
        }

        else if ($location_info['type'] == self::MEDIA_VHS){
          $this->media = self::VHS;
          $this->accession_number = $this->get_accession_num($location_info['callnumber']);
          return;
        }
      }

      else {
        // There are multiple discs for this item. It has to be
        // On Reserve, a DVD set, a VHS set, or Multiple Types.

        // Sets the type to the first type in the locations array.
        $loc = $this->location_array[0]['type'];

        // Sets the accession number to the first callnumber in the locations array.
        $this->accession_number =
          $this->get_accession_num(
            $this->location_array[0]['callnumber']);

        // Check to see if all the items have the same type. Items are consireded to have the same types even if some
        // discs are on reserve.
        foreach ($this->location_array as $location_info){
          $new_loc = $location_info['type'];

          if (($loc == self::ON_RESERVE_AT_JMC && $new_loc == self::MEDIA_DVD) ||
            ($loc == self::ON_RESERVE_AT_JMC && $new_loc == self::MEDIA_VHS)) {
            $loc = $new_loc;
          $this->accession_number = $this->get_accession_num($location_info['callnumber']);

          }

          // If one item is different (not including items on
          // reserve), the type  is set to "Multiple Types."
          if ($new_loc != $loc && $new_loc != self::ON_RESERVE_AT_JMC){
            $this->media = self::MULTIPLE_TYPE;
            return;
          }
        }

        // If all types are the same (even if some discs are on reserve)
        // set to Multiple DVD or Multiple VHS.
        // If all items are on reserve then set the type to On Reserve.
        if ($loc == self::MEDIA_DVD)
          $this->media = self::MULTIPLE_DVD;
        else if ($loc == self::MEDIA_VHS)
          $this->media = self::MULTIPLE_VHS;
        else if ($loc == self::ON_RESERVE_AT_JMC)
          $this->media = self::RESERVE;
      }
    }

    function get_accession_num($call_number_string){
      $accession_array = explode(" ", $call_number_string);

      return trim($accession_array[0]);
    }

    function get_title($title_node){
      $title_split = explode("/", $title_node->item(0)->nodeValue);
      $title_split = explode("[", $title_split[0]);
      return ucwords(trim($title_split[0]));
    }

    function get_locations($table_node){

      $loc_array = array();

      // Getting call number, type and status from each row in the table.
      foreach ($table_node as $n){ // for each row

        $columns = $n->childNodes;

        if (trim(str_replace("\xA0", "", utf8_decode($columns->item(4)->nodeValue))) == 'LIBRARY HAS')
          continue;

        $type = trim(str_replace("\xA0", "", utf8_decode($columns->item(0)->nodeValue)));

        $call_number = trim(str_replace("Browse Nearby Items", "", str_replace("\xA0", "", utf8_decode($columns->item(2)->nodeValue))));

        $status = trim(str_replace("\xA0", "", utf8_decode($columns->item(4)->nodeValue)));

        array_push($loc_array, array("type" => $type, "callnumber" => $call_number, "status" => $status));

      }
      return $loc_array;
    }

    function get_img_path() {
      // To Do: Check if file exists on the server.
      $img_file = self::BEG_IMG_PATH . $this->accession_number . self::JPG_EXTENTION;

      if (file_exists($img_file) && $this->media == self::DVD)
        return $img_file;
      else if (file_exists($img_file) && $this->media == self::MULTIPLE_DVD)
        return $img_file;
      else if ($this->media == self::RESERVE)
        return self::IMG_ON_RESERVE;
      else
        return self::IMG_NOT_AVAILABLE;
    }

    function create_JSON_representation(){
      $json_array = array(
        "title" => $this->title,
        "media" => $this->media,
        "accession_number" => $this->accession_number,
        "summary" => $this->summary,
        "url" => $this->url,
        "bibnumber" => $this->bib_number,
        "cast" => $this->cast,
        "image_path" => $this->image_path,
        "location" => $this->location_array
      );

      return json_encode($json_array);
    }

  }
?>
